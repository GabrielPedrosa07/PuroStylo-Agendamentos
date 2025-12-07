<?php
// Inclui a conexão (garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4')
include_once '../../conexao.php';

// --- TRATAMENTO DAS VARIÁVEIS DE ENTRADA ---
// No novo fluxo, estas variáveis vêm diretamente do script "orquestrador", não do $_GET.

// --- LÓGICA DE PREPARAÇÃO DO RELATÓRIO ---
$dataInicialF = (new DateTime($dataInicial))->format('d/m/Y');
$dataFinalF = (new DateTime($dataFinal))->format('d/m/Y');

if ($dataInicial == $dataFinal) {
    $texto_apuracao = 'ANIVERSARIANTES DO DIA ' . $dataInicialF;
} else {
    $texto_apuracao = 'ANIVERSARIANTES DE ' . $dataInicialF . ' ATÉ ' . $dataFinalF;
}

// --- GERAÇÃO DA DATA ATUAL (Método Moderno) ---
try {
    $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'America/Sao_Paulo');
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- LÓGICA CORRETA E SEGURA PARA CONSULTA DE ANIVERSÁRIOS ---
// Convertemos as datas de nascimento e os filtros para o formato 'mês-dia' ('%m-%d')
// Isso permite comparar aniversários ignorando o ano.

$dataInicialObj = new DateTime($dataInicial);
$dataFinalObj = new DateTime($dataFinal);

$inicio_mmdd = $dataInicialObj->format('m-d');
$fim_mmdd = $dataFinalObj->format('m-d');

$params = [
    ':inicio_mmdd' => $inicio_mmdd,
    ':fim_mmdd' => $fim_mmdd
];

// Verificamos se o período cruza o ano (ex: Dezembro -> Janeiro)
if ($inicio_mmdd > $fim_mmdd) {
    // Lógica para períodos que cruzam o ano
    $condicao_data = "(DATE_FORMAT(data_nasc, '%m-%d') >= :inicio_mmdd OR DATE_FORMAT(data_nasc, '%m-%d') <= :fim_mmdd)";
} else {
    // Lógica para períodos dentro do mesmo "ano virtual"
    $condicao_data = "DATE_FORMAT(data_nasc, '%m-%d') BETWEEN :inicio_mmdd AND :fim_mmdd";
}

$query_sql = sprintf(
    "SELECT nome, telefone, data_cad, data_nasc, cartoes 
     FROM clientes 
     WHERE %s 
     ORDER BY MONTH(data_nasc), DAY(data_nasc), id ASC",
    $condicao_data
);

$query = $pdo->prepare($query_sql);
$query->execute($params);
$aniversariantes = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Aniversariantes</title>
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
    </style>
</head>
<body>

    <header class="cabecalho-info">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="text-align: left; vertical-align: top;">
                    <div class="titulo-relatorio" style="position: static; font-size: 24px; font-weight: bold; text-transform: uppercase;">Relatório de Aniversariantes</div>
                    <div class="data-relatorio" style="position: static; font-size: 12px; color: #555; margin-top: 5px;"><?php echo ucwords($data_hoje); ?></div>
                </td>
                <td style="text-align: right; vertical-align: top; width: 130px;">
                    <?php 
                    $path_logo = 'C:/wamp64/www/PuroStylo-Agendamentos/sistema/img/logo_rel.jpg';
                    $type_logo = pathinfo($path_logo, PATHINFO_EXTENSION);
                    $data_logo = file_get_contents($path_logo);
                    $base64_logo = 'data:image/' . $type_logo . ';base64,' . base64_encode($data_logo);
                    ?>
                    <img class="imagem-logo" src="<?php echo $base64_logo; ?>" style="width: 120px; position: static;">
                </td>
            </tr>
        </table>
    </header>

    <main>
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>

        <?php if (!empty($aniversariantes)): ?>
            <table class="table relatorio-tabela">
                <thead>
                    <tr>
                        <th width="35%">Nome</th>
                        <th>Telefone</th>
                        <th>Cadastro</th>
                        <th>Nascimento</th>
                        <th>Cartões</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aniversariantes as $aniversariante):
                        $data_cadF = (new DateTime($aniversariante['data_cad']))->format('d/m/Y');
                        $data_nascF = (new DateTime($aniversariante['data_nasc']))->format('d/m/Y');
                        if ($data_nascF == '01/01/1970' || $aniversariante['data_nasc'] == '0000-00-00') {
                            $data_nascF = 'Sem Lançamento';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($aniversariante['nome']); ?></td>
                        <td><?php echo htmlspecialchars($aniversariante['telefone']); ?></td>
                        <td><?php echo $data_cadF; ?></td>
                        <td><?php echo $data_nascF; ?></td>
                        <td><?php echo htmlspecialchars($aniversariante['cartoes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center; padding: 20px;">Nenhum aniversariante encontrado para o período selecionado!</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($aniversariantes)): ?>
    <div class="resumo-relatorio">
        <span>TOTAL DE ANIVERSARIANTES: <?php echo count($aniversariantes); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?>
    </div>
</body>
</html>