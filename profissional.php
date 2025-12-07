<?php require_once("cabecalho.php") ?>

<?php 
$id = $_GET['id'];
$query = $pdo->query("SELECT * FROM usuarios WHERE id = '$id'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
if(@count($res) > 0){
    $nome = $res[0]['nome'];
    $foto = $res[0]['foto'];
    $email = $res[0]['email'];
    $telefone = $res[0]['telefone'];
    $telefone_link = preg_replace('/[^0-9]/', '', $telefone);
    $descricao = "Profissional especializado em diversos serviços de beleza."; // Placeholder or fetch if generic field exists
}else{
    echo "<script>window.location='index.php'</script>";
    exit();
}
?>

<section class="about_section layout_padding">
    <div class="container">
      <div class="row">
        <div class="col-md-5">
          <div class="img-box">
             <img src="sistema/painel/img/perfil/<?php echo $foto ?>" alt="<?php echo $nome ?>" style="width: 100%; border-radius: 15px; object-fit: cover;">
          </div>
        </div>
        <div class="col-md-7">
          <div class="detail-box">
            <div class="heading_container">
              <h2>
                <?php echo $nome ?>
              </h2>
            </div>
            <p style="margin-top: 20px;">
              <?php echo $descricao ?>
            </p>
            <p>
               <i class="fa fa-envelope" aria-hidden="true"></i> <?php echo $email ?> <br>
               <i class="fa fa-whatsapp" aria-hidden="true"></i> <?php echo $telefone ?>
            </p>
            <a href="agendamentos.php?func=<?php echo $id ?>" class="btn btn-primary" style="background-color: #000; border-color: #000; padding: 10px 25px;">
              Agendar Horário
            </a>
            <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $telefone_link ?>" target="_blank" class="btn btn-success" style="padding: 10px 25px; margin-left: 10px; background-color: #25d366; border-color: #25d366; color: white;">
              <i class="fa fa-whatsapp"></i> WhatsApp
            </a>
          </div>
        </div>
      </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="product_section layout_padding" style="background-color: #f9f9f9;">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
          Trabalhos Realizados
        </h2>
        <p>
            Alguns dos serviços realizados por <?php echo $nome ?>
        </p>
      </div>
      <div class="row">
        <?php 
        // Query to get unique services performed by this professional
        $query = $pdo->query("SELECT DISTINCT s.id, s.nome, s.foto, s.valor 
                              FROM agendamentos a 
                              INNER JOIN servicos s ON a.servico = s.id 
                              WHERE a.funcionario = '$id' AND a.status = 'Concluído' 
                              LIMIT 8"); // Limit to recent 8 unique services for variety
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        $total_reg = @count($res);
        if($total_reg > 0){
            for($i=0; $i < $total_reg; $i++){
                foreach ($res[$i] as $key => $value){}
                $nome_serv = $res[$i]['nome'];
                $foto_serv = $res[$i]['foto'];
                $valor_serv = $res[$i]['valor'];
                $valorF = number_format($valor_serv, 2, ',', '.');
        ?>
        <div class="col-sm-6 col-md-3">
          <div class="box">
            <div class="img-box">
              <img src="sistema/painel/img/servicos/<?php echo $foto_serv ?>" alt="<?php echo $nome_serv ?>">
            </div>
            <div class="detail-box">
              <h5>
                <?php echo $nome_serv ?>
              </h5>
              <div class="price">
                   R$ <?php echo $valorF ?>
              </div>
            </div>
          </div>
        </div>
        <?php 
            }
        }else{
            echo '<p class="text-center" style="width:100%">Nenhum serviço registrado recentemente no portfólio.</p>';
        }
        ?>
      </div>
    </div>
</section>

<?php require_once("rodape.php") ?>
