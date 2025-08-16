<?php 
require_once("../../../conexao.php");
$tabela = 'receber';

// Certifica-se de que o ID foi enviado e não está vazio
if (empty($_POST['id'])) {
    echo 'ID não fornecido.';
    exit(); // Encerra o script
}

$id = $_POST['id'];

try {
    // 1. BUSCA O REGISTRO A SER EXCLUÍDO DE FORMA SEGURA
    // Usamos prepared statements para evitar SQL Injection.
    $query_select = $pdo->prepare("SELECT foto, produto, quantidade FROM $tabela WHERE id = :id");
    $query_select->bindValue(":id", $id);
    $query_select->execute();
    $dados_receber = $query_select->fetch(PDO::FETCH_ASSOC); // Usamos fetch() pois esperamos apenas 1 resultado

    // 2. VERIFICA SE O REGISTRO REALMENTE EXISTE ANTES DE CONTINUAR
    if ($dados_receber) {
        $foto = $dados_receber['foto'];
        $produto_id = $dados_receber['produto'];
        $quantidade = $dados_receber['quantidade'];

        // 3. APAGA A FOTO ASSOCIADA (se houver e existir)
        if ($foto != "sem-foto.jpg" && file_exists('../../img/contas/'.$foto)) {
            @unlink('../../img/contas/'.$foto);
        }

        // 4. DEVOLVE A QUANTIDADE AO ESTOQUE DO PRODUTO (SE HOUVER PRODUTO)
        if (!empty($produto_id)) {
            // Busca o estoque atual do produto de forma segura
            $query_produto = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :produto_id");
            $query_produto->bindValue(":produto_id", $produto_id);
            $query_produto->execute();
            $dados_produto = $query_produto->fetch(PDO::FETCH_ASSOC);

            if ($dados_produto) {
                $estoque_atual = $dados_produto['estoque'];
                $novo_estoque = $estoque_atual + $quantidade;

                // Atualiza o estoque do produto de forma segura
                $query_update = $pdo->prepare("UPDATE produtos SET estoque = :novo_estoque WHERE id = :produto_id");
                $query_update->bindValue(":novo_estoque", $novo_estoque);
                $query_update->bindValue(":produto_id", $produto_id);
                $query_update->execute();
            }
        }

        // 5. FINALMENTE, EXCLUI O REGISTRO DA TABELA 'receber' DE FORMA SEGURA
        $query_delete = $pdo->prepare("DELETE FROM $tabela WHERE id = :id");
        $query_delete->bindValue(":id", $id);
        $query_delete->execute();

        echo 'Excluído com Sucesso';

    } else {
        echo 'Registro não encontrado.';
    }

} catch (PDOException $e) {
    // Captura exceções do banco de dados para tratar erros.
    echo 'Erro ao processar a solicitação: ' . $e->getMessage();
}
?>