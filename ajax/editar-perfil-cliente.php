<?php
@session_start();
require_once("../sistema/conexao.php");

$id = $_SESSION['id_cliente'];
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$endereco = $_POST['endereco'];
$data_nasc = $_POST['data_nasc'];
$senha = $_POST['senha'];
$conf_senha = $_POST['conf_senha'];

if($nome == ""){
    echo 'Preencha o Nome!';
    exit();
}
if($telefone == ""){
    echo 'Preencha o Telefone!';
    exit();
}

// Verificar se telefone ou email já existem para outro usuário
$query = $pdo->query("SELECT * FROM clientes WHERE (telefone = '$telefone' OR email = '$email') AND email != '' AND id != '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
    echo 'Email ou Telefone já cadastrado em outra conta!';
    exit();
}

$campos_senha = "";
if($senha != ""){
    if($senha != $conf_senha){
        echo 'As senhas não coincidem!';
        exit();
    }
    $senha_crip = md5($senha); 
    $campos_senha = ", senha = :senha, senha_crip = :senha_crip";
}

$query = $pdo->prepare("UPDATE clientes SET nome = :nome, telefone = :telefone, email = :email, endereco = :endereco, data_nasc = :data_nasc $campos_senha WHERE id = :id");

$query->bindValue(":nome", "$nome");
$query->bindValue(":telefone", "$telefone");
$query->bindValue(":email", "$email");
$query->bindValue(":endereco", "$endereco");
$query->bindValue(":data_nasc", "$data_nasc");
$query->bindValue(":id", "$id");

if($senha != ""){
    $query->bindValue(":senha", "$senha");
    $query->bindValue(":senha_crip", "$senha_crip");
}

$query->execute();

// Atualizar Sessão
$_SESSION['nome_cliente'] = $nome;
$_SESSION['telefone_cliente'] = $telefone;

echo 'Salvo com Sucesso';
?>
