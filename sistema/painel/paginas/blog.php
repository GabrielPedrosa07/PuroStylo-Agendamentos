<?php 
@session_start();
require_once("verificar.php");
require_once("../conexao.php");

$pag = 'blog';

//verificar se ele tem a permissão de estar nessa página
if(@$blog == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}
?>

<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">


<div class="">      
	<a class="btn btn-primary" onclick="inserir()" class="btn btn-primary btn-flat btn-pri"><i class="fa fa-plus" aria-hidden="true"></i> Novo Post</a>
</div>

<div class="bs-example widget-shadow" style="padding:15px" id="listar">
	
</div>

<!-- Modal Inserir-->
<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><span id="titulo_inserir"></span></h4>
				<button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			<form id="form">
			<div class="modal-body">

					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label for="titulo">Título</label>
								<input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título do Artigo" required>    
							</div> 	
						</div>
                        <div class="col-md-4">
                            <div class="form-group">
								<label for="data">Data</label>
								<input type="date" class="form-control" id="data" name="data" value="<?php echo date('Y-m-d') ?>" required>    
							</div>
                        </div>
					</div>

					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="descricao">Descrição Curta <small>(Resumo)</small></label>
								<textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="255" required></textarea>   
							</div> 	
						</div>
					</div>

                    <div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="conteudo">Conteúdo</label>
								<textarea class="form-control" id="conteudo" name="conteudo"></textarea>   
							</div> 	
						</div>
					</div>

                    <div class="row">
                         <div class="col-md-12">
                            <div class="form-group">
                                <label for="palavras">Palavras-chave <small>(Separadas por vírgula - para SEO)</small></label>
                                <input type="text" class="form-control" id="palavras" name="palavras" placeholder="ex: evento, inauguração, dicas">
                            </div>
                        </div>
                    </div>


					<div class="row">
						<div class="col-md-8">						
							<div class="form-group"> 
								<label>Imagem Capa</label> 
								<input class="form-control" type="file" name="foto" onChange="carregarImg();" id="foto">
							</div>						
						</div>
						<div class="col-md-4">
							<div id="divImg">
								<img src="img/blog/sem-foto.jpg"  width="100px" id="target">									
							</div>
						</div>
					</div>

					<input type="hidden" name="id" id="id">
					<br>
					<small><div id="mensagem" align="center"></div></small>
				</div>

				<div class="modal-footer">      
					<button type="submit" class="btn btn-primary">Salvar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Modal Imagens -->
<div class="modal fade" id="modalImagens" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Galeria de Imagens - <span id="nome_galeria"></span></h4>
				<button id="btn-fechar-imagens" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
					<span aria-hidden="true" >&times;</span>
				</button>
			</div>
			
			<div class="modal-body">
                <form id="form-imagens">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Imagem</label>
                                <input class="form-control" type="file" name="imagem" id="imagem_galeria" onchange="carregarImgGaleria();" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div id="divImgGaleria">
								<img src="img/blog/sem-foto.jpg"  width="60px" id="target_galeria">									
							</div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-12" align="right">
                             <button type="submit" class="btn btn-primary">Inserir</button>
                        </div>
                    </div>
                
                    <input type="hidden" name="id" id="id_galeria">
                </form>

                <hr>

                <div id="listar-imagens"></div>
			</div>
		</div>
	</div>
</div>



<script type="text/javascript">var pag = "<?=$pag?>"</script>
<script src="js/ajax.js"></script>
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
        $('#conteudo').summernote({
            placeholder: 'Escreva o conteúdo do artigo aqui...',
            tabsize: 2,
            height: 300
        });
    });

	function carregarImg() {
        var target = document.getElementById('target');
        var file = document.querySelector("#foto").files[0];
        
            var reader = new FileReader();

            reader.onloadend = function () {
                target.src = reader.result;
            };

            if (file) {
                reader.readAsDataURL(file);

            } else {
                target.src = "";
            }
    }

    function carregarImgGaleria() {
        var target = document.getElementById('target_galeria');
        var file = document.querySelector("#imagem_galeria").files[0];
        
            var reader = new FileReader();

            reader.onloadend = function () {
                target.src = reader.result;
            };

            if (file) {
                reader.readAsDataURL(file);

            } else {
                target.src = "";
            }
    }
</script>

<script type="text/javascript">
    $("#form-imagens").submit(function () {
        event.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/inserir-imagens.php",
            type: 'POST',
            data: formData,

            success: function (mensagem) {
                $('#mensagem').text('');
                $('#mensagem').removeClass()
                if (mensagem.trim() == "Salvo com Sucesso") {
                   listarImagens($('#id_galeria').val());
                   $('#imagem_galeria').val('');
                   $('#target_galeria').attr('src','img/blog/sem-foto.jpg');
                } else {
                    alert(mensagem)
                }
            },

            cache: false,
            contentType: false,
            processData: false,

        });
    });

    function listarImagens(id){
        $.ajax({
            url: 'paginas/' + pag + "/listar-imagens.php",
            method: 'POST',
            data: {id},
            dataType: "html",

            success:function(result){
                $("#listar-imagens").html(result);
            }
        });
    }
</script>
