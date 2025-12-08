<?php 
require_once("../sistema/conexao.php");
@session_start();

if(!isset($_SESSION['id_cliente'])){
    echo 'Erro: Usuário não logado!';
    exit();
}

$id = $_SESSION['id_cliente'];
$campo = $_POST['campo'];
$valor = $_POST['valor'];

try {
    $pdo->query("UPDATE clientes SET $campo = '$valor' WHERE id = '$id'");
    
    // Update session
    if($campo == 'nome') $_SESSION['nome_cliente'] = $valor;
    if($campo == 'telefone') $_SESSION['telefone_cliente'] = $valor;

    echo 'Salvo!';
} catch (Exception $e) {
    echo 'Erro ao salvar: ' . $e->getMessage();
}
?>
