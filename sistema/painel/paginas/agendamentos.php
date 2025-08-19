<?php 
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'agendamentos';
$data_atual = date('Y-m-d');

//verificar se ele tem a permissão de estar nessa página
if(@$agendamentos == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}

// --- BUSCA DE DADOS INICIAIS (FEITA DE FORMA SEGURA E ORGANIZADA NO TOPO) ---
$query_func = $pdo->query("SELECT id, nome FROM usuarios WHERE atendimento = 'Sim' ORDER BY nome ASC");
$funcionarios = $query_func->fetchAll(PDO::FETCH_ASSOC);

$query_cli = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$clientes = $query_cli->fetchAll(PDO::FETCH_ASSOC);

$query_serv = $pdo->query("SELECT id, nome FROM servicos ORDER BY nome ASC");
$servicos = $query_serv->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <style>
        .horarios-container { max-height: 200px; overflow-y: auto; padding: 5px; }
        .horario-item input[type="radio"] { display: none; }
        .horario-item label { display: block; padding: 8px 12px; background-color: #f0f0f0; border: 1px solid #ddd; border-radius: 25px; cursor: pointer; transition: all 0.2s ease-in-out; font-weight: 500; text-align: center; }
        .horario-item label.text-danger { color: #dc3545 !important; text-decoration: line-through; background-color: #f8d7da; border-color: #f5c6cb; cursor: not-allowed; }
        .horario-item input[type="radio"]:checked + label { background-color: #0d6efd; color: white; border-color: #0d6efd; }
    </style>
</head>

<div class="row">
    <div class="col-md-3">
        <button style="margin-bottom:10px" onclick="inserir()" type="button" class="btn btn-primary btn-flat btn-pri"><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
    </div>

    <div class="col-md-3">
        <div class="form-group">            
            <select class="form-control sel2" id="funcionario" name="funcionario" style="width:100%;" onchange="mudarFuncionario()"> 
                <option value="">Selecione um Funcionário</option>
                <?php foreach ($funcionarios as $func): ?>
                    <option value="<?php echo $func['id'] ?>"><?php echo htmlspecialchars($func['nome']) ?></option>
                <?php endforeach; ?>
            </select>   
        </div>  
    </div>

</div>
<input type="hidden" name="data_agenda" id="data_agenda" value="<?php echo date('Y-m-d') ?>"> 

<div class="row" style="margin-top: 15px">

    <div class="col-md-4 agile-calendar">
        <div class="calendar-widget">
            <div class="agile-calendar-grid">
                <div class="page">
                    <div class="w3l-calendar-left">
                        <div class="calendar-heading"></div>
                        <div class="monthly" id="mycalendar"></div>
                    </div>
                    <div class="clearfix"> </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xs-12 col-md-8 bs-example widget-shadow" style="padding:10px 5px; margin-top: 0px;" id="listar">
        </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="titulo_inserir"></h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-agendamento">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>Cliente</label> 
                            <select class="form-control sel3" id="cliente" name="cliente" style="width:100%;" required> 
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?php echo $cli['id'] ?>"><?php echo htmlspecialchars($cli['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>    
                        </div>
                        <div class="col-md-4 form-group">
                             <label>Serviço</label> 
                             <select class="form-control sel3" id="servico" name="servico" style="width:100%;" required> 
                                <?php foreach ($servicos as $serv): ?>
                                    <option value="<?php echo $serv['id'] ?>"><?php echo htmlspecialchars($serv['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>    
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Data </label> 
                            <input type="date" class="form-control" name="data" id="data-modal" value="<?php echo $data_atual ?>"> 
                        </div>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label>Horários Disponíveis</label>
                        <div id="listar-horarios" class="horarios-container">
                            <small>Selecionar Funcionário</small>
                        </div>
                    </div>
                    <hr>

                    <div class="form-group"> 
                        <label>OBS <small>(Máx 100 Caracteres)</small></label> 
                        <input maxlength="100" type="text" class="form-control" name="obs" id="obs">
                    </div>

                    <br>
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="id_funcionario" id="id_funcionario"> 
                    <small><div id="mensagem" align="center" class="mt-3"></div></small>                      
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btn-salvar" disabled>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalServico" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Serviço: <span id="titulo_servico"></span></h4>
                <button id="btn-fechar-servico" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="form-servico">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 form-group">
                            <label>Funcionário</label> 
                            <select class="form-control sel4" id="funcionario_agd" name="funcionario_agd" style="width:100%;" required> 
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?php echo $func['id'] ?>"><?php echo htmlspecialchars($func['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>    
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Valor </label> 
                            <input type="text" class="form-control" name="valor_serv_agd" id="valor_serv_agd"> 
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Data PGTO</label> 
                            <input type="date" class="form-control" name="data_pgto" id="data_pgto" value="<?php echo $data_atual ?>"> 
                        </div>  
                    </div>
                    <br>
                    <input type="hidden" name="id_agd" id="id_agd"> 
                    <input type="hidden" name="cliente_agd" id="cliente_agd"> 
                    <input type="hidden" name="servico_agd" id="servico_agd">
                    <input type="hidden" name="descricao_serv_agd" id="descricao_serv_agd">
                    <small><div id="mensagem-servico" align="center" class="mt-3"></div></small>                      
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">var pag = "<?=$pag?>";</script>

<script type="text/javascript" src="js/monthly.js"></script>
<script type="text/javascript">
    $(window).on('load', function() {
        $('#mycalendar').monthly({ mode: 'event' });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        // Inicializa o plugin Select2
        $('.sel2').select2();
        $('.sel3').select2({ dropdownParent: $('#modalForm') });
        $('.sel4').select2({ dropdownParent: $('#modalServico') });

        // Não lista mais ao carregar, espera o usuário selecionar um funcionário.
    });

    // --- FUNÇÕES GLOBAIS DA PÁGINA ---

    function inserir(){
        var funcionario = $('#funcionario').val();
        if (funcionario === "") {
            alert('Selecione um Funcionário antes de agendar!');
            return;
        }
        $('#mensagem').text('');
        $('#titulo_inserir').text('Inserir Registro');
        limparCampos();
        $('#modalForm').modal('show');
        listarHorarios();
    }

    function limparCampos(){
        $('#id').val('');     
        $('#obs').val('');
        $('#cliente').val($('#cliente option:first').val()).trigger('change');
        $('#servico').val($('#servico option:first').val()).trigger('change');
        $('#data-modal').val($("#data_agenda").val());
    }
    
    // Função-chave que é chamada ao mudar o funcionário no select principal
    function mudarFuncionario() {
        var funcionario = $('#funcionario').val();
        // Guarda o ID do funcionário selecionado no input hidden do formulário do modal
        $('#id_funcionario').val(funcionario);           
        listar(); // Atualiza a lista de agendamentos do dia
    }
    
    // Gatilho para quando a data no modal de agendamento é alterada
    $('#data-modal').on('change', function(){
        listarHorarios();
    });

    // --- FUNÇÕES DE AÇÃO DOS BOTÕES (EXCLUIR e FINALIZAR) ---
    // Essas funções precisam estar aqui para que o onclick as encontre.

    function excluir(id, hora){
        if(confirm("Deseja realmente excluir este agendamento das " + hora + "?")){
            $.ajax({
                url: 'paginas/' + pag + "/excluir.php",
                method: 'POST',
                data: {id},
                dataType: "text",
                success: function (mensagem) {
                    if (mensagem.trim() == "Excluído com Sucesso") {
                        listar();
                    } else {
                        alert(mensagem);
                    }
                },
            });
        }
    }

    function fecharServico(id_agd, cliente, servico, valor_serv, funcionario, nome_serv){
        $('#id_agd').val(id_agd);
        $('#cliente_agd').val(cliente);
        $('#servico_agd').val(servico);
        $('#valor_serv_agd').val(valor_serv);
        $('#funcionario_agd').val(funcionario).trigger('change');
        $('#titulo_servico').text(nome_serv);
        $('#descricao_serv_agd').val(nome_serv);
        $('#modalServico').modal('show');
        $('#mensagem-servico').text('');
    }

    // --- FUNÇÕES AJAX PRINCIPAIS ---

    function listar(){
        var funcionario = $('#funcionario').val();
        var data = $("#data_agenda").val(); 
        $("#data-modal").val(data);

        if (funcionario === "") {
            $("#listar").html(''); // Limpa a lista se nenhum funcionário estiver selecionado
            return;
        }

        $.ajax({
            url: `paginas/${pag}/listar.php`,
            method: 'POST',
            data: { data, funcionario },
            dataType: "html",
            success:function(result){
                $("#listar").html(result);
            }
        });
    }

    function listarHorarios(){
        var funcionario = $('#funcionario').val();
        if (funcionario === "") {
            $("#listar-horarios").html('<small class="text-muted">Selecione um Funcionário na tela principal.</small>');
            $('#btn-salvar').prop('disabled', true);
            return;
        }
        
        var data = $('#data-modal').val(); 
        var id_agd = $('#id').val();
        var hora_agd = $('#form-agendamento').data('hora-salva') || ''; 

        $('#listar-horarios').html('<div class="text-center p-3"><small>Carregando...</small></div>');
        $('#btn-salvar').prop('disabled', true);
        
        $.ajax({
            url: `paginas/${pag}/listar-horarios.php`,
            method: 'POST',
            data: { funcionario, data, id: id_agd, hora: hora_agd },
            dataType: "json", // A CORREÇÃO PRINCIPAL!

            success: function(result) {
                if(result.status === 'success'){
                    $("#listar-horarios").html(result.html);
                    $('#btn-salvar').prop('disabled', false);
                } else {
                    $("#listar-horarios").html(result.message);
                    $('#btn-salvar').prop('disabled', true);
                }
            },
            error: function(){
                 $("#listar-horarios").html('<div class="text-danger text-center">Erro ao carregar horários.</div>');
                 $('#btn-salvar').prop('disabled', true);
            }
        });
    }

    // --- SUBMISSÃO DOS FORMULÁRIOS ---

    // Este formulário é para o modal de NOVO AGENDAMENTO (`#modalForm`)
    $("#form-agendamento").submit(function (event) {
        event.preventDefault();
        $('#btn-salvar').prop('disabled', true).text('Salvando...');
        var formData = new FormData(this);

        $.ajax({
            url: `paginas/${pag}/inserir.php`,
            type: 'POST',
            data: formData,
            success: function (mensagem) {
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar').click();
                    listar(); 
                } else {
                    $('#mensagem').addClass('text-danger').text(mensagem);
                }
            },
            cache: false, contentType: false, processData: false,
            complete: function(){
                $('#btn-salvar').prop('disabled', false).text('Salvar');
            }
        });
    });

    // Este formulário é para o modal de FINALIZAR SERVIÇO (`#modalServico`)
    $("#form-servico").submit(function (event) {
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: `paginas/${pag}/inserir-servico.php`,
            type: 'POST',
            data: formData,
            success: function (mensagem) {
                if (mensagem.trim() == "Salvo com Sucesso") {
                    $('#btn-fechar-servico').click();
                    listar();
                } else {
                    $('#mensagem-servico').addClass('text-danger').text(mensagem);
                }
            },
            cache: false, contentType: false, processData: false,
        });
    });
</script>