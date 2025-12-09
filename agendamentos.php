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
        
        /* ===== ESTILOS GERAIS ===== */
        body {
            background-color: #f4f4f4;
            color: #333;
            font-family: 'Poppins', sans-serif;
        }

        /* ===== container PRINCIPAL ===== */
        .scheduling_section {
            background: #ffffff !important; /* Fundo branco total */
            padding: 40px 0;
            min-height: 80vh; /* Altura mínima */
            display: block; 
        }

        .scheduling_form_content {
            background: #fff;
            padding: 20px;
            width: 100%;
            margin: 0 auto;
            border: none !important; 
            box-shadow: none !important; 
        }

        h2.titulo-agendamento {
            text-align: center;
            color: #222;
            font-weight: 700;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ===== FORMULÁRIO COM ANIMAÇÃO ===== */
        .form-animate {
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group input, .form-group select {
            height: 50px !important;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding-left: 20px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: #000; /* Foco preto */
            box-shadow: none;
        }

        /* ===== SELEÇÃO DE FUNCIONÁRIO ===== */
        .selecao-funcionario-container { margin-bottom: 25px; text-align: center; }
        .label-titulo {
            font-weight: 600; color: #555; margin-bottom: 15px; display: block; font-size: 1.1rem;
        }
        .selecao-funcionario {
            display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;
        }
        .funcionario-card { position: relative; cursor: pointer; }
        .funcionario-card input[type="radio"] { display: none; }
        .funcionario-card-content {
            display: flex; flex-direction: column; align-items: center;
            gap: 10px; padding: 10px; border: 2px solid transparent;
            border-radius: 15px; transition: all 0.3s ease;
            width: 100px;
        }
        .funcionario-card img {
            width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
            border: 3px solid #eee; transition: all 0.3s;
        }
        .funcionario-card span { font-size: 14px; font-weight: 600; color: #555; }
        
        .funcionario-card input[type="radio"]:checked + .funcionario-card-content img {
            border-color: #000; transform: scale(1.05);
        }
         .funcionario-card input[type="radio"]:checked + .funcionario-card-content span {
            color: #000;
        }

        /* ===== HORÁRIOS ===== */
        #listar-horarios {
            display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;
            padding: 15px; background: #fafafa; border-radius: 10px; border: 1px solid #eee;
        }
        .horario-item label {
            padding: 8px 20px; background: #fff; border: 1px solid #ccc;
            border-radius: 30px; cursor: pointer; transition: all 0.2s;
            font-weight: 500; font-size: 14px; color: #333;
        }
        .horario-item label:hover { background: #eee; }
        .horario-item input[type="radio"]:checked + label {
            background: #000; color: #fff; border-color: #000; /* Preto */
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .horario-item label.text-danger {
            background: #f9f9f9; color: #ccc !important; border-color: #eee;
            text-decoration: line-through; cursor: not-allowed;
        }

        /* ===== BOTÕES ===== */
        .botao-preto {
            background: #000; color: #fff; padding: 15px; font-weight: 600;
            border: none; border-radius: 8px; transition: transform 0.2s, box-shadow 0.2s;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .botao-preto:hover {
            background: #333; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: #fff;
        }
         .botao-azul { /* Estilo para editar (manter ou mudar par cinza) */
             background: #333; color: #fff; border: none; padding: 15px; border-radius: 8px;
         }

        /* ===== MENSAGEM ===== */
        #mensagem { font-weight: 600; font-size: 16px; margin-top: 15px; }

        /* Select2 override */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ddd !important; border-radius: 8px !important; height: 50px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 50px !important; padding-left: 20px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
        }

    </style>
</head>

<div class="scheduling_section layout_padding">
    <div class="container">
        <!-- Conteúdo centralizado e estilizado -->
        <div class="scheduling_form_content form-animate">
            <h2 class="titulo-agendamento">Agende seu Horário</h2>
            
            <?php if(!isset($_SESSION['id_cliente'])){ ?>
                <div class="text-center mb-5">
                    <div class="alert alert-warning">
                        <h4><i class="fa fa-lock"></i> Área Restrita</h4>
                        <p>Para realizar agendamentos, é necessário acessar sua conta.</p>
                    </div>
                    <a href="login-cliente.php" class="btn btn-dark" style="margin-right: 10px;">Fazer Login</a>
                    <a href="cadastro-cliente.php" class="btn btn-outline-dark">Criar Conta</a>
                </div>
            <?php } else { 
                // Initialize with Session data (Fallback)
                $nome_cliente = $_SESSION['nome_cliente'];
                $telefone_cliente = $_SESSION['telefone_cliente'];

                $id_cliente = $_SESSION['id_cliente'];
                $query_cli = $pdo->query("SELECT * FROM clientes WHERE id = '$id_cliente'");
                $res_cli = $query_cli->fetchAll(PDO::FETCH_ASSOC);

                if(@count($res_cli) > 0){
                    if(!empty($res_cli[0]['nome'])) $nome_cliente = $res_cli[0]['nome'];
                    if(!empty($res_cli[0]['telefone'])) $telefone_cliente = $res_cli[0]['telefone'];
                    
                    // Update session to match DB
                    $_SESSION['nome_cliente'] = $nome_cliente;
                    $_SESSION['telefone_cliente'] = $telefone_cliente;
                }
            ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                     <span class="text-muted d-block">Logado como:</span>
                     <h4><strong><?php echo $_SESSION['nome_cliente'] ?></strong></h4>
                     <span class="text-muted"><i class="fa fa-phone"></i> <?php echo $_SESSION['telefone_cliente'] ?></span>
                </div>
                
                <div class="text-right">
                     <a href="painel-cliente/index.php" class="btn btn-sm btn-outline-dark mb-2 d-block"><i class="fa fa-calendar"></i> Meus Agendamentos</a>
                     <a href="painel-cliente/editar-perfil.php" class="btn btn-sm btn-outline-primary mb-2 d-block"><i class="fa fa-edit"></i> Editar Perfil</a>
                     <a href="logout-cliente.php" class="btn btn-sm btn-danger d-block"><i class="fa fa-sign-out"></i> Sair</a>
                </div>
            </div>

            <form id="form-agenda" method="post">
                <!-- Inputs ocultos movidos para dentro do form -->
                <input type="hidden" name="nome" id="nome" value="<?php echo $_SESSION['nome_cliente'] ?>">
                <input type="hidden" name="telefone" id="telefone" value="<?php echo $_SESSION['telefone_cliente'] ?>">

                <div class="footer_form">
                    
                    <!-- Linha 1 removida pois agora é fixo via hidden inputs acima -->

                    <!-- Seleção de Funcionário com Fotos -->
                    <div class="selecao-funcionario-container">
                        <label class="label-titulo">Com quem você quer agendar?</label>
                        <div class="selecao-funcionario">
                            <?php 
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

                    <!-- Linha 2: Data e Serviço -->
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Data</label>
                            <input class="form-control" type="date" name="data" id="data" value="<?php echo $data_atual ?>" required />
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Serviço</label>
                            <select class="form-control sel2" id="servico" name="servico" style="width:100%;" required> 
                                <option value="">Selecione...</option>
                                <?php foreach ($servicos as $serv): ?>
                                    <option value="<?php echo $serv['id'] ?>">
                                        <?php echo htmlspecialchars($serv['nome']) . ' - R$ ' . number_format($serv['valor'], 2, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Horários -->
                     <div class="form-group">
                        <label>Horários Disponíveis</label>
                        <div id="listar-horarios">
                            <small class="text-muted">Selecione uma data e um profissional para ver os horários.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <input maxlength="100" type="text" class="form-control" name="obs" id="obs" placeholder="Observações (Opcional)">
                    </div>
                    
                    <button id="btn-agendar" class="botao-preto" type="submit" style="width:100%;" disabled>Confirmar Agendamento</button>
                    <button id="btn-editar" class="botao-azul" type="submit" style="width:100%; display:none;">Salvar Edição</button>
                    <button type="button" id="btn-excluir" class="btn btn-danger mt-2" style="width:100%; display:none;" data-toggle="modal" data-target="#modalExcluir">Excluir Agendamento</button>
                     
                    <!-- Modal/Botão de Notificação (Oculto Inicialmente) -->
                    <div id="area-notificacao" class="text-center mt-3" style="display:none;">
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> Agendamento Realizado!
                        </div>
                        <a id="link-whatsapp" href="#" target="_blank" class="btn btn-success" style="width: 100%; font-weight: bold;">
                            <i class="fa fa-whatsapp"></i> Avisar Cabeleireira
                        </a>
                        <small class="text-muted mt-2 d-block">Clique acima para enviar o comprovante via WhatsApp.</small>
                    </div>

                    <br>
                    <small><div id="mensagem" align="center"></div></small>
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="hora_rec" name="hora_rec">
                </div>
            </form>
            <div id="listar-cartoes" style="margin-top: -30px"></div>
        <?php } ?>
        </div>
    </div>
</div>

<?php require_once("rodape.php") ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.sel2').select2({ width: '100%' });
        $('#telefone').mask('(00) 00000-0000');
        
        // $('#telefone').on('blur', buscarClientePorTelefone); // Desativado pois agora puxa da sessão
        $('input[name="funcionario"]').on('change', listarHorarios); 
        $('#data').on('change', listarHorarios);
        $('#form-agenda').on('submit', salvarAgendamento);
        $('#form-excluir').on('submit', excluirAgendamento);

        // Auto-select professional from URL
        const urlParams = new URLSearchParams(window.location.search);
        const funcId = urlParams.get('func');
        if (funcId) {
            const radio = $(`input[name="funcionario"][value="${funcId}"]`);
            if (radio.length) {
                radio.prop('checked', true);
                listarHorarios();
                
                // Scroll to the form area smoothly
                $('html, body').animate({
                    scrollTop: $(".scheduling_form_content").offset().top - 100
                }, 1000);
            }
        }
    });

    // --- FUNÇÕES AJAX ---

    function buscarClientePorTelefone() {
        var tel = $('#telefone').val();
        if (tel.length < 14) { resetarFormulario(false); return; } // Validação básica de tamanho
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
                        $('#funcionario').val(response.agendamento.funcionario_id).trigger('change'); // Radio buttons precisam ser verificado manualmente se value não funcionar
                        $("input[name=funcionario][value=" + response.agendamento.funcionario_id + "]").prop('checked', true);
                        
                        $('#servico').val(response.agendamento.servico_id).trigger('change');
                        $('#obs').val(response.agendamento.obs);
                        $('#hora_rec').val(response.agendamento.hora);
                        $('#id_excluir').val(response.agendamento.id);
                        
                        $('#btn-agendar').hide();
                        $('#btn-editar, #btn-excluir').show();
                        $('#mensagem').text('Você já tem um agendamento.').addClass('text-info');
                        
                        listarHorarios(); // Atualiza horários para mostrar o atual selecionado
                    } else {
                        // Cliente existe mas sem agendamento futuro
                         $('#btn-agendar').show();
                         $('#btn-editar, #btn-excluir').hide();
                    }
                }
            }
        });
    }

    function listarHorarios() {
        var funcionario = $('input[name="funcionario"]:checked').val();
         var data = $('#data').val();
        var hora = $('#hora_rec').val();
        
        if (!funcionario || !data) return;

        $('#listar-horarios').html('<div class="text-center p-2"><i class="fa fa-spinner fa-spin"></i> Carregando...</div>');
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
            }
        });
    }

    function salvarAgendamento(event) {
        if(event) event.preventDefault();
        var submitButton = $('#id').val() ? $('#btn-editar') : $('#btn-agendar');
        var originalText = submitButton.text();
        submitButton.prop('disabled', true).text('Processando...');

        $.ajax({
            url: "ajax/agendar.php",
            type: 'POST', data: new FormData(document.getElementById('form-agenda')), dataType: 'json', 
            success: function(response) { // Agora espera JSON
                $('#mensagem').removeClass().text('');
                
                if (response.status === 'success') {
                    // Oculta botões e mostra área de notificação
                    $('#btn-agendar, #btn-editar, #btn-excluir').hide();
                    $('#area-notificacao').fadeIn();
                    $('#link-whatsapp').attr('href', response.whatsapp_link);
                    
                    // Reset parcial ou total após tempo se desejar, mas aqui deixa o usuário ver
                } else {
                    // Fallback para caso retorno venha texto puro de erro (ex: exceptions não tratadas como json)
                     if(response.responseText) {
                        $('#mensagem').addClass('text-danger').text(response.responseText);
                     } else {
                         // Se JSON vier com erro mas status != success (improvável com o código atual mas bom prevenir)
                          $('#mensagem').addClass('text-danger').text(response.message || 'Erro desconhecido');
                     }
                }
            },
            error: function(xhr) {
                 // Captura o texto de erro se o PHP der echo simples em caso de erro
                 $('#mensagem').addClass('text-danger').text(xhr.responseText);
            },
            cache: false, contentType: false, processData: false,
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
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
                    resetarFormulario();
                    $('#mensagem').addClass('text-success').text(response);
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
        $('input[name="funcionario"]').prop('checked', false);
        $('#servico').val('').trigger('change');
        $('#listar-horarios').html('<small class="text-muted">Selecione uma data e um profissional.</small>');
        $('#btn-agendar').show();
        $('#btn-editar, #btn-excluir, #area-notificacao').hide();
        $('#mensagem').text('');
    }
</script>