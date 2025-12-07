<?php require_once("cabecalho.php") ?>
<style type="text/css">
	.sub_page .hero_area {
  min-height: auto;
}
</style>

</div>





  <?php 
$query = $pdo->query("SELECT p.*, u.telefone as tel_prof, u.nome as nome_prof FROM produtos p LEFT JOIN usuarios u ON p.usuario = u.id where p.estoque > 0 and p.valor_venda > 0 ORDER BY p.id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){ 
   ?>

  <section class="product_section layout_padding">
    <div class="container-fluid">
      <div class="heading_container heading_center ">
        <h2 class="">
          Nossos Produtos
        </h2>
        <p class="col-lg-8 px-0">
          Confira alguns de nossos produtos, damos desconto caso compre em grande quantidade.
        </p>
      </div>
      <div class="row">

<?php 
for($i=0; $i < $total_reg; $i++){
  foreach ($res[$i] as $key => $value){}
 
  $id = $res[$i]['id'];
  $nome = $res[$i]['nome'];   
  $valor = $res[$i]['valor_venda'];
  $foto = $res[$i]['foto'];
  $descricao = $res[$i]['descricao'];
  $tel_prof = $res[$i]['tel_prof'];
  $nome_prof = $res[$i]['nome_prof'];

  // Default to system whatsapp if no professional linked
  if(empty($tel_prof)) {
      $tel_prof = $tel_whatsapp; // from system config
      $nome_prof = "Loja";
  }

   $valorF = number_format($valor, 2, ',', '.');
 $nomeF = mb_strimwidth($nome, 0, 23, "...");

 ?>

        <div class="col-sm-6 col-md-3">
          <div class="box">
            <div class="img-box">
              <img src="sistema/painel/img/produtos/<?php echo $foto ?>" title="<?php echo $descricao ?>">
            </div>
            <div class="detail-box">
              <h5>
               <?php echo $nomeF ?>
              </h5>
              <h6 class="price">
                <span class="new_price">
                 R$ <?php echo $valorF ?>
                </span>
               
              </h6>
              
              <a href="javascript:void(0)" class="btn-add-cart" 
                 onclick="handleAddToCart(this, event)"
                 data-id="<?php echo $id ?>" 
                 data-name="<?php echo $nome ?>" 
                 data-price="<?php echo $valor ?>"
                 data-prof-tel="<?php echo $tel_prof ?>"
                 data-prof-name="<?php echo $nome_prof ?>">
               Adicionar ao Carrinho
              </a>

            </div>
          </div>
        </div>
            </div>
          </div>
        </div>
      
   <?php } ?>    


      </div>
      
    </div>
  </section>

<?php } ?>

  <!-- product section ends -->




 
   <?php require_once("rodape.php") ?>