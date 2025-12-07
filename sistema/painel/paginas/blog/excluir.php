<?php 
require_once("../../../conexao.php");
$tabela = 'blog';

$id = $_POST['id'];

// Delete image first
$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$imagem = @$res[0]['imagem'];
if($imagem != "sem-foto.jpg"){
    @unlink('../../img/blog/'.$imagem);
}

// Delete gallery images
$query = $pdo->query("SELECT * FROM imagens_blog where id_blog = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $row) {
    @unlink('../../img/blog/'.$row['imagem']);
}
$pdo->query("DELETE FROM imagens_blog WHERE id_blog = '$id'");

$pdo->query("DELETE FROM $tabela WHERE id = '$id'");

echo 'Excluído com Sucesso';
?>
