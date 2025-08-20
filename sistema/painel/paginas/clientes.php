<?php 
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'clientes';

//verificar se ele tem a permissão de estar nessa página
if(@$clientes == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}
?>

<div class="mb-3">
    <a class="btn btn-primary" onclick="inserir()" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Novo Cliente</a>
</div>

<div class="bs-example widget-shadow" style="padding:15px">
    <table class="table table-hover" id="tabela-clientes" style="width:100%;">
        <thead> 
            <tr> 
                <th>Nome</th>   
                <th class="esc">Telefone</th>   
                <th class="esc">Cadastro</th>   
                <th class="esc">Nascimento</th> 
                <th class="esc">Retorno</th> 
                <th class="esc">Cartões</th> 
                <th>Ações</th>
            </tr> 
        </thead> 
        <tbody>
            </tbody>
    </table>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true" >&times;</span>
                </button>
            </div>
            <form id="form" method="post">
                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-5 form-group">
                            <label>Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" placeholder="Telefone" >
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Cartões</label>
                            <input type="number" class="form-control" id="cartao" name="cartao" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" placeholder="Rua X Número 1 Bairro xxx" >
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Nascimento</label>
                            <input type="date" class="form-control" id="data_nasc" name="data_nasc" >
                        </div>
                    </div>
                    <input type="hidden" name="id" id="id">
                    <br>
                    <small><div id="mensagem" align="center"></div></small>
                </div>
                <div class="modal-footer">
                    <button type="submit" id="btn-salvar" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDados" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel"><span id="nome_dados"></span></h4>
                <button id="btn-fechar-perfil" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true" >&times;</span>
                </button>
            </div>
            <div class="modal-body">
                </div>
        </div>
    </div>
</div>


<script type="text/javascript">var pag = "<?=$pag?>";</script>

<script type="text/javascript">
    var table;
    $(document).ready(function() {
        // Inicializa o DataTables UMA VEZ, configurado para usar AJAX
        table = $('#tabela-clientes').DataTable({
            "ajax": `paginas/${pag}/listar.php`,
            "ordering": false,
            "stateSave": true,
            "columns": [
                { "data": "nome" },
                { "data": "telefone", "className": "esc" },
                { "data": "data_cad", "className": "esc" },
                { "data": "data_nasc", "className": "esc" },
                { "data": "data_retorno", "className": "esc" },
                { "data": "cartoes", "className": "esc" },
                { "data": "acoes" }
            ],
            "columnDefs": [{
                "targets": -1,
                "data": "acoes",
                "render": function (data, type, row, meta) {
                    var rowData = JSON.stringify(row).replace(/"/g, '&quot;'); // Escapa aspas para o HTML
                    return `
                        <big><a href="#" onclick='editar(${rowData})' title="Editar Dados"><i class="fa fa-edit text-primary"></i></a></big>
                        <big><a href="#" onclick='mostrar(${rowData})' title="Ver Dados"><i class="fa fa-info-circle text-secondary"></i></a></big>
                        <li class="dropdown head-dpdn2" style="display: inline-block;">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-trash-o text-danger"></i></big></a>
                            <ul class="dropdown-menu" style="margin-left:-230px;">
                                <li>
                                    <div class="notification_desc2">
                                        <p>Confirmar Exclusão? <a href="#" onclick="excluir(${row.id})"><span class="text-danger">Sim</span></a></p>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <big><a href="http://api.whatsapp.com/send?1=pt_BR&phone=${row.whats}&text=" target="_blank" title="Abrir Whatsapp"><i class="fa fa-whatsapp verde"></i></a></big>
                    `;
                }
            },
            {
                "targets": 4, 
                "createdCell": function (td, cellData, rowData, row, col) {
                    if (rowData.classe_retorno) $(td).addClass(rowData.classe_retorno);
                }
            }],
             "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json" }
        });

        // Inicializa os selects dos modais
        $('#modalForm .sel2').select2({ dropdownParent: $('#modalForm') });
    });

    // --- FUNÇÕES DE AÇÃO DOS BOTÕES (AGORA TODAS AQUI) ---

    function limparCampos(){
        $('#id').val('');
        $('#nome').val('');
        $('#telefone').val('');
        $('#endereco').val('');
        $('#data_nasc').val('');
        $('#cartao').val('0');
        $('#mensagem').text('');
    }

    function inserir(){
        limparCampos();
        $('#titulo_inserir').text('Inserir Registro');
        $('#modalForm').modal('show');
    }

    function editar(dados){
        $('#id').val(dados.id);
        $('#nome').val(dados.nome);
        $('#telefone').val(dados.telefone);
        $('#endereco').val(dados.endereco);
        $('#data_nasc').val(dados.data_nasc_raw);
        $('#cartao').val(dados.cartoes);
        
        $('#titulo_inserir').text('Editar Registro');
        $('#modalForm').modal('show');
        $('#mensagem').text('');
    }

    function mostrar(dados){
        $('#nome_dados').text(dados.nome);
        $('#data_cad_dados').text(dados.data_cad);
        $('#data_nasc_dados').text(dados.data_nasc);
        $('#cartoes_dados').text(dados.cartoes);
        $('#telefone_dados').text(dados.telefone);
        $('#endereco_dados').text(dados.endereco);
        $('#retorno_dados').text(dados.data_retorno);
        $('#servico_dados').text(dados.nome_servico);
        $('#modalDados').modal('show');
    }

    function excluir(id){
        $.ajax({
            url: `paginas/${pag}/excluir.php`,
            method: 'POST',
            data: {id},
            dataType: "text",
            success: function (mensagem) {
                if (mensagem.trim() == "Excluído com Sucesso") {
                    table.ajax.reload(); // Forma correta de atualizar o DataTable
                } else {
                    alert(mensagem); // Mostra outras mensagens de erro do servidor
                }
            },
        });
    }

    // --- SUBMISSÃO DO FORMULÁRIO DE INSERIR/EDITAR ---
    $("#form").submit(function (event) {
        event.preventDefault();
        $('#btn-salvar').prop('disabled', true).text('Salvando...');
        
        var formData = new FormData(this);

        $.ajax({
            url: `paginas/${pag}/salvar.php`,
            type: 'POST',
            data: formData,
            success: function (mensagem) {
                $('#mensagem').removeClass();
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar').click();
                    table.ajax.reload(); // Forma correta de atualizar o DataTable
                } else {
                    $('#mensagem').addClass('text-danger').text(mensagem);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
            complete: function() {
                $('#btn-salvar').prop('disabled', false).text('Salvar');
            }
        });
    });
</script>