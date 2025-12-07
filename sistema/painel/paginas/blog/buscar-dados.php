<?php 
require_once("../../../conexao.php");

$id = $_POST['id'];

$query = $pdo->query("SELECT * FROM blog where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if(@count($res) > 0){
    $descricao = $res[0]['descricao'];
    $conteudo = $res[0]['conteudo'];
    
    $dados = array(
        'descricao' => $descricao,
        'conteudo' => $conteudo
    );

    echo json_encode($dados);
}
?>
