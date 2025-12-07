<?php require_once("cabecalho.php") ?>

<style>
    .sub_page .hero_area { min-height: auto; }
</style>

</div>
<!-- Close hero_area to separate header from content -->

<section class="layout_padding" style="background: white;">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
          Nosso Blog
        </h2>
        <p>
            Fique por dentro das novidades, eventos e dicas.
        </p>
      </div>

      <div class="row mt-5">
        <?php 
        $query = $pdo->query("SELECT * FROM blog WHERE ativo = 'Sim' ORDER BY data DESC");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if($total_reg > 0){
            for($i=0; $i < $total_reg; $i++){
                foreach ($res[$i] as $key => $value){}
                $id = $res[$i]['id'];
                $titulo = $res[$i]['titulo'];
                $descricao = $res[$i]['descricao'];
                $imagem = $res[$i]['imagem'];
                $data = $res[$i]['data'];
                $nome_url = $res[$i]['nome_url'];
                
                $dataF = implode('/', array_reverse(explode('-', $data)));
         ?>
         
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img src="sistema/painel/img/blog/<?php echo $imagem ?>" class="card-img-top" alt="<?php echo $titulo ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo $titulo ?></h5>
                    <small class="text-muted mb-2"><i class="fa fa-calendar"></i> <?php echo $dataF ?></small>
                    <p class="card-text flex-grow-1" style="color: #666;"><?php echo mb_strimwidth($descricao, 0, 100, "...") ?></p>
                    <a href="blog-post.php?id=<?php echo $id ?>" class="btn btn-primary mt-auto">Ler Mais</a>
                </div>
            </div>
        </div>

        <?php 
            }
        }else{
            echo '<p class="text-center w-100">Nenhum post encontrado.</p>';
        }
        ?>
      </div>
    </div>
</section>

<?php require_once("rodape.php") ?>
