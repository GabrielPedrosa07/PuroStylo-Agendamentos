<?php 
require_once("../../../conexao.php");

$id = $_POST['id'];

// Get image name to unlink
$query = $pdo->query("SELECT * FROM imagens_blog where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$imagem = @$res[0]['imagem'];

if($imagem != ""){
    @unlink('../../img/blog/'.$imagem);
}

$pdo->query("DELETE FROM imagens_blog WHERE id = '$id'");

echo 'Excluído com Sucesso';
?>
