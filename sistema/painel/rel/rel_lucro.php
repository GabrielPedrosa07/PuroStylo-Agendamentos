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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { margin: 20px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #333;
        }
        .footer { 
            width: 100%; 
            background-color: #000; 
            color: #fff;
            padding: 10px; 
            position: absolute; 
            bottom: 0; 
            text-align: center; 
            font-size: 10px; 
        }
        .cabecalho-info { 
            position: relative; 
            height: 100px; 
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }
        .imagem-logo { 
            width: 120px; 
            position: absolute; 
            right: 0; 
            top: 0; 
            filter: grayscale(100%); 
        }
        .titulo-relatorio { 
            position: absolute; 
            left: 0; 
            top: 20px;
            font-size: 24px; 
            font-weight: bold; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .data-relatorio { 
            position: absolute; 
            left: 0;
            top: 55px; 
            font-size: 12px; 
            color: #555;
        }
        .texto-apuracao { 
            font-size: 11px; 
            text-transform: uppercase; 
            margin-bottom: 15px; 
            font-weight: bold;
        }
        table.relatorio-tabela { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12px; 
            margin-bottom: 20px;
        }
        table.relatorio-tabela th, table.relatorio-tabela td { 
            padding: 10px; 
            text-align: left; 
            border-bottom: 1px solid #ddd;
        }
        table.relatorio-tabela th { 
            background-color: #000; 
            color: #fff; 
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        table.relatorio-tabela tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-danger { color: #d9534f !important; }
        .text-success { color: #000 !important; } /* Success is Black */
        .resumo-final { 
            text-align: center; 
            margin-top: 30px; 
            padding: 20px;
            border: 1px solid #ddd;
            background: #fdfdfd;
        }
        .resumo-final span { 
            font-size: 24px; 
            font-weight: bold; 
            vertical-align: middle; 
        }
        .resumo-final img { 
            width: 40px; 
            vertical-align: middle; 
            margin-right: 15px; 
            filter: grayscale(100%);
        }
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Demonstrativo de Lucro</div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <?php 
        $path_logo = 'C:/wamp64/www/PuroStylo-Agendamentos/sistema/img/logo_rel.jpg';
        $type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
        $data_logo = file_get_contents($path_logo);
        $base64_logo = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);
        ?>
        <img class="imagem-logo" src="<?php echo $base64_logo; ?>">
    </header>

    <main>
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>
        <table class="table relatorio-tabela">
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
                    <td style="background: #f0f0f0; font-weight:bold" colspan="3" scope="col">TOTAL GANHOS</td>
                    <td style="background: #f0f0f0; font-weight:bold" colspan="3" scope="col">TOTAL DESPESAS</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-success" style="font-weight: bold; font-size:14px">R$ <?php echo number_format($total_entradas, 2, ',', '.'); ?></td>
                    <td colspan="3" class="text-danger" style="font-weight: bold; font-size:14px">R$ <?php echo number_format($total_saidas, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>
    </main>

    <div class="resumo-final">
        <!-- Using a local status image if available, else omit img to avoid break -->
        <?php 
        $path_img_status = 'C:/wamp64/www/PuroStylo-Agendamentos/sistema/img/' . $classe_img;
        if(file_exists($path_img_status)){
            $type_status = pathinfo($path_img_status, PATHINFO_EXTENSION);
            $data_status = file_get_contents($path_img_status);
            $base64_status = 'data:image/' . $type_status . ';base64,' . base64_encode($data_status);
            echo '<img src="'.$base64_status.'">';
        }
        ?>
        <span class="<?php echo ($saldo_total < 0) ? 'text-danger' : 'text-success'; ?>">
            LUCRO LÍQUIDO: R$ <?php echo $saldo_totalF ?>
        </span>
    </div>

    <div class="footer">
        <?php echo $nome_sistema; ?> | <?php echo $whatsapp_sistema; ?>
    </div>
</body>
</html>