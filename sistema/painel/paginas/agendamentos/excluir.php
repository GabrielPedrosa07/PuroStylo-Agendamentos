<?php
// O nome da tabela é fixo, o que é bom para a segurança.
$tabela = 'agendamentos';
require_once("../../../conexao.php");

// Valida se o ID foi realmente enviado.
if (empty($_POST['id'])) {
    echo 'ID não fornecido.';
    exit();
}

$id = $_POST['id'];

try {
    // Usamos prepare() para criar uma consulta segura com um placeholder :id
    $query = $pdo->prepare("DELETE FROM $tabela WHERE id = :id");
    
    // Associamos a variável $id ao placeholder :id de forma segura.
    $query->bindValue(':id', $id);
    
    // Executamos a consulta
    $query->execute();

    echo 'Excluído com Sucesso';

} catch (PDOException $e) {
    // Em caso de erro no banco de dados, informa o problema.
    echo 'Falha ao excluir. Erro: ' . $e->getMessage();
}
?>