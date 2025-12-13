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
        /* ===== ESTILO PARA A NOVA SELEÇÃO DE FUNCIONÁRIOS COM FOTOS ===== */
        .selecao-funcionarios {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 5px 0 15px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .funcionario-card {
            cursor: pointer;
            text-align: center;
        }
        .funcionario-card input[type="radio"] {
            display: none;
        }
        .funcionario-card img {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 3px solid #e9ecef;
            object-fit: cover;
            transition: all 0.2s ease-in-out;
        }
        .funcionario-card span {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
        }
        .funcionario-card input[type="radio"]:checked + label img {
            border-color: #0d6efd;
            transform: scale(1.1);
        }
        .funcionario-card input[type="radio"]:checked + label span {
            color: #0d6efd;
            font-weight: 600;
        }

        /* ===== NOVO VISUAL PARA HORÁRIOS DISPONÍVEIS (BOTÕES EM GRADE) ===== */
/* ===== CSS CORRIGIDO PARA OS HORÁRIOS (USANDO FLEXBOX) ===== */
#listar-horarios {
    display: flex;             /* MUDANÇA 1: Usando flexbox */
    flex-wrap: wrap;           /* Permite que os itens quebrem para a próxima linha */
    gap: 10px;                 /* Espaçamento entre os botões */
    justify-content: flex-start; /* Alinha os itens à esquerda */
    padding: 10px;
    border-radius: 8px;
}

/* Garante que o wrapper de cada botão não interfira no layout flex */
#listar-horarios .form-check {
    flex-basis: 85px; /* Define uma largura base para cada botão */
    flex-grow: 1;     /* Permite que os botões cresçam para preencher espaços vazios */
    margin: 0 !important;
    padding: 0 !important;
}

#listar-horarios input[type="radio"] {
    display: none;
}

#listar-horarios label {
    display: block;
    width: 100%;
    padding: 10px 5px;
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    font-weight: 600;
    color: #495057;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    text-align: center;
}

#listar-horarios label:hover {
    border-color: #0d6efd;
    color: #0d6efd;
    transform: translateY(-1px);
}

#listar-horarios input[type="radio"]:checked + label {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    box-shadow: 0 3px 7px rgba(13, 110, 253, 0.3);
    transform: translateY(-2px);
}

#listar-horarios label.text-danger {
    color: #adb5bd !important;
    background-color: #f0f0f0;
    border-color: #f0f0f0;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}
        /* ===== NOVO VISUAL PARA OS CARDS DE AGENDAMENTOS DO DIA ===== */
        .appointment-card {
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            border-left-width: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
            transition: box-shadow 0.2s ease;
        }
        .appointment-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .status-agendado {
            border-left-color: #0d6efd; 
        }
        .status-concluido {
            border-left-color: #198754;
        }
        .appointment-card .card-body {
            padding: 0.8rem 1rem;
        }
        .appointment-card .time-block {
            margin-right: 15px;
            text-align: center;
            flex-shrink: 0;
        }
        .appointment-card .time-block .time {
            font-size: 1.3rem;
            font-weight: 700;
            color: #343a40;
        }
        .appointment-card .details-block h6 {
            font-weight: 600;
            margin-bottom: 0;
        }
        .appointment-card .details-block small {
            font-size: 0.85rem;
        }
        .appointment-card .action-menu a {
            text-decoration: none;
            color: #6c757d;
        }
        .dropdown-menu .dropdown-item {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== NOVO VISUAL PARA OS CARDS DE AGENDAMENTO ===== */
.agenda-card {
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    border-left-width: 5px; /* Borda de status mais grossa */
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.2s ease-in-out;
    margin-bottom: 12px !important; /* Garante o espaçamento */
}
.agenda-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}
/* Cores da borda para cada status */
.status-agendado {
    border-left-color: #0d6efd; /* Azul */
}
.status-concluido {
    border-left-color: #198754; /* Verde */
}
.status-aguardando {
    border-left-color: #ffc107; /* Amarelo */
}
.agenda-card .card-body {
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.agenda-card .info-principal {
    display: flex;
    align-items: center;
    gap: 15px; /* Espaço entre a hora e os detalhes */
}
.agenda-card .hora {
    font-size: 1.5rem;
    font-weight: 700;
    color: #343a40;
    text-align: center;
}
.agenda-card .status-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 50px;
}
.agenda-card .detalhes span {
    display: block; /* Cada informação em uma linha */
    color: #6c757d;
    font-size: 0.9rem;
}
.agenda-card .detalhes strong {
    color: #212529;
    font-size: 1.1rem;
}
.agenda-card .detalhes .fa {
    margin-right: 8px;
    width: 15px; /* Alinha os ícones */
    text-align: center;
}
.agenda-card .menu-acoes a {
    text-decoration: none;
    color: #6c757d;
}
    </style>
</head>
<div class="row">
    <div class="col-md-3">
        <button style="margin-bottom:10px" onclick="inserir()" type="button" class="btn btn-primary btn-flat btn-pri"><i class="fa fa-plus" aria-hidden="true"></i> Novo Agendamento</button>
    </div>

<div class="col-md-12">
    <div class="selecao-funcionarios">
        <?php 
        // Consulta para buscar funcionários com FOTO
        $query_func_fotos = $pdo->query("SELECT id, nome, foto FROM usuarios WHERE atendimento = 'Sim' ORDER BY nome ASC");
        $funcionarios_fotos = $query_func_fotos->fetchAll(PDO::FETCH_ASSOC);
        foreach ($funcionarios_fotos as $func): ?>
            <div class="funcionario-card">
                <input type="radio" name="funcionario_radio" onchange="mudarFuncionario()" id="func_<?php echo $func['id'] ?>" value="<?php echo $func['id'] ?>">
                <label for="func_<?php echo $func['id'] ?>">
                    <img src="img/perfil/<?php echo $func['foto'] ?>" title="<?php echo htmlspecialchars($func['nome']) ?>">
                    <span><?php echo htmlspecialchars(explode(' ', $func['nome'])[0]) ?></span>
                </label>
            </div>
        <?php endforeach; ?>
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

    // COLE ESTAS DUAS FUNÇÕES NO SEU JAVASCRIPT NO FINAL DA PÁGINA agendamentos.php

function excluir(id, hora){
    if(confirm("Deseja realmente excluir este agendamento das " + hora + "?")){
        $.ajax({
            url: 'paginas/agendamentos/excluir.php', // Verifique se o caminho está correto
            method: 'POST',
            data: {id: id},
            dataType: "text",
            success: function (mensagem) {
                if (mensagem.trim() == "Excluído com Sucesso") {
                    listar(); // Recarrega a lista de agendamentos
                } else {
                    // Exibe qualquer outra mensagem de erro que o PHP retornar
                    alert(mensagem);
                }
            },
            error: function(){
                alert("Ocorreu um erro de comunicação ao tentar excluir.");
            }
        });
    }
}

// FUNÇÃO NOVA E CORRIGIDA (COLE ESTA NO LUGAR DA ANTIGA)
function fecharServico(dados) { // Recebe um único objeto 'dados'
    // Agora acessamos as propriedades do objeto: dados.id, dados.cliente, etc.
    $('#id_agd').val(dados.id);
    $('#cliente_agd').val(dados.cliente);
    $('#servico_agd').val(dados.servico);
    $('#valor_serv_agd').val(dados.valor);
    $('#funcionario_agd').val(dados.funcionario).trigger('change');
    $('#titulo_servico').text(dados.nome_serv);
    $('#descricao_serv_agd').val(dados.nome_serv);

    // Abre o modal
    $('#modalServico').modal('show');
    $('#mensagem-servico').text(''); // Limpa mensagens de erro antigas
}
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
// VERSÃO NOVA E CORRIGIDA
function inserir(){
    // 1. Pega o ID do funcionário que foi selecionado na tela principal
    // Ele busca o radio button com o nome 'funcionario_radio' que está 'checked' (marcado)
    var funcionarioId = $('input[name="funcionario_radio"]:checked').val();

    // 2. Verifica se um funcionário foi realmente selecionado
    if (!funcionarioId) {
        alert('Por favor, selecione um Profissional clicando na foto antes de agendar!');
        return; // Para a execução da função aqui
    }

    // 3. Coloca o ID do funcionário no campo escondido DENTRO do formulário do modal
    // Este é o passo crucial que estava faltando!
    $('#id_funcionario').val(funcionarioId);

    // 4. Agora, o resto do seu código original para abrir o modal
    $('#mensagem').text('');
    $('#titulo_inserir').text('Inserir Registro');
    limparCampos(); // Sua função para limpar os campos
    $('#modalForm').modal('show');
    listarHorarios(); // Chama a função para buscar os horários já com o funcionário correto
}

    function limparCampos(){
        $('#id').val('');     
        $('#obs').val('');
        $('#cliente').val($('#cliente option:first').val()).trigger('change');
        $('#servico').val($('#servico option:first').val()).trigger('change');
        $('#data-modal').val($("#data_agenda").val());
    }
    
    // Função-chave que é chamada ao mudar o funcionário no select principal
function mudarFuncionario(){
    listar();   // Apenas atualiza a lista de agendamentos
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
// FUNÇÃO NOVA E CORRIGIDA (COLE ESTA NO LUGAR DA ANTIGA)
function fecharServico(dados) { // Recebe um único objeto 'dados'
    // Agora acessamos as propriedades do objeto: dados.id, dados.cliente, etc.
    $('#id_agd').val(dados.id);
    $('#cliente_agd').val(dados.cliente);
    $('#servico_agd').val(dados.servico);
    $('#valor_serv_agd').val(dados.valor);
    $('#funcionario_agd').val(dados.funcionario).trigger('change');
    $('#titulo_servico').text(dados.nome_serv);
    $('#descricao_serv_agd').val(dados.nome_serv);

    // Abre o modal
    $('#modalServico').modal('show');
    $('#mensagem-servico').text(''); // Limpa mensagens de erro antigas
}

    // --- FUNÇÕES AJAX PRINCIPAIS ---

    function listar(){
        var funcionario = $('input[name="funcionario_radio"]:checked').val();
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

// COLE ESTA FUNÇÃO CORRIGIDA NO LUGAR DA ANTIGA
function listarHorarios() {
    var funcionario = $('input[name="funcionario_radio"]:checked').val();

    // Se estiver usando a versão com o <select> em vez das fotos, use esta linha:
    // var funcionario = $('#funcionario').val();

    if (!funcionario) {
        $("#listar-horarios").html('<small class="text-muted">Selecione um Profissional.</small>');
        $('#btn-salvar').prop('disabled', true);
        return;
    }

    var data = $('#data-modal').val();
    var id_agd = $('#id').val();
    var hora_agd = $('#form-agendamento').data('hora-salva') || '';

    // ===== LINHA CORRIGIDA AQUI (TEXTO EM VEZ DE GIF) =====
    $('#listar-horarios').html('<div class="text-center p-3"><small>Carregando...</small></div>');
    $('#btn-salvar').prop('disabled', true);

    $.ajax({
        url: `paginas/${pag}/listar-horarios.php`,
        method: 'POST',
        data: {
            funcionario,
            data,
            id: id_agd,
            hora: hora_agd
        },
        dataType: "json",
        success: function(result) {
            if (result.status === 'success') {
                $("#listar-horarios").html(result.html);
                $('#btn-salvar').prop('disabled', false);
            } else {
                $("#listar-horarios").html(result.message);
                $('#btn-salvar').prop('disabled', true);
            }
        },
        error: function() {
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

    // --- NOVA FUNÇÃO DE CONFIRMAÇÃO ---
    function confirmarAgendamento(id, linkWhats){
        console.log("Iniciando confirmação...", id, linkWhats);
        
        // Remove verificação de null/undefined
        if(!linkWhats) linkWhats = "";

        if(confirm("Deseja confirmar este agendamento?")){
            $.ajax({
                url: 'paginas/' + pag + "/confirmar.php",
                method: 'POST',
                data: {id: id},
                dataType: "text",
                success: function (mensagem) {
                    console.log("Resposta do servidor:", mensagem);
                    if (mensagem.trim() == "Confirmado com Sucesso") {
                        listar();
                        // Abre WhatsApp
                        if(linkWhats != "") {
                             window.open(linkWhats, '_blank');
                        }
                    } else {
                        alert("Erro: " + mensagem); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erro AJAX:", status, error);
                    alert("Ocorreu um erro de comunicação. Verifique o console.");
                }
            });
        }
    }
</script>