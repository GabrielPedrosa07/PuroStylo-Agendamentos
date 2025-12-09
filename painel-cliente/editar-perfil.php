<?php 
$nivel = '../';
require_once("../cabecalho.php");
@session_start();

if(!isset($_SESSION['id_cliente'])){
    echo "<script>window.location='../login-cliente.php'</script>";
    exit();
}

$id_cliente = $_SESSION['id_cliente'];

// Buscar dados atuais do cliente
$query = $pdo->query("SELECT * FROM clientes WHERE id = '$id_cliente'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);

if(@count($res) > 0){
    $nome = $res[0]['nome'];
    $telefone = $res[0]['telefone'];
    $email = $res[0]['email'];
    $endereco = $res[0]['endereco'];
    $data_nasc = $res[0]['data_nasc'];
    $cpf = isset($res[0]['cpf']) ? $res[0]['cpf'] : ''; 
    $senha = $res[0]['senha'];
} else {
    // Fallback caso não encontre no banco (Sessão deve ter o básico)
    $nome = $_SESSION['nome_cliente'] ?? '';
    $telefone = $_SESSION['telefone_cliente'] ?? '';
    $email = '';
    $endereco = '';
    $data_nasc = '';
    $cpf = '';
    $senha = '';
} 

?>
<div class="layout_padding" style="background: white;">
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h3 class="text-center font-weight-bold">Editar Meu Perfil</h3>
                    </div>
                    <div class="card-body p-4">
                        
                        <form id="form-editar-perfil" method="post">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nome Completo</label>
                                    <input type="text" class="form-control" name="nome" value="<?php echo $nome ?>" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Telefone (WhatsApp)</label>
                                    <input type="text" class="form-control" name="telefone" id="telefone" value="<?php echo $telefone ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo $email ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Data de Nascimento</label>
                                    <input type="date" class="form-control" name="data_nasc" value="<?php echo $data_nasc ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Endereço</label>
                                <input type="text" class="form-control" name="endereco" value="<?php echo $endereco ?>">
                            </div>

                            <hr>
                            <h5 class="text-muted mb-3"><i class="fa fa-lock"></i> Segurança</h5>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nova Senha (deixe em branco para manter)</label>
                                    <input type="password" class="form-control" name="senha" placeholder="******">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" class="form-control" name="conf_senha" placeholder="******">
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" id="btn-salvar" class="btn btn-dark btn-lg px-5">Salvar Alterações</button>
                            </div>
                            
                            <div id="mensagem" class="text-center mt-3"></div>

                        </form>
                         <div class="text-center mt-3">
                            <a href="index.php" class="text-muted"><i class="fa fa-arrow-left"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once("../rodape.php") ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#telefone').mask('(00) 00000-0000');

        $('#form-editar-perfil').submit(function(e){
            e.preventDefault();
            $('#mensagem').text('');
            $('#btn-salvar').text('Salvando...').attr('disabled', true);

            $.ajax({
                url: "../ajax/editar-perfil-cliente.php",
                method: "post",
                data: $(this).serialize(),
                dataType: "text",
                success: function(msg){
                    if(msg.trim() === 'Salvo com Sucesso'){
                         $('#mensagem').addClass('text-success').removeClass('text-danger').text(msg);
                         // Atualiza página após 1s para refletir novos dados na sessão se a página recarregar
                         setTimeout(() => { window.location.reload(); }, 1500);
                    }else{
                         $('#mensagem').addClass('text-danger').removeClass('text-success').text(msg);
                    }
                    $('#btn-salvar').text('Salvar Alterações').attr('disabled', false);
                }
            })
        });
    });
</script>
