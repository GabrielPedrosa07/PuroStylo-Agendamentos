<?php 
require_once("../../../conexao.php");

$id = $_POST['id']; // Blog post ID

if(@$_FILES['imagem']['name'] != ""){
	$foto = $_FILES['imagem'];
	
	if($foto['type'] == "image/jpeg" || $foto['type'] == "image/appplication/pdf" || $foto['type'] == "image/png" || $foto['type'] == "image/jpg" ){
		
		$nome_img = date('d-m-Y-H-i-s') .'-'.@$_FILES['imagem']['name'];
		$nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

		$caminho = '../../img/blog/' .$nome_img;

		$imagem_temp = @$_FILES['imagem']['tmp_name']; 
		
		if(@$_FILES['imagem']['name'] != ""){
			move_uploaded_file($imagem_temp, $caminho);
		}

        $pdo->query("INSERT INTO imagens_blog SET id_blog = '$id', imagem = '$nome_img'");
        echo 'Salvo com Sucesso';

	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}else{
    echo 'Selecione uma Imagem';
}
?>
