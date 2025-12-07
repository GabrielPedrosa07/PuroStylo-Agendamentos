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
        .text-success { color: #000 !important; }
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
        .img-produto {
            width: 30px;
            height: 30px;
            object-fit: cover;
            border-radius: 4px;
            vertical-align: middle;
            margin-right: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <header class="cabecalho-info">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="text-align: left; vertical-align: top;">
                    <div class="titulo-relatorio" style="position: static; font-size: 24px; font-weight: bold; text-transform: uppercase;">Relatório de Produtos</div>
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
        <?php if (!empty($produtos)): ?>
            <table class="table relatorio-tabela">
                <thead>
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">V. Compra</th>
                        <th scope="col">V. Venda</th>
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
                        <td style="text-align:left;">
                            <!-- Invalidating DomPDF cache / path issues by using Base64 -->
                            <?php 
                            $path_img_prod = 'C:/wamp64/www/PuroStylo-Agendamentos/sistema/painel/img/produtos/' . $produto['foto'];
                            if(file_exists($path_img_prod) && !empty($produto['foto']) && $produto['foto'] != 'sem-foto.jpg'){
                                $type_prod = pathinfo($path_img_prod, PATHINFO_EXTENSION);
                                $data_prod = file_get_contents($path_img_prod);
                                $base64_prod = 'data:image/' . $type_prod . ';base64,' . base64_encode($data_prod);
                                echo '<img class="img-produto" src="'.$base64_prod.'">';
                            } else {
                                // Optional: Fallback image if desired, or just empty
                                // echo '<img class="img-produto" src="placeholder_base64...">';
                            }
                            ?>
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
        <?php echo $nome_sistema; ?> | <?php echo $whatsapp_sistema; ?>
    </div>

</body>
</html>