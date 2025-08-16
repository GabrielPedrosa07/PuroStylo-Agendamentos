<?php
// Inclui a conexão (garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4')
include_once '../../conexao.php';

// --- TRATAMENTO DAS VARIÁVEIS DE ENTRADA ---
// No novo fluxo, estas variáveis vêm diretamente do script "orquestrador", não do $_GET.

// Formata as datas para exibição no relatório
$dataInicialF = (new DateTime($dataInicial))->format('d/m/Y');
$dataFinalF = (new DateTime($dataFinal))->format('d/m/Y');

// Lógica para o texto de apuração do período
if ($dataInicial == $dataFinal) {
    $texto_apuracao = 'APURADO EM ' . $dataInicialF;
} elseif ($dataInicial == '1980-01-01') {
    $texto_apuracao = 'APURADO EM TODO O PERÍODO';
} else {
    $texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSULTA ÚNICA E AGREGADA PARA TODOS OS TOTAIS ---
// Esta consulta é o coração da otimização.
// 1. Usa SUM() e GROUP BY para que o próprio banco de dados calcule os totais.
// 2. Usa UNION ALL para combinar os resultados das tabelas 'receber' e 'pagar' em uma única chamada.
// 3. É 100% segura, usando Prepared Statements.
$query_sql = "
    (SELECT 'receber' as origem, tipo, SUM(valor) as total 
     FROM receber 
     WHERE data_pgto >= :dataInicial1 AND data_pgto <= :dataFinal1 AND pago = 'Sim' AND tipo IN ('Serviço', 'Venda', 'Conta')
     GROUP BY tipo)
    UNION ALL
    (SELECT 'pagar' as origem, tipo, SUM(valor) as total 
     FROM pagar 
     WHERE data_pgto >= :dataInicial2 AND data_pgto <= :dataFinal2 AND pago = 'Sim' AND tipo IN ('Conta', 'Compra', 'Comissão')
     GROUP BY tipo)
";

$query = $pdo->prepare($query_sql);
// Bind dos parâmetros para a primeira parte da união
$query->bindValue(':dataInicial1', $dataInicial);
$query->bindValue(':dataFinal1', $dataFinal);
// Bind dos parâmetros para a segunda parte da união
$query->bindValue(':dataInicial2', $dataInicial);
$query->bindValue(':dataFinal2', $dataFinal);
$query->execute();
$resultados = $query->fetchAll(PDO::FETCH_ASSOC);

// --- PROCESSAMENTO DOS RESULTADOS AGREGADOS ---
// Inicializa todas as variáveis de total
$totais = [
    'servicos' => 0,
    'vendas' => 0,
    'receber' => 0,
    'pagar' => 0,
    'compras' => 0,
    'comissoes' => 0
];

// Loop através dos resultados (que serão no máximo 6 linhas) e atribui os totais
foreach ($resultados as $resultado) {
    if ($resultado['origem'] == 'receber') {
        if ($resultado['tipo'] == 'Serviço') $totais['servicos'] = $resultado['total'];
        if ($resultado['tipo'] == 'Venda') $totais['vendas'] = $resultado['total'];
        if ($resultado['tipo'] == 'Conta') $totais['receber'] = $resultado['total'];
    } elseif ($resultado['origem'] == 'pagar') {
        if ($resultado['tipo'] == 'Conta') $totais['pagar'] = $resultado['total'];
        if ($resultado['tipo'] == 'Compra') $totais['compras'] = $resultado['total'];
        if ($resultado['tipo'] == 'Comissão') $totais['comissoes'] = $resultado['total'];
    }
}

// Cálculos finais
$total_entradas = $totais['servicos'] + $totais['vendas'] + $totais['receber'];
$total_saidas = $totais['pagar'] + $totais['compras'] + $totais['comissoes'];
$saldo_total = $total_entradas - $total_saidas;

// Formatação para exibição
$saldo_totalF = number_format($saldo_total, 2, ',', '.');
$classe_saldo = ($saldo_total < 0) ? 'text-danger' : 'text-success';
$classe_img = ($saldo_total < 0) ? 'negativo.jpg' : 'positivo.jpg';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Demonstrativo de Lucro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <style>
        @page { margin: 0; }
        body { font-family: 'Times New Roman', Times, serif; margin: 5px 20px; }
        .footer { width: 100%; background-color: #ebebeb; padding: 5px; position: absolute; bottom: 0; text-align: center; font-size: 10px; }
        .cabecalho-principal { padding: 10px 0; margin-bottom: 20px; width: 100%; border-bottom: 1px solid #0340a3; }
        .cabecalho-info { position: relative; height: 120px; }
        .imagem-logo { width: 150px; position: absolute; right: 0; top: 10px; }
        .titulo-relatorio, .data-relatorio { position: absolute; left: 0; }
        .titulo-relatorio { top: 10px; font-size: 18px; font-weight: bold; text-decoration: underline; }
        .data-relatorio { top: 40px; font-size: 12px; }
        .texto-apuracao { font-size: 10px; text-decoration: underline; margin-bottom: 15px; }
        table.relatorio-tabela { width: 100%; border-collapse: collapse; font-size: 12px; vertical-align: middle; }
        table.relatorio-tabela th, table.relatorio-tabela td { border: 1px solid #dbdbdb; padding: 6px; text-align: center; }
        table.relatorio-tabela th { background-color: #ededed; font-size: 13px; }
        .resumo-final { text-align: center; margin-top: 30px; }
        .resumo-final span { font-size: 20px; font-weight: bold; vertical-align: middle; }
        .resumo-final img { width: 50px; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Demonstrativo de Lucro</div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </header>

    <div class="cabecalho-principal"></div>

    <main class="mx-2">
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>
        <table class="table table-striped relatorio-tabela">
            <thead>
                <tr>
                    <th scope="col">Serviços</th>
                    <th scope="col">Vendas</th>
                    <th scope="col">Recebimentos</th>
                    <th scope="col">Despesas</th>
                    <th scope="col">Compras</th>
                    <th scope="col">Comissões</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-success">R$ <?php echo number_format($totais['servicos'], 2, ',', '.'); ?></td>
                    <td class="text-success">R$ <?php echo number_format($totais['vendas'], 2, ',', '.'); ?></td>
                    <td class="text-success">R$ <?php echo number_format($totais['receber'], 2, ',', '.'); ?></td>
                    <td class="text-danger">R$ <?php echo number_format($totais['pagar'], 2, ',', '.'); ?></td>
                    <td class="text-danger">R$ <?php echo number_format($totais['compras'], 2, ',', '.'); ?></td>
                    <td class="text-danger">R$ <?php echo number_format($totais['comissoes'], 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td style="background: #e6ffe8" colspan="3" scope="col">Total de Entradas / Ganhos</td>
                    <td style="background: #ffe7e6" colspan="3" scope="col">Total de Saídas / Despesas</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-success" style="font-weight: bold;">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></td>
                    <td colspan="3" class="text-danger" style="font-weight: bold;">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>
    </main>

    <div class="resumo-final">
        <img src="<?php echo $url_sistema ?>/sistema/img/<?php echo $classe_img ?>" width="100px">
        <span class="<?php echo $classe_saldo ?>">R$ <?php echo $saldo_totalF ?></span>
    </div>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>

</body>
</html>