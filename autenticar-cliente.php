<?php 
@session_start();
require_once("sistema/conexao.php");

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];
$senha_crip = md5($senha);

$query = $pdo->prepare("SELECT * from clientes where (email = :usuario or telefone = :usuario) and senha_crip = :senha");
$query->bindValue(":usuario", "$usuario");
$query->bindValue(":senha", "$senha_crip");
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if(@count($res) > 0){
    $_SESSION['id_cliente'] = $res[0]['id'];
    $_SESSION['nome_cliente'] = $res[0]['nome'];
    $_SESSION['telefone_cliente'] = $res[0]['telefone'];
    
    echo "<script>window.location='agendamentos.php'</script>";
}else{
    echo "<script>window.alert('Dados Incorretos!')</script>";
    echo "<script>window.location='login-cliente.php'</script>";
}
?>
