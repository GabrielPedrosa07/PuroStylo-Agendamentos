<?php
require_once("conexao.php");

// Cria um usuário padrão se não existir (apenas para garantia, igual ao original)
$query = $pdo->query("SELECT * FROM usuarios");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg == 0){
    $senha_crip = md5('123');
    $pdo->query("INSERT INTO usuarios SET nome = 'Administrador', cpf = '000.000.000-00', email = 'contato@hugocursos.com.br', senha_crip = '$senha_crip', senha = '123', nivel = 'Administrador', data = curDate(), ativo = 'Sim', foto = 'sem-foto.jpg', telefone = '(00)00000-0000', atendimento = 'Sim'");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?></title>
    <link rel="shortcut icon" href="img/icone.png" type="image/x-icon">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('../images/espaco1.jpg') no-repeat center center fixed;
            background-size: cover;
            overflow: hidden;
        }

        /* Overlay escuro com blur */
        body::before {
            content: '';
            position: absolute;
            top: 0; 
            left: 0;
            right: 0; 
            bottom: 0;
            background: rgba(0, 0, 0, 0.6); /* Escurece a imagem */
            backdrop-filter: blur(8px); /* Desfoque */
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.1); /* Vidro */
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeInDown 1s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-img {
            width: 120px;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.3));
        }

        h2 {
            color: #fff;
            margin-bottom: 30px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.5rem;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px;
            height: 50px;
            padding-left: 15px;
            font-size: 15px;
        }

        .form-control:focus {
            background: #fff;
            box-shadow: 0 0 15px rgba(255,255,255,0.5);
        }

        .btn-login {
            background: #fff;
            color: #000;
            width: 100%;
            height: 50px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #000;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .footer-login {
            margin-top: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Logo -->
        <img src="img/<?php echo $logo_sistema ?>" class="logo-img" alt="Logo">
        
        <h2>Bem-vindo</h2>

        <form action="autenticar.php" method="post">
            <div class="form-floating">
                <input type="text" name="email" class="form-control" id="floatingInput" placeholder="Email ou CPF" required>
                <label for="floatingInput">Email ou CPF</label>
            </div>
            
            <div class="form-floating">
                <input type="password" name="senha" class="form-control" id="floatingPassword" placeholder="Senha" required>
                <label for="floatingPassword">Senha</label>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="footer-login">
            PuroStylo System &copy; <?php echo date('Y') ?>
        </div>
    </div>

</body>
</html>
