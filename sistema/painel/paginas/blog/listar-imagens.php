<?php 
require_once("../../../conexao.php");
$id = $_POST['id']; // Blog post ID

$query = $pdo->query("SELECT * FROM imagens_blog where id_blog = '$id' ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

if($total_reg > 0){
    echo '<div class="row">';
    for($i=0; $i < $total_reg; $i++){
        foreach ($res[$i] as $key => $value){}
        $id_img = $res[$i]['id'];
        $imagem = $res[$i]['imagem'];

        echo <<<HTML
        <div class="col-md-3" style="margin-bottom: 20px;">
            <img src="img/blog/{$imagem}" width="100%" height="150" style="object-fit: cover;">
            <div align="center">
                <a href="#" onclick="excluirImagem('{$id_img}')" title="Excluir Imagem"><span class="text-danger">Excluir</span></a>
            </div>
        </div>
HTML;
    }
    echo '</div>';
    
    echo <<<HTML
   <script>
    function excluirImagem(id){
        $.ajax({
            url: 'paginas/' + pag + "/excluir-imagem.php",
            method: 'POST',
            data: {id},
            success:function(result){
                if(result.trim() == "Excluído com Sucesso"){
                     listarImagens($('#id_galeria').val());
                }else{
                    alert(result);
                }
            }
        });
    }
   </script> 
HTML;

}else{
    echo '<small>Nenhuma imagem na galeria deste post.</small>';
}
?>
