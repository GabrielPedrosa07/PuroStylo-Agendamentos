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
            filter: grayscale(100%); /* Optional: Make logo B&W */
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
        .status-pago { background-color: #000; }
        .status-pendente { background-color: #666; }
        .text-danger { color: #d9534f !important; }
        .text-success { color: #000 !important; } /* Success is Black in B&W theme */
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Comissões</div>
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
        
        <?php if (!empty($comissoes)): ?>
            <table class="table relatorio-tabela">
                <thead>
                    <tr>
                        <th width="30%">Serviço</th>
                        <th>Valor</th>
                        <th>Funcionário</th>
                        <th>Data</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comissoes as $comissao):
                        $total_geral = 0; // Just init
                        if ($comissao['pago'] == 'Sim') {
                            $total_pago += $comissao['valor'];
                            $status_class = 'status-pago';
                            $status_text = 'PAGO';
                        } else {
                            $total_a_pagar += $comissao['valor'];
                            $status_class = 'status-pendente';
                            $status_text = 'PENDENTE';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($comissao['nome_servico'] ?? 'N/A'); ?></td>
                        <td>R$ <?php echo number_format($comissao['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($comissao['nome_funcionario'] ?? 'N/A'); ?></td>
                        <td><?php echo (new DateTime($comissao['data_lanc']))->format('d/m/Y'); ?></td>
                        <td><?php echo (new DateTime($comissao['data_venc']))->format('d/m/Y'); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding: 20px;">Nenhum registro encontrado.</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($comissoes)): ?>
    <div class="resumo-relatorio">
        <span>ITEMS: <?php echo count($comissoes); ?></span>
        <span>PAGO: R$ <?php echo number_format($total_pago, 2, ',', '.'); ?></span>
        <span>PENDENTE: R$ <?php echo number_format($total_a_pagar, 2, ',', '.'); ?></span>
    </div>
    <?php endif; ?>

    <?php if ($info_funcionario): ?>
    <div style="text-align:center; font-size:12px; margin-top:20px; padding:10px; border:1px solid #ddd;">
        <b>DADOS BANCÁRIOS:</b><br>
        <?php echo $info_funcionario['nome']; ?> | 
        <?php echo $info_funcionario['pix']; ?>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php echo $nome_sistema; ?> | <?php echo $whatsapp_sistema; ?>
    </div>
</body>
</html>