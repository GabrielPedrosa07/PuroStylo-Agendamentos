<?php
// Inclui a conexão (garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4')
include_once '../../conexao.php';

// --- TRATAMENTO DAS VARIÁVEIS DE ENTRADA ---
// No novo fluxo, estas variáveis vêm diretamente do script "orquestrador", não do $_GET
// $dataInicial, $dataFinal, $filtro já estão disponíveis.

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
$acao_rel = 'Saídas / Despesas';
if ($filtro == 'Compra') {
    $acao_rel = 'Compras';
} elseif ($filtro == 'Comissão') {
    $acao_rel = 'Comissões';
} elseif (!empty($filtro)) {
    $acao_rel = 'Despesas';
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSULTA ÚNICA, SEGURA E OTIMIZADA ---
// Usamos múltiplos LEFT JOINs para buscar todos os dados relacionados em uma única consulta.
// Isso resolve o problema de performance "N+1" e a vulnerabilidade de SQL Injection.
// COALESCE é usado para encontrar o primeiro nome não nulo entre fornecedor e funcionário.
$query_sql = "
    SELECT 
        p.descricao,
        p.tipo,
        p.valor,
        p.data_pgto,
        ub.nome AS nome_usuario_pgto,
        COALESCE(f.nome, func.nome) AS nome_destinatario
    FROM 
        pagar p
    LEFT JOIN 
        fornecedores f ON p.pessoa = f.id
    LEFT JOIN 
        usuarios func ON p.funcionario = func.id
    LEFT JOIN 
        usuarios ub ON p.usuario_baixa = ub.id
    WHERE 
        p.data_pgto >= :dataInicial 
        AND p.data_pgto <= :dataFinal 
        AND p.tipo LIKE :filtro 
        AND p.pago = 'Sim' 
    ORDER BY 
        p.data_pgto ASC
";

$query = $pdo->prepare($query_sql);
$query->bindValue(':dataInicial', $dataInicial);
$query->bindValue(':dataFinal', $dataFinal);
$query->bindValue(':filtro', '%' . $filtro . '%');
$query->execute();
$saidas = $query->fetchAll(PDO::FETCH_ASSOC);

$total_saidas_valor = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Saídas</title>
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
    </style>
</head>
<body>

    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de <?php echo $acao_rel; ?></div>
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

        <?php if (!empty($saidas)): ?>
            <table class="table relatorio-tabela">
                <thead>
                    <tr>
                        <th width="35%">Descrição</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Pago Por</th>
                        <th>Destinado à</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($saidas as $saida):
                        $total_saidas_valor += $saida['valor'];
                        $valorF = number_format($saida['valor'], 2, ',', '.');
                        $data_pgtoF = (new DateTime($saida['data_pgto']))->format('d/m/Y');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($saida['descricao']); ?></td>
                        <td><?php echo htmlspecialchars($saida['tipo']); ?></td>
                        <td>R$ <?php echo $valorF; ?></td>
                        <td><?php echo $data_pgtoF; ?></td>
                        <td><?php echo htmlspecialchars($saida['nome_usuario_pgto'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($saida['nome_destinatario'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">Nenhum registro encontrado.</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($saidas)): ?>
    <div class="resumo-relatorio">
        <span>ITEMS: <?php echo count($saidas); ?></span>
        <span class="text-danger">TOTAL: R$ <?php echo number_format($total_saidas_valor, 2, ',', '.'); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php echo $nome_sistema; ?> | <?php echo $whatsapp_sistema; ?>
    </div>
</body>
</html>