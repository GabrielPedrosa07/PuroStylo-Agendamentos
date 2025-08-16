<?php
// Inclui a conexão (garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4')
include_once '../../conexao.php';

// --- TRATAMENTO DAS VARIÁVEIS DE ENTRADA ---
// No novo fluxo, estas variáveis vêm diretamente do script "orquestrador", não do $_GET.

// --- LÓGICA DE PREPARAÇÃO DO RELATÓRIO ---
$dataInicialF = (new DateTime($dataInicial))->format('d/m/Y');
$dataFinalF = (new DateTime($dataFinal))->format('d/m/Y');

if ($dataInicial == $dataFinal) {
    $texto_apuracao = 'APURADO EM ' . $dataInicialF;
} elseif ($dataInicial == '1980-01-01') {
    $texto_apuracao = 'APURADO EM TODO O PERÍodo';
} else {
    $texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}

$acao_rel = '';
if ($pago == 'Sim') {
    $acao_rel = ' Pagas ';
} elseif ($pago == 'Não') {
    $acao_rel = ' Pendentes ';
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSTRUÇÃO DINÂMICA E SEGURA DA CONSULTA ---
// Começamos com a base da query e vamos adicionar filtros conforme necessário.
$query_sql = "
    SELECT 
        p.valor, p.data_lanc, p.data_venc, p.data_pgto, p.pago,
        func.nome AS nome_funcionario, func.telefone AS tel_funcionario, 
        func.tipo_chave AS tipo_chave_pix, func.chave_pix,
        cli.nome AS nome_cliente,
        serv.nome AS nome_servico
    FROM 
        pagar p
    LEFT JOIN 
        usuarios func ON p.funcionario = func.id
    LEFT JOIN 
        clientes cli ON p.cliente = cli.id
    LEFT JOIN 
        servicos serv ON p.servico = serv.id
    WHERE 
        p.data_lanc >= :dataInicial 
        AND p.data_lanc <= :dataFinal 
        AND p.pago LIKE :pago 
        AND p.tipo = 'Comissão'
";

// Array de parâmetros para o prepared statement
$params = [
    ':dataInicial' => $dataInicial,
    ':dataFinal' => $dataFinal,
    ':pago' => '%' . $pago . '%'
];

// Adiciona o filtro de funcionário apenas se um foi selecionado
if (!empty($funcionario)) {
    $query_sql .= " AND p.funcionario = :funcionario";
    $params[':funcionario'] = $funcionario;
}

$query_sql .= " ORDER BY p.pago ASC, p.data_venc ASC";

// Executa a consulta única
$query = $pdo->prepare($query_sql);
$query->execute($params);
$comissoes = $query->fetchAll(PDO::FETCH_ASSOC);

// Inicializa totais e informações do funcionário (se filtrado)
$total_pago = 0;
$total_a_pagar = 0;
$info_funcionario = null;
if (!empty($funcionario) && !empty($comissoes)) {
    // Se filtramos por um funcionário e encontramos resultados, pegamos os dados dele da primeira linha
    $info_funcionario = [
        'nome' => $comissoes[0]['nome_funcionario'],
        'tel' => $comissoes[0]['tel_funcionario'],
        'pix' => '<b>Chave:</b> ' . $comissoes[0]['tipo_chave_pix'] . ' <b>Pix:</b> ' . $comissoes[0]['chave_pix']
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Comissões</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <style>
        @page { margin: 0; }
        body { font-family: 'Times New Roman', Times, serif; margin: 5px 20px; }
        .footer { width: 100%; background-color: #ebebeb; padding: 5px; position: absolute; bottom: 0; text-align: center; font-size: 10px; }
        .cabecalho-principal { padding: 10px 0; margin-bottom: 20px; width: 100%; border-bottom: 1px solid #0340a3; }
        .cabecalho-info { position: relative; height: 120px; }
        .imagem-logo { width: 150px; position: absolute; right: 0; top: 10px; }
        .titulo-relatorio, .data-relatorio { position: absolute; left: 0; }
        .titulo-relatorio { top: 10px; font-size: 17px; font-weight: bold; text-decoration: underline; }
        .data-relatorio { top: 40px; font-size: 12px; }
        .texto-apuracao { font-size: 10px; text-decoration: underline; margin-bottom: 15px; }
        table.relatorio-tabela { width: 100%; border-collapse: collapse; font-size: 12px; vertical-align: middle; }
        table.relatorio-tabela th, table.relatorio-tabela td { border: 1px solid #dbdbdb; padding: 6px; text-align: center; }
        table.relatorio-tabela th { background-color: #ededed; font-size: 13px; }
        .texto-esquerda { text-align: left; }
        .resumo-relatorio, .info-funcionario { text-align: right; margin: 20px 0; font-size: 10px; font-weight: bold; }
        .resumo-relatorio span { margin-left: 20px; }
        .info-funcionario { text-align: center; font-size: 12px; }
        .info-funcionario span { margin: 0 15px; }
        .status-img { width: 11px; height: 11px; margin-right: 5px; }
        .vermelho-escuro { color: #a30303; font-weight: bold; }
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Comissões <?php echo $acao_rel . ($info_funcionario ? ' - Funcionário: ' . $info_funcionario['nome'] : ''); ?></div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </header>
    <div class="cabecalho-principal"></div>
    <main class="mx-2">
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>
        <?php if (!empty($comissoes)): ?>
            <table class="table table-striped relatorio-tabela">
                <thead>
                    <tr>
                        <th class="texto-esquerda">Serviço</th>
                        <th>Valor</th>
                        <th>Funcionário</th>
                        <th>Data Serviço</th>
                        <th>Vencimento</th>
                        <th>Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comissoes as $comissao):
                        $classe_debito = '';
                        $imagem = 'vermelho.jpg';
                        if ($comissao['pago'] == 'Sim') {
                            $total_pago += $comissao['valor'];
                            $imagem = 'verde.jpg';
                        } else {
                            $total_a_pagar += $comissao['valor'];
                            // Verifica se está vencido
                            if (new DateTime($comissao['data_venc']) < new DateTime(date('Y-m-d'))) {
                                $classe_debito = 'vermelho-escuro';
                            }
                        }
                    ?>
                    <tr class="<?php echo $classe_debito; ?>">
                        <td class="texto-esquerda">
                            <img class="status-img" src="<?php echo $url_sistema; ?>/sistema/img/<?php echo $imagem; ?>">
                            <?php echo htmlspecialchars($comissao['nome_servico'] ?? 'N/A'); ?>
                        </td>
                        <td>R$ <?php echo number_format($comissao['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($comissao['nome_funcionario'] ?? 'N/A'); ?></td>
                        <td><?php echo (new DateTime($comissao['data_lanc']))->format('d/m/Y'); ?></td>
                        <td><?php echo (new DateTime($comissao['data_venc']))->format('d/m/Y'); ?></td>
                        <td><?php echo htmlspecialchars($comissao['nome_cliente'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado para os filtros selecionados!</p>
        <?php endif; ?>
    </main>
    <?php if (!empty($comissoes)): ?>
    <div class="resumo-relatorio">
        <span>TOTAL DE COMISSÕES: <?php echo count($comissoes); ?></span>
        <span class="text-success">TOTAL PAGO: R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></span>
        <span class="text-danger">TOTAL A PAGAR: R$ <?php echo number_format($total_a_pagar, 2, ',', '.'); ?></span>
    </div>
    <div class="cabecalho-principal"></div>
    <?php endif; ?>

    <?php if ($info_funcionario): ?>
    <div class="info-funcionario">
        <span><b>Funcionário:</b> <?php echo $info_funcionario['nome']; ?></span>
        <span><b>Telefone:</b> <?php echo $info_funcionario['tel']; ?></span>
        <span><?php echo $info_funcionario['pix']; ?></span>
        <span class="text-danger"><b>Total a Receber:</b> R$ <?php echo number_format($total_a_pagar, 2, ',', '.'); ?></span>
    </div>
    <div class="cabecalho-principal"></div>
    <?php endif; ?>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>
</body>
</html>