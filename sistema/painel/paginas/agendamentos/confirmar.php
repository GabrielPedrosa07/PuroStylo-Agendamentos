<?php 
require_once("../../../conexao.php");

$id = $_POST['id'];

// Atualiza o status
$pdo->query("UPDATE agendamentos SET status = 'Confirmado' WHERE id = '$id'");

echo 'Confirmado com Sucesso';
?>
