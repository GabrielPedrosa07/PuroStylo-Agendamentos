<?php require_once("cabecalho.php") ?>

<style>
    .sub_page .hero_area { min-height: auto; }
</style>

</div>
<!-- Close hero_area to separate header from content -->

<section class="layout_padding" style="background: white;">

<?php
$id = @$_GET['id'];
$query = $pdo->query("SELECT * FROM blog WHERE id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
    $titulo = $res[0]['titulo'];
    $conteudo = $res[0]['conteudo'];
    $data = $res[0]['data'];
    $imagem = $res[0]['imagem'];
    $palavras = $res[0]['palavras'];
    
    $dataF = implode('/', array_reverse(explode('-', $data)));
}else{
    echo "<script>window.location='blog.php'</script>";
    exit();
}
?>

<section class="layout_padding">
    <div class="container">
      <div class="row">
        <div class="col-md-9 mx-auto">
            <h2 class="mb-3"><?php echo $titulo ?></h2>
            <p class="text-muted"><i class="fa fa-calendar"></i> <?php echo $dataF ?></p>
            
            <img src="sistema/painel/img/blog/<?php echo $imagem ?>" class="img-fluid rounded mb-4 w-100" alt="<?php echo $titulo ?>" style="max-height: 500px; object-fit: cover;">
            
            <div class="blog-content" style="font-size: 1.1em; line-height: 1.8; color: #333;">
                <?php echo $conteudo ?>
            </div>

            <!-- Gallery Section -->
            <?php 
            $query = $pdo->query("SELECT * FROM imagens_blog WHERE id_blog = '$id'");
            $res = $query->fetchAll(PDO::FETCH_ASSOC);
            $total_reg = @count($res);
            if($total_reg > 0){
            ?>
            <div class="mt-5">
                <h4>Galeria de Fotos</h4>
                <div class="row mt-3">
                    <?php 
                    for($i=0; $i < $total_reg; $i++){
                        foreach ($res[$i] as $key => $value){}
                        $imagem_galeria = $res[$i]['imagem'];
                    ?>
                    <div class="col-md-4 mb-3">
                        <a href="sistema/painel/img/blog/<?php echo $imagem_galeria ?>" target="_blank">
                            <img src="sistema/painel/img/blog/<?php echo $imagem_galeria ?>" class="img-fluid rounded shadow-sm" style="height: 200px; width:100%; object-fit: cover;">
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <div class="mt-5">
                <a href="blog.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Voltar</a>
            </div>

        </div>
      </div>
    </div>
</section>

<?php require_once("rodape.php") ?>
