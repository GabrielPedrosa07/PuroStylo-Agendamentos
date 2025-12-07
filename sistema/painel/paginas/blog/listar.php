<?php 
require_once("../../../conexao.php");
$tabela = 'blog';

$query = $pdo->query("SELECT * FROM $tabela ORDER BY id desc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);
if($total_reg > 0){

echo <<<HTML
	<small>
	<table class="table table-hover" id="tabela">
	<thead> 
	<tr> 
	<th>Imagem</th>	
	<th class="esc">Título</th> 	
	<th class="esc">Data</th> 	
	<th class="esc">Ativo</th> 
	<th>Ações</th>
	</tr> 
	</thead> 
	<tbody>	
HTML;

for($i=0; $i < $total_reg; $i++){
	foreach ($res[$i] as $key => $value){}
	$id = $res[$i]['id'];
	$titulo = $res[$i]['titulo'];	
	$descricao = $res[$i]['descricao'];
	$imagem = $res[$i]['imagem'];
	$data = $res[$i]['data'];
	$ativo = $res[$i]['ativo'];
    $palavras = $res[$i]['palavras'];
    // We don't list conteude here, it is too big. We fetch it via ajax when editing.

	$dataF = implode('/', array_reverse(explode('-', $data)));
    
    if($ativo == 'Sim'){
        $classe_ativo = 'text-success';
        $ativo_texto = 'Sim';
    }else{
        $classe_ativo = 'text-danger';
        $ativo_texto = 'Não';
    }

echo <<<HTML
<tr>
<td>
<img src="img/blog/{$imagem}" width="27px" class="mr-2">
</td>
<td class="esc">{$titulo}</td>
<td class="esc">{$dataF}</td>
<td class="esc {$classe_ativo}">{$ativo_texto}</td>

<td>
		<big><a href="#" onclick="editar('{$id}','{$titulo}', '{$data}', '{$ativo}', '{$imagem}', '{$palavras}')" title="Editar Dados"><i class="fa fa-edit text-primary"></i></a></big>

        <big><a href="#" onclick="imagens('{$id}','{$titulo}')" title="Galeria de Imagens"><i class="fa fa-image text-dark"></i></a></big>

		<li class="dropdown head-dpdn2" style="display: inline-block;">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>

		<ul class="dropdown-menu" style="margin-left:-230px;">
		<li>
		<div class="notification_desc2">
		<p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id}')"><span class="text-danger">Sim</span></a></p>
		</div>
		</li>										
		</ul>
		</li>
		</td>
</tr>
HTML;

}

echo <<<HTML
</tbody>
<small><div align="center" id="mensagem-excluir"></div></small>
</table>
</small>
HTML;


}else{
	echo '<small>Não possui nenhum registro Cadastrado!</small>';
}

?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#tabela').DataTable({
    		"ordering": false,
			"stateSave": true
    	});
    $('#tabela_filter label input').focus();
} );
</script>


<script type="text/javascript">
	function editar(id, titulo, data, ativo, imagem, palavras){
		$('#id').val(id);
		$('#titulo').val(titulo);
		$('#data').val(data);
		$('#palavras').val(palavras);
						
		$('#titulo_inserir').text('Editar Registro');
		$('#modalForm').modal('show');
		$('#foto').val('');
		$('#target').attr('src','img/blog/' + imagem);

        // Fetch description and content via AJAX to avoid cluttering the table
        $.ajax({
            url: 'paginas/' + pag + "/buscar-dados.php",
            method: 'POST',
            data: {id},
            dataType: "json",

            success:function(result){
               $('#descricao').val(result.descricao);
               $('#conteudo').summernote('code', result.conteudo);
            }
        });
	}

	function limparCampos(){
		$('#id').val('');
		$('#titulo').val('');
        $('#data').val('<?=date('Y-m-d')?>');
        $('#palavras').val('');
		$('#descricao').val('');
        $('#conteudo').summernote('code', '');
		$('#foto').val('');
		$('#target').attr('src','img/blog/sem-foto.jpg');
	}

    function imagens(id, nome){
        $('#id_galeria').val(id);
        $('#nome_galeria').text(nome);
        listarImagens(id);
        $('#modalImagens').modal('show');
    }
</script>
