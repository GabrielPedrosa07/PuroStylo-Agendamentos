<?php require_once("cabecalho.php") ?>


<style>
    .sub_page .hero_area { min-height: auto; }
</style>
</div>

<div class="layout_padding" style="background: white;">
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Cadastro de Cliente</h3>
                    
                    <form id="form-cadastro" method="post">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nome Completo</label>
                                <input type="text" name="nome" class="form-control" value="<?php echo @$_GET['nome'] ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Telefone (WhatsApp)</label>
                                <input type="text" name="telefone" id="telefone" class="form-control" placeholder="(XX) XXXXX-XXXX" value="<?php echo @$_GET['telefone'] ?>" required>
                            </div>
                        </div>

                        <div class="row">
                             <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Data de Nascimento</label>
                                <input type="date" name="data_nasc" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Confirmar Senha</label>
                                <input type="password" name="conf_senha" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Endereço / Bairro (Opcional)</label>
                            <input type="text" name="endereco" class="form-control">
                        </div>
                        
                        <div id="mensagem" align="center" class="mb-3"></div>

                        <button type="submit" id="btn-salvar" class="btn btn-primary btn-block" style="background-color: #000; border-color: #000;">Cadastrar</button>
                    </form>

                     <div class="text-center mt-3">
                        <p>Já tem conta? <a href="login-cliente.php" style="color: #000; font-weight: bold;">Faça Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<?php require_once("rodape.php") ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#telefone').mask('(00) 00000-0000');
    });

    $('#form-cadastro').submit(function(event){
        event.preventDefault();
        
        if($('input[name="senha"]').val() != $('input[name="conf_senha"]').val()){
             $('#mensagem').addClass('text-danger').text("As senhas não coincidem!");
             return;
        }

        $('#btn-salvar').prop('disabled', true).text('Cadastrando...');
        $('#mensagem').text('');

        // alert($('#form-cadastro').serialize()); // Debug line to verify data
        $.ajax({
            url: "inserir-cliente.php",
            method: "post",
            data: $('#form-cadastro').serialize(),
            dataType: "text",
            success: function(msg){
                if(msg.trim() === 'Salvo com Sucesso'){
                    alert("Cadastro com Sucesso! Redirecionando...");
                    window.location = 'agendamentos.php';
                }else{
                    $('#mensagem').addClass('text-danger').text(msg);
                    $('#btn-salvar').prop('disabled', false).text('Cadastrar');
                }
            }
        });
    });
</script>
