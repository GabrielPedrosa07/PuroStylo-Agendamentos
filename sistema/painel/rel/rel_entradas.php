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

// Lógica para o título do relatório
$acao_rel = 'Entradas / Ganhos';
if ($filtro == 'Venda') {
    $acao_rel = 'Vendas';
} elseif ($filtro == 'Serviço') {
    $acao_rel = 'Serviços';
} elseif (!empty($filtro)) {
    $acao_rel = 'Recebimentos';
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSULTA ÚNICA, SEGURA E OTIMIZADA COM LEFT JOIN ---
// Buscamos todos os dados necessários (recebimentos, clientes, usuários) em uma única consulta.
// Isso elimina o problema de performance "N+1" e a vulnerabilidade de SQL Injection.
$query_sql = "
    SELECT 
        r.descricao,
        r.tipo,
        r.valor,
        r.data_pgto,
        c.nome AS nome_cliente,
        ub.nome AS nome_usuario_baixa
    FROM 
        receber r
    LEFT JOIN 
        clientes c ON r.pessoa = c.id
    LEFT JOIN 
        usuarios ub ON r.usuario_baixa = ub.id
    WHERE 
        r.data_pgto >= :dataInicial 
        AND r.data_pgto <= :dataFinal 
        AND r.tipo LIKE :filtro 
        AND r.pago = 'Sim' 
    ORDER BY 
        r.data_pgto ASC
";

$query = $pdo->prepare($query_sql);
$query->bindValue(':dataInicial', $dataInicial);
$query->bindValue(':dataFinal', $dataFinal);
$query->bindValue(':filtro', '%' . $filtro . '%');
$query->execute();
$entradas = $query->fetchAll(PDO::FETCH_ASSOC);

$total_entradas_valor = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Ganhos</title>
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
        .texto-esquerda { text-align: left; }
        .resumo-relatorio { text-align: right; margin: 20px 0; font-size: 10px; font-weight: bold; }
        .resumo-relatorio span { margin-left: 20px; }
    </style>
</head>
<body>

    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de <?php echo $acao_rel; ?></div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </header>

    <div class="cabecalho-principal"></div>

    <main class="mx-2">
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>

        <?php if (!empty($entradas)): ?>
            <table class="table table-striped relatorio-tabela">
                <thead>
                    <tr>
                        <th class="texto-esquerda">Descrição</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Data PGTO</th>
                        <th>Recebido Por</th>
                        <th>Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entradas as $entrada):
                        $total_entradas_valor += $entrada['valor'];
                        $valorF = number_format($entrada['valor'], 2, ',', '.');
                        $data_pgtoF = (new DateTime($entrada['data_pgto']))->format('d/m/Y');
                    ?>
                    <tr>
                        <td class="texto-esquerda"><?php echo htmlspecialchars($entrada['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($entrada['tipo']); ?></td>
                        <td>R$ <?php echo $valorF; ?></td>
                        <td><?php echo $data_pgtoF; ?></td>
                        <td><?php echo htmlspecialchars($entrada['nome_usuario_baixa'] ?? 'Não informado'); ?></td>
                        <td><?php echo htmlspecialchars($entrada['nome_cliente'] ?? 'Não informado'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado para o período e filtro selecionados!</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($entradas)): ?>
    <div class="resumo-relatorio">
        <span>TOTAL DE RECEBIMENTOS: <?php echo count($entradas); ?></span>
        <span class="text-success">TOTAL GERAL: R$ <?php echo number_format($total_entradas_valor, 2, ',', '.'); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>

</body>
</html>