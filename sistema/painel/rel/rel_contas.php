<?php
// Inclui a conexão (garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4')
include_once '../../conexao.php';

// --- TRATAMENTO DAS VARIÁVEIS DE ENTRADA ---
// No novo fluxo, estas variáveis vêm diretamente do script "orquestrador", não do $_GET.

// --- ZONA DE SEGURANÇA: VALIDAÇÃO E WHITELISTING ---
// Esta é a parte mais CRÍTICA. Nunca confie em nomes de tabelas ou colunas vindos do usuário.

// 1. Whitelist para a tabela: só permitimos 'pagar' ou 'receber'.
$allowed_tables = ['pagar', 'receber'];
if (!in_array($tabela, $allowed_tables)) {
    die('Erro: Tabela inválida selecionada.'); // Interrompe o script se a tabela for inválida
}

// 2. Whitelist para a coluna de busca: só permitimos colunas de data válidas.
$allowed_columns = ['data_lanc', 'data_venc', 'data_pgto'];
if (!in_array($busca, $allowed_columns)) {
    die('Erro: Filtro de data inválido selecionado.'); // Interrompe o script se a coluna for inválida
}
// Após a validação, as variáveis $tabela e $busca são seguras para serem usadas na query.

// --- LÓGICA DE PREPARAÇÃO DO RELATÓRIO ---
$dataInicialF = (new DateTime($dataInicial))->format('d/m/Y');
$dataFinalF = (new DateTime($dataFinal))->format('d/m/Y');

if ($dataInicial == $dataFinal) {
    $texto_apuracao = 'APURADO EM ' . $dataInicialF;
} elseif ($dataInicial == '1980-01-01') {
    $texto_apuracao = 'APURADO EM TODO O PERÍODO';
} else {
    $texto_apuracao = 'APURAÇÃO DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}

$acao_rel = '';
if ($pago == 'Sim') {
    $acao_rel = ' Pagas ';
} elseif ($pago == 'Não') {
    $acao_rel = ' Pendentes ';
}

if ($tabela == 'receber') {
    $texto_tabela = 'à Receber';
    $cor_tabela = 'text-success';
    $tabela_pago = 'RECEBIDAS';
} else {
    $texto_tabela = 'à Pagar';
    $cor_tabela = 'text-danger';
    $tabela_pago = 'PAGAS';
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSULTA DINÂMICA, SEGURA E OTIMIZADA ---
// Selecionamos apenas as colunas necessárias em vez de `SELECT *`.
// Os nomes da tabela e da coluna de busca são inseridos diretamente, pois já foram validados pela whitelist.
// Os dados (datas, status) são tratados de forma segura com prepared statements.
$query_sql = sprintf(
    "SELECT descricao, valor, data_lanc, data_venc, pago, data_pgto 
     FROM %s 
     WHERE (%s >= :dataInicial AND %s <= :dataFinal) 
     AND pago LIKE :pago 
     ORDER BY id DESC",
    $tabela,
    $busca, $busca // A coluna de busca é usada duas vezes
);

$query = $pdo->prepare($query_sql);
$query->bindValue(':dataInicial', $dataInicial);
$query->bindValue(':dataFinal', '2025-08-16');
$query->bindValue(':pago', '%' . $pago . '%');
$query->execute();
$contas = $query->fetchAll(PDO::FETCH_ASSOC);

$total_pago = 0;
$total_pendente = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Contas</title>
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
        .status-img { width: 11px; height: 11px; margin-right: 5px; }
    </style>
</head>
<body>

    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Contas <?php echo $texto_tabela . $acao_rel; ?></div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </header>

    <div class="cabecalho-principal"></div>

    <main class="mx-2">
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>

        <?php if (!empty($contas)): ?>
            <table class="table table-striped relatorio-tabela">
                <thead>
                    <tr>
                        <th class="texto-esquerda">Descrição</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Data PGTO</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas as $conta):
                        $imagem = 'vermelho.jpg';
                        if ($conta['pago'] == 'Sim') {
                            $total_pago += $conta['valor'];
                            $imagem = 'verde.jpg';
                        } else {
                            $total_pendente += $conta['valor'];
                        }

                        $data_pgtoF = (new DateTime($conta['data_pgto']))->format('d/m/Y');
                        if ($data_pgtoF == '01/01/1970' || $data_pgtoF == '00/00/0000') {
                            $data_pgtoF = 'Pendente';
                        }
                    ?>
                    <tr>
                        <td class="texto-esquerda">
                            <img class="status-img" src="<?php echo $url_sistema; ?>/sistema/img/<?php echo $imagem; ?>">
                            <?php echo htmlspecialchars($conta['descricao']); ?>
                        </td>
                        <td>R$ <?php echo number_format($conta['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo (new DateTime($conta['data_venc']))->format('d/m/Y'); ?></td>
                        <td><?php echo $data_pgtoF; ?></td>
                        <td><?php echo htmlspecialchars($conta['pago']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado para os filtros selecionados!</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($contas)): ?>
    <div class="resumo-relatorio">
        <span class="text-danger">TOTAL PENDENTE: R$ <?php echo number_format($total_pendente, 2, ',', '.'); ?></span>
        <span class="text-success">TOTAL <?php echo mb_strtoupper($tabela_pago); ?>: R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>

</body>
</html>