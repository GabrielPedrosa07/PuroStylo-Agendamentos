<?php 
require_once("../../../conexao.php");
$tabela = 'clientes';

$id = $_POST['id'];
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$data_nasc = $_POST['data_nasc'];
$endereco = $_POST['endereco'];
$cartoes = $_POST['cartao'];
$email = $_POST['email'];
$senha = $_POST['senha'];

//validar telefone
$query = $pdo->query("SELECT * from $tabela where telefone = '$telefone'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0 and $id != $res[0]['id']){
	echo 'Telefone já Cadastrado, escolha outro!!';
	exit();
}

//validar email
if($email != ""){
    $query = $pdo->query("SELECT * from $tabela where email = '$email'");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    if(@count($res) > 0 and $id != $res[0]['id']){
        echo 'Email já Cadastrado, escolha outro!!';
        exit();
    }
}

$campos_senha = "";
if($senha != ""){
    $senha_crip = md5($senha); 
    $campos_senha = ", senha = :senha, senha_crip = :senha_crip";
}


if($id == ""){
    // Inserção
    if($senha == ""){
        // Senha padrão se não preenchida na criação? Ou obrigar? Vamos deixar vazio se não por
        $campos_senha = ""; 
    }
	$query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, telefone = :telefone, email = :email, data_cad = curDate(), data_nasc = '$data_nasc', cartoes = '$cartoes', endereco = :endereco, alertado = 'Não' $campos_senha");
}else{
    // Edição
	$query = $pdo->prepare("UPDATE $tabela SET nome = :nome, telefone = :telefone, email = :email, data_nasc = '$data_nasc', cartoes = '$cartoes', endereco = :endereco $campos_senha WHERE id = '$id'");
}

$query->bindValue(":nome", "$nome");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":email", "$email");
$query->bindValue(":endereco", "$endereco");

if($senha != ""){
    $query->bindValue(":senha", "$senha");
    $query->bindValue(":senha_crip", "$senha_crip");
}

$query->execute();

echo 'Salvo com Sucesso';
 ?>