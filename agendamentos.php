<?php 
require_once("cabecalho.php");
$data_atual = date('Y-m-d');

// --- BUSCA DE DADOS INICIAIS (FEITA DE FORMA SEGURA E ORGANIZADA) ---
$query_func = $pdo->query("SELECT id, nome FROM usuarios WHERE atendimento = 'Sim' ORDER BY nome ASC");
$funcionarios = $query_func->fetchAll(PDO::FETCH_ASSOC);

$query_serv = $pdo->query("SELECT id, nome, valor FROM servicos WHERE ativo = 'Sim' ORDER BY nome ASC");
$servicos = $query_serv->fetchAll(PDO::FETCH_ASSOC);
?>
<head>
    <style type="text/css">
        .sub_page .hero_area { min-height: auto; }
        
        /* ===== NOVOS ESTILOS PARA UM FORMULÁRIO MAIS BONITO ===== */

        /* Container dos horários com Flexbox para auto-ajuste */
        #listar-horarios {
            display: flex;
            flex-wrap: wrap;
            gap: 8px; /* Espaçamento entre os horários */
            justify-content: center;
            padding: 10px;
        }

        /* Esconde o radio button original */
        .horario-item input[type="radio"] {
            display: none;
        }

        /* Estilo do "chip" do horário */
        .horario-item label {
            display: block;
            padding: 6px 16px; /* Padding menor para botões mais compactos */
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            text-align: center;
            margin: 0;
        }
        
        /* Efeito ao passar o mouse */
        .horario-item label:hover {
            background-color: #e9ecef;
            border-color: #bbb;
        }

        /* Estilo do horário ocupado */
        .horario-item label.text-danger {
            color: #a9a9a9 !important; /* Cinza claro para o texto */
            text-decoration: line-through;
            background-color: #f8f9fa;
            border-color: #eee;
            cursor: not-allowed;
        }
        .horario-item label.text-danger:hover {
            background-color: #f8f9fa; /* Não muda de cor no hover */
        }


        /* Estilo do horário selecionado */
        .horario-item input[type="radio"]:checked + label {
            background: linear-gradient(45deg, #5a8e94, #48757a);
            color: white;
            border-color: #48757a;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Estilo para o plugin Select2 */
        .select2-selection__rendered { line-height: 45px !important; font-size:16px !important; color:#000 !important; }
        .select2-selection { height: 45px !important; font-size:16px !important; color:#000 !important; }
        .sub_page .hero_area { min-height: auto; }
        
        /* ===== NOVO VISUAL PARA SELEÇÃO DE FUNCIONÁRIO COM FOTOS ===== */
        .selecao-funcionario-container { margin-bottom: 20px; }
        .selecao-funcionario-container .label-titulo {
            font-weight: 600; color: #333; margin-bottom: 10px; display: block;
        }
        .selecao-funcionario {
            display: flex; gap: 15px; overflow-x: auto; padding-bottom: 15px;
        }
        .funcionario-card input[type="radio"] { display: none; }
        .funcionario-card-content {
            display: flex; flex-direction: column; align-items: center;
            gap: 8px; padding: 10px; border: 2px solid #e9ecef;
            border-radius: 10px; transition: all 0.3s ease;
            width: 110px; cursor: pointer; background: #fff;
        }
        .funcionario-card img {
            width: 70px; height: 70px; border-radius: 50%; object-fit: cover;
        }
        .funcionario-card span { font-size: 14px; font-weight: 500; color: #555; text-align: center; }
        .funcionario-card input[type="radio"]:checked + .funcionario-card-content {
            border-color: #5a8e94; background-color: #f0f7f8; transform: translateY(-5px);
        }
        
        /* (o resto do seu CSS para horários e select2 continua o mesmo) */
    </style>
</head>

</div> <div class="footer_section" style="background: #5a8e94; padding: 50px 0;">
    <div class="container">
        <div class="footer_content">
            <h2 class="text-center text-white mb-4">Faça seu Agendamento</h2>
            <form id="form-agenda" method="post" style="margin-top: -25px !important">
                <div class="footer_form footer-col">
                    <div class="form-group">
                        <input class="form-control" type="text" name="telefone" id="telefone" placeholder="Seu Telefone (DDD + número)" required />
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="text" name="nome" id="nome" placeholder="Seu Nome Completo" required />
                    </div>
<div class="selecao-funcionario-container">
    <label class="label-titulo"><?php echo htmlspecialchars($texto_agendamento) ?></label>
    <div class="selecao-funcionario">
        <?php 
        // A consulta no topo do arquivo precisa buscar a foto também.
        // Garanta que a consulta seja: SELECT id, nome, foto FROM usuarios...
        $query_func_fotos = $pdo->query("SELECT id, nome, foto FROM usuarios WHERE atendimento = 'Sim' ORDER BY nome ASC");
        $funcionarios_fotos = $query_func_fotos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($funcionarios_fotos as $func): 
        ?>
            <label class="funcionario-card">
                <input type="radio" name="funcionario" value="<?php echo $func['id'] ?>" required>
                <div class="funcionario-card-content">
                    <img src="sistema/painel/img/perfil/<?php echo $func['foto'] ?>" alt="<?php echo htmlspecialchars($func['nome']) ?>">
                    <span><?php echo htmlspecialchars(explode(' ', $func['nome'])[0]) ?></span>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>
                    <div class="form-group">
                        <input class="form-control" type="date" name="data" id="data" value="<?php echo $data_atual ?>" required />
                    </div>
                    <div class="form-group">
                        <select class="form-control sel2" id="servico" name="servico" style="width:100%;" required> 
                            <option value="">Selecione um Serviço</option>
                            <?php foreach ($servicos as $serv): ?>
                                <option value="<?php echo $serv['id'] ?>">
                                    <?php echo htmlspecialchars($serv['nome']) . ' - R$ ' . number_format($serv['valor'], 2, ',', '.') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div id="listar-horarios" class="bg-light rounded">
                            </div>
                    </div>
                    <div class="form-group">
                        <input maxlength="100" type="text" class="form-control" name="obs" id="obs" placeholder="Observações (Opcional)">
                    </div>
                    
                    <button id="btn-agendar" class="botao-verde" type="submit" style="width:100%;" disabled>Confirmar Agendamento</button>
                    <button id="btn-editar" class="botao-azul" type="submit" style="width:100%; display:none;">Editar Agendamento</button>
                    <button type="button" id="btn-excluir" style="width:100%; display:none;" data-toggle="modal" data-target="#modalExcluir">Excluir Agendamento</button>

                    <br><br>
                    <small><div id="mensagem" align="center"></div></small>
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="hora_rec" name="hora_rec">
                </div>
            </form>
            <div id="listar-cartoes" style="margin-top: -30px"></div>
        </div>
    </div>
</div>

<?php require_once("rodape.php") ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Inicializa o plugin Select2
        $('.sel2').select2();
        
        // Gatilhos de eventos
        $('#telefone').on('blur', buscarClientePorTelefone);
// O gatilho para os novos botões de rádio
$('input[name="funcionario"]').on('change', listarHorarios); 

// O gatilho para a data continua o mesmo
$('#data').on('change', listarHorarios);
        $('#form-agenda').on('submit', salvarAgendamento);
        $('#form-excluir').on('submit', excluirAgendamento);
    });

    // --- FUNÇÕES AJAX ---

    function buscarClientePorTelefone() {
        var tel = $('#telefone').val();
        if (tel.length < 10) { resetarFormulario(false); return; }
        listarCartoes(tel);
        $.ajax({
            url: "ajax/buscar-cliente.php",
            method: 'POST', data: { tel: tel }, dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    $('#nome').val(response.cliente_nome);
                    if (response.agendamento) {
                        $('#id').val(response.agendamento.id);
                        $('#data').val(response.agendamento.data);
                        $('#funcionario').val(response.agendamento.funcionario_id).trigger('change');
                        $('#servico').val(response.agendamento.servico_id).trigger('change');
                        $('#obs').val(response.agendamento.obs);
                        $('#hora_rec').val(response.agendamento.hora);
                        $('#id_excluir').val(response.agendamento.id);
                        $('#btn-agendar').hide();
                        $('#btn-editar, #btn-excluir').show();
                        $('#mensagem').text('Você já tem um agendamento. Pode editá-lo ou excluí-lo.').removeClass('text-danger text-success').addClass('text-info');
                    } else {
                        resetarFormulario(false);
                    }
                } else {
                    resetarFormulario(false);
                    $('#nome').focus();
                }
            }
        });
    }

    function listarHorarios() {
var funcionario = $('input[name="funcionario"]:checked').val();        var data = $('#data').val();
        var hora = $('#hora_rec').val();
        
        if (!funcionario || !data) {
            $('#listar-horarios').html('');
            $('#btn-agendar, #btn-editar').prop('disabled', true);
            return;
        }

        $('#listar-horarios').html('<div class="text-center p-3"><small>Buscando horários...</small></div>');
        $('#btn-agendar, #btn-editar').prop('disabled', true);
        
        $.ajax({
            url: "ajax/listar-horarios.php",
            method: 'POST', data: { funcionario, data, hora }, dataType: "json",
            success: function(result) {
                if (result.status === 'success') {
                    $('#listar-horarios').html(result.html);
                    $('#btn-agendar, #btn-editar').prop('disabled', false);
                } else {
                    $('#listar-horarios').html(result.message);
                }
            },
            error: function() {
                $('#listar-horarios').html('<div class="text-danger text-center">Erro ao carregar horários.</div>');
            }
        });
    }

    function salvarAgendamento(event) {
        if(event) event.preventDefault();
        var submitButton = $('#id').val() ? $('#btn-editar') : $('#btn-agendar');
        var buttonText = submitButton.text();
        submitButton.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: "ajax/agendar.php",
            type: 'POST', data: new FormData(document.getElementById('form-agenda')),
            success: function(response) {
                $('#mensagem').removeClass().text(response);
                if (response.trim().includes('Sucesso')) {
                    $('#mensagem').addClass('text-success');
                    buscarClientePorTelefone();
                } else {
                    $('#mensagem').addClass('text-danger');
                }
            },
            cache: false, contentType: false, processData: false,
            complete: function() {
                submitButton.prop('disabled', false).text(buttonText);
            }
        });
    }

    function excluirAgendamento(event) {
        if(event) event.preventDefault();
         $.ajax({
            url: "ajax/excluir.php", type: 'POST', data: new FormData(document.getElementById('form-excluir')),
            success: function (response) {
                if (response.trim() == "Cancelado com Sucesso") {
                    $('#btn-fechar-excluir').click();
                    $('#mensagem').removeClass().addClass('text-success').text(response);
                    resetarFormulario(false);
                } else {
                    $('#mensagem-excluir').addClass('text-danger').text(response);
                }
            },
            cache: false, contentType: false, processData: false,
        });
    }

    function listarCartoes(tel) {
        $.ajax({
            url: "ajax/listar-cartoes.php", method: 'POST', data: { tel: tel }, dataType: "html",
            success: function(result) { $("#listar-cartoes").html(result); }
        });
    }

    function resetarFormulario(limparTelefone = true){
        if(limparTelefone) $('#telefone').val('');
        $('#nome').val('');
        $('#id').val('');
        $('#hora_rec').val('');
        $('#obs').val('');
        $('#funcionario').val('').trigger('change');
        $('#servico').val('').trigger('change');
        $('#listar-horarios').html('');
        $('#btn-agendar').show().text('Confirmar Agendamento');
        $('#btn-editar, #btn-excluir').hide();
        $('#mensagem').text('');
    }
</script>