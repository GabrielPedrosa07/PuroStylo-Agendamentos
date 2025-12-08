<?php require_once("cabecalho.php") ?>


<style>
    .sub_page .hero_area { min-height: auto; }
</style>
</div>

<div class="layout_padding" style="background: white;">
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Login do Cliente</h3>
                    
                    <form action="autenticar-cliente.php" method="post">
                        <div class="form-group">
                            <label>Email ou Telefone</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Seu email ou telefone" required>
                        </div>
                        <div class="form-group">
                            <label>Senha</label>
                            <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block" style="background-color: #000; border-color: #000;">Entrar</button>
                    </form>

                    <div class="text-center mt-3">
                        <p>Ainda não tem conta? <a href="cadastro-cliente.php" style="color: #000; font-weight: bold;">Cadastre-se</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<?php require_once("rodape.php") ?>
