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
    </style>
</head>
<body>
    <header class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Aniversariantes</div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </header>
    <div class="cabecalho-principal"></div>
    <main class="mx-2">
        <div class="texto-apuracao"><?php echo $texto_apuracao; ?></div>
        <?php if (!empty($aniversariantes)): ?>
            <table class="table table-striped relatorio-tabela">
                <thead>
                    <tr>
                        <th class="texto-esquerda">Nome</th>
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
                        <td class="texto-esquerda"><?php echo htmlspecialchars($aniversariante['nome']); ?></td>
                        <td><?php echo htmlspecialchars($aniversariante['telefone']); ?></td>
                        <td><?php echo $data_cadF; ?></td>
                        <td><?php echo $data_nascF; ?></td>
                        <td><?php echo htmlspecialchars($aniversariante['cartoes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum aniversariante encontrado para o período selecionado!</p>
        <?php endif; ?>
    </main>

    <?php if (!empty($aniversariantes)): ?>
    <div class="resumo-relatorio">
        <span>TOTAL DE ANIVERSARIANTES: <?php echo count($aniversariantes); ?></span>
    </div>
    <?php endif; ?>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>

</body>
</html>