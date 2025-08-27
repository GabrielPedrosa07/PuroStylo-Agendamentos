<?php require_once("sistema/conexao.php") ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  
  <!-- Site Metas -->
  <meta name="keywords" content="Puro Stylo, salão de beleza, cortes, estética" />
  <meta name="description" content="Fazemos todo tipo de serviço de beleza e estética. Confira nossos serviços, produtos e agende online." />
  <meta name="author" content="Puro Stylo" />
  
  <!-- Favicon -->
  <link rel="shortcut icon" href="images/<?php echo $icone_site ?>" type="image/x-icon">

  <title><?php echo $nome_sistema ?></title>

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <!--owl slider stylesheet -->
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />

  <!-- font awesome style -->
  <link href="css/font-awesome.min.css" rel="stylesheet" />

  <!-- Custom styles -->
  <link href="css/style.css" rel="stylesheet" />
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet" />
</head>

<body class="sub_page">
  <div class="hero_area">
    <div class="hero_bg_box">
      <img src="images/<?php echo $img_banner_index ?>" alt="Banner <?php echo $nome_sistema ?>">
    </div>

    <!-- Header -->
    <header class="header_section">
      <div class="container">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
          <!-- Logo / Nome -->
          <a class="navbar-brand" href="index"> <?php echo $nome_sistema ?> </a>

          <!-- Botão menu mobile -->
          <button class="navbar-toggler" type="button" data-toggle="collapse"
            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Menu -->
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto">

              <li class="nav-item active">
                <a class="nav-link" href="index">Home <span class="sr-only">(current)</span></a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="agendamentos">Agendamentos</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="produtos">Produtos</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="servicos">Serviços</a>
              </li>

              <!-- Ícones -->
              <li class="nav-item">
                <a title="Ir para o Sistema" class="nav-link" href="sistema" target="_blank">
                  <i class="fa fa-user" aria-hidden="true"></i>
                </a>
              </li>

              <li class="nav-item">
                <a title="Whatsapp" class="nav-link"
                  href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whatsapp ?>" target="_blank">
                  <i class="fa fa-whatsapp" aria-hidden="true"></i>
                </a>
              </li>

              <li class="nav-item">
                <a title="Instagram" class="nav-link" href="<?php echo $instagram_sistema ?>" target="_blank">
                  <i class="fa fa-instagram" aria-hidden="true"></i>
                </a>
              </li>

            </ul>
          </div>
        </nav>
      </div>
      
    </header>
    <!-- end header section -->
