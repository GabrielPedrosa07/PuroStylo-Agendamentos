<?php 
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
    // Isso é útil se, por exemplo, houver uma restrição de chave estrangeira que impeça a exclusão.
    error_log("Erro ao excluir agendamento: " . $e->getMessage()); // Loga o erro para o admin
    echo 'Falha ao excluir. Tente novamente.';
}
?>