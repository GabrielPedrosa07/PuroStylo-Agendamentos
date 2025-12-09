<?php 
require_once("sistema/conexao.php");

$nome = $_POST['nome'];
$telefone = $_POST['telefone'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$data_nasc = $_POST['data_nasc'];
$endereco = $_POST['endereco'];

if($nome == '' || $telefone == ''){
    echo 'Nome e Telefone são obrigatórios!';
    exit();
}

$senha_crip = md5($senha);

// Validar Duplicidade (Telefone ou Email)
// Build query dynamically or just be smarter
$query_check = "SELECT * FROM clientes WHERE (telefone = :telefone AND telefone != '')";
if(!empty($email)){
    $query_check .= " OR (email = :email AND email != '')";
}

$query = $pdo->prepare($query_check);
$query->bindValue(":telefone", "$telefone");
if(!empty($email)){
    $query->bindValue(":email", "$email");
}
$query->execute();
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if(@count($res) > 0){
    echo 'Email ou Telefone já cadastrado!';
    exit();
}

try {
    if($data_nasc == ""){
        $data_nasc = null;
    }
    
    $query = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, email = :email, senha = :senha, senha_crip = :senha_crip, data_nasc = :data_nasc, endereco = :endereco, data_cad = curDate(), alertado = 'Não', cartoes = '0'");
    $query->bindValue(":nome", "$nome");
    $query->bindValue(":telefone", "$telefone");
    $query->bindValue(":email", "$email");
    $query->bindValue(":senha", "$senha"); 
    $query->bindValue(":senha_crip", "$senha_crip");
    $query->bindValue(":data_nasc", $data_nasc);
    $query->bindValue(":endereco", "$endereco");
    $query->execute();

    @session_start();
    $_SESSION['id_cliente'] = $pdo->lastInsertId();
    $_SESSION['nome_cliente'] = $nome;
    $_SESSION['telefone_cliente'] = $telefone;

    echo 'Salvo com Sucesso';

} catch (Exception $e) {
    echo 'Erro ao Salvar: ' . $e->getMessage();
}
?>
