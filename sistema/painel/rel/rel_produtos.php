<?php
// Inclui a conexão com o banco de dados.
// Garanta que em 'conexao.php' a conexão PDO está com 'charset=utf8mb4'.
include_once '../../conexao.php';

// --- GERAÇÃO DA DATA EM PORTUGUÊS (Método Moderno) ---
// A função strftime() está obsoleta no PHP 8.1+.
// A classe IntlDateFormatter é a substituta oficial, mais poderosa e compatível com UTF-8.
try {
    $fmt = new IntlDateFormatter(
        'pt_BR',
        IntlDateFormatter::FULL, // Formato completo (longo) para a data
        IntlDateFormatter::NONE, // Sem hora
        'America/Sao_Paulo',
        IntlDateFormatter::GREGORIAN
    );
    $data_hoje = $fmt->format(new DateTime());
} catch (Exception $e) {
    // Caso a extensão intl não esteja habilitada, usamos um fallback simples.
    date_default_timezone_set('America/Sao_Paulo');
    $data_hoje = date('d/m/Y');
}

// --- CONSULTA ÚNICA E OTIMIZADA AO BANCO DE DADOS ---
// Usamos um LEFT JOIN para buscar os produtos e os nomes das suas categorias em uma única consulta.
// Isso evita o "problema N+1", melhorando drasticamente a performance.
$query_sql = "
    SELECT 
        p.id, p.nome, p.descricao, p.valor_compra, p.valor_venda, p.foto, p.estoque, p.nivel_estoque,
        c.nome AS nome_categoria
    FROM 
        produtos p
    LEFT JOIN 
        cat_produtos c ON p.categoria = c.id
    ORDER BY 
        p.nome ASC
";

// Usamos prepared statements, que é a prática mais segura, mesmo sem input do usuário.
$query = $pdo->prepare($query_sql);
$query->execute();
$produtos = $query->fetchAll(PDO::FETCH_ASSOC);

$estoque_baixo = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <style>
        /* Para garantir que o CSS seja aplicado no PDF, é melhor tê-lo inline. */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 5px 20px; /* Margens horizontais para o conteúdo */
        }
        .footer {
            width: 100%;
            background-color: #ebebeb;
            padding: 5px;
            position: absolute;
            bottom: 0;
            text-align: center;
            font-size: 10px;
        }
        .cabecalho-principal {
            padding: 10px 0;
            margin-bottom: 20px;
            width: 100%;
            border-bottom: 1px solid #0340a3;
        }
        .cabecalho-info {
            position: relative;
            height: 120px; /* Altura para acomodar logo e textos */
        }
        .imagem-logo {
            width: 150px;
            position: absolute;
            right: 0;
            top: 10px;
        }
        .titulo-relatorio, .data-relatorio {
            position: absolute;
            left: 0;
        }
        .titulo-relatorio {
            top: 10px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
        }
        .data-relatorio {
            top: 40px;
            font-size: 12px;
        }
        table.relatorio-tabela {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            vertical-align: middle;
        }
        table.relatorio-tabela th, table.relatorio-tabela td {
            border: 1px solid #dbdbdb;
            padding: 6px;
            text-align: center;
        }
        table.relatorio-tabela th {
            background-color: #ededed;
            font-size: 13px;
        }
        table.relatorio-tabela .texto-esquerda {
            text-align: left;
        }
        .img-produto {
            width: 27px;
            height: 27px;
            vertical-align: middle;
            margin-right: 5px;
        }
        .resumo-relatorio {
            text-align: right;
            margin: 20px 0;
            font-size: 10px;
            font-weight: bold;
        }
        .resumo-relatorio span {
            margin-left: 20px;
        }
    </style>
</head>
<body>

    <div class="cabecalho-info">
        <div class="titulo-relatorio">Relatório de Produtos</div>
        <div class="data-relatorio"><?php echo ucwords($data_hoje); ?></div>
        <img class="imagem-logo" src="<?php echo $url_sistema; ?>/sistema/img/logo_rel.jpg">
    </div>

    <div class="cabecalho-principal"></div>

    <main class="mx-2">
        <?php if (!empty($produtos)): ?>
            <table class="table table-striped relatorio-tabela">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Valor Compra</th>
                        <th scope="col">Valor Venda</th>
                        <th scope="col">Estoque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtos as $produto):
                        // Formata os valores monetários
                        $valor_compraF = number_format($produto['valor_compra'], 2, ',', '.');
                        $valor_vendaF = number_format($produto['valor_venda'], 2, ',', '.');
                        
                        // Define a classe de alerta para estoque baixo
                        $alerta_estoque = '';
                        if ($produto['estoque'] <= $produto['nivel_estoque']) {
                            $alerta_estoque = 'text-danger';
                            $estoque_baixo++;
                        }
                    ?>
                    <tr class="<?php echo $alerta_estoque; ?>">
                        <td class="texto-esquerda">
                            <img class="img-produto" src="<?php echo $url_sistema; ?>/sistema/painel/img/produtos/<?php echo $produto['foto']; ?>">
                            <?php echo htmlspecialchars($produto['nome']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($produto['nome_categoria'] ?? 'Sem Categoria'); ?></td>
                        <td>R$ <?php echo $valor_compraF; ?></td>
                        <td>R$ <?php echo $valor_vendaF; ?></td>
                        <td><?php echo $produto['estoque']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Não há registros de produtos para serem exibidos!</p>
        <?php endif; ?>
    </main>

    <div class="resumo-relatorio">
        <?php if (!empty($produtos)): ?>
            <span class="text-danger">PRODUTOS COM ESTOQUE BAIXO: <?php echo $estoque_baixo; ?></span>
            <span>TOTAL DE PRODUTOS: <?php echo count($produtos); ?></span>
        <?php endif; ?>
    </div>

    <div class="footer">
        <span><?php echo $nome_sistema; ?> | Whatsapp: <?php echo $whatsapp_sistema; ?></span>
    </div>

</body>
</html>