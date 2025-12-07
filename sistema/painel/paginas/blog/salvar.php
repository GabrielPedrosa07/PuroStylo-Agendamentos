<?php 
require_once("../../../conexao.php");

$tabela = 'blog';

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$conteudo = $_POST['conteudo'];
$data = $_POST['data'];
$palavras = $_POST['palavras'];
$ativo = 'Sim'; // Default active

// Validate title duplication
$query = $pdo->query("SELECT * FROM $tabela where titulo = '$titulo'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$id_reg = @$res[0]['id'];
if(@count($res) > 0 and $id != $id_reg){
	echo 'Título já cadastrado!';
	exit();
}

// Generate URL friendly slug
$nome_url = preg_replace('/[ -]+/' , '-' , $titulo);
$nome_url = preg_replace('/[áàãâä]/ui', 'a', $nome_url);
$nome_url = preg_replace('/[éèêë]/ui', 'e', $nome_url);
$nome_url = preg_replace('/[íìîï]/ui', 'i', $nome_url);
$nome_url = preg_replace('/[óòõôö]/ui', 'o', $nome_url);
$nome_url = preg_replace('/[úùûü]/ui', 'u', $nome_url);
$nome_url = preg_replace('/[ç]/ui', 'c', $nome_url);
$nome_url = preg_replace('/[^a-z0-9-]/i', '', $nome_url);
$nome_url = strtolower($nome_url);


// Image handling
$query = $pdo->query("SELECT * FROM $tabela where id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$foto_antiga = @$res[0]['imagem'];

if(@$_FILES['foto']['name'] != ""){
	$foto = $_FILES['foto'];
	
	if($foto['type'] == "image/jpeg" || $foto['type'] == "image/appplication/pdf" || $foto['type'] == "image/png" || $foto['type'] == "image/jpg" ){
		
		$nome_img = date('d-m-Y-H-i-s') .'-'.@$_FILES['foto']['name'];
		$nome_img = preg_replace('/[ :]+/' , '-' , $nome_img);

		$caminho = '../../img/blog/' .$nome_img;

		$imagem_temp = @$_FILES['foto']['tmp_name']; 
		
		if(@$_FILES['foto']['name'] != ""){
			move_uploaded_file($imagem_temp, $caminho);
            if($foto_antiga != "sem-foto.jpg" && $foto_antiga != ""){
                @unlink('../../img/blog/'.$foto_antiga);
            }
		}
	}else{
		echo 'Extensão de Imagem não permitida!';
		exit();
	}
}else{
    if($foto_antiga != ""){
	    $nome_img = $foto_antiga;
    }else{
        $nome_img = "sem-foto.jpg";
    }
}


if($id == ""){
	$query = $pdo->prepare("INSERT INTO $tabela SET titulo = :titulo, nome_url = :nome_url, descricao = :descricao, conteudo = :conteudo, data = :data, palavras = :palavras, imagem = :imagem, ativo = :ativo");
}else{
	$query = $pdo->prepare("UPDATE $tabela SET titulo = :titulo, nome_url = :nome_url, descricao = :descricao, conteudo = :conteudo, data = :data, palavras = :palavras, imagem = :imagem, ativo = :ativo WHERE id = '$id'");
}

$query->bindValue(":titulo", "$titulo");
$query->bindValue(":nome_url", "$nome_url");
$query->bindValue(":descricao", "$descricao");
$query->bindValue(":conteudo", "$conteudo");
$query->bindValue(":data", "$data");
$query->bindValue(":palavras", "$palavras");
$query->bindValue(":imagem", "$nome_img");
$query->bindValue(":ativo", "$ativo");
$query->execute();

echo 'Salvo com Sucesso';
?>
