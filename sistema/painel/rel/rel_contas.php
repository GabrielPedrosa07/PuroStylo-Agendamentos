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
        .resumo-relatorio { 
            text-align: right; 
            margin: 20px 0; 
            font-size: 12px; 
            font-weight: bold; 
            padding: 10px;
            background: #f4f4f4;
            border-radius: 4px;
        }
        .resumo-relatorio span { 
            margin-left: 20px; 
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }
        .status-sim { background-color: #000; }
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Contas <?php echo $texto_tabela; ?></div>
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

        <?php if (!empty($contas)): ?>
            <table class="table relatorio-tabela">
                <thead>
                    <tr>
                        <th width="40%">Descrição</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contas as $conta):
                        if ($conta['pago'] == 'Sim') {
                            $total_pago += $conta['valor'];
                            $status_class = 'status-sim';
                        } else {
                            $total_pendente += $conta['valor'];
                            $status_class = 'status-nao';
                        }
                        $data_pgtoF = (new DateTime($conta['data_pgto']))->format('d/m/Y');
                        if ($data_pgtoF == '01/01/1970' || $data_pgtoF == '31/12/1969' || $data_pgtoF == '00/00/0000') {
                            $data_pgtoF = '-';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($conta['descricao']); ?></td>
                        <td>R$ <?php echo number_format($conta['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo (new DateTime($conta['data_venc']))->format('d/m/Y'); ?></td>
                        <td><?php echo $data_pgtoF; ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $conta['pago']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">Nenhum registro encontrado.</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($contas)): ?>
    <div class="resumo-relatorio">
        <span>PENDENTE: R$ <?php echo number_format($total_pendente, 2, ',', '.'); ?></span>
        <span>PAGO: R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php echo $nome_sistema; ?> | <?php echo $whatsapp_sistema; ?>
    </div>

</body>
</html>