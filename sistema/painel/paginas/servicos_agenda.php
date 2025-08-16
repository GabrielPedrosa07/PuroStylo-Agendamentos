<?php 
require_once("verificar.php");
require_once("../conexao.php");
$pag = 'servicos_agenda';

//verificar se ele tem a permissão de estar nessa página
if(@$servicos_agenda == 'ocultar'){
    echo "<script>window.location='../index.php'</script>";
    exit();
}

$data_hoje = date('Y-m-d');
$data_ontem = date('Y-m-d', strtotime("-1 days"));
$mes_atual = date('Y-m');
$data_inicio_mes = $mes_atual . '-01';
$data_final_mes = date('Y-m-t', strtotime($data_hoje)); // Forma mais segura de pegar o último dia do mês

// --- BUSCA DE DADOS INICIAIS (FEITA DE FORMA SEGURA E ORGANIZADA) ---
$query_func = $pdo->query("SELECT id, nome FROM usuarios WHERE atendimento = 'Sim' ORDER BY nome ASC");
$funcionarios = $query_func->fetchAll(PDO::FETCH_ASSOC);

$query_cli = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC");
$clientes = $query_cli->fetchAll(PDO::FETCH_ASSOC);

$query_serv = $pdo->query("SELECT id, nome FROM servicos ORDER BY nome ASC");
$servicos = $query_serv->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-3"> <a class="btn btn-primary" onclick="inserir()" href="#"><i class="fa fa-plus" aria-hidden="true"></i> Novo Serviço</a>
</div>

<div class="bs-example widget-shadow" style="padding:15px">

    <div class="row">
        <div class="col-md-5 mb-2">
            <div class="d-flex align-items-center"> <span class="me-2"><small><i title="Data Inicial" class="fa fa-calendar-o"></i></small></span>
                <input type="date" class="form-control me-3" name="data-inicial" id="data-inicial-caixa" value="<?php echo $data_hoje ?>">
                
                <span class="me-2"><small><i title="Data Final" class="fa fa-calendar-o"></i></small></span>
                <input type="date" class="form-control" name="data-final" id="data-final-caixa" value="<?php echo $data_hoje ?>">
            </div>
        </div>
        
        <div class="col-md-3 mb-2 d-flex align-items-center justify-content-center"> 
            <div> 
                <small>
                    <a title="Filtrar por Ontem" class="text-muted" href="#" onclick="valorData('<?php echo $data_ontem ?>', '<?php echo $data_ontem ?>')">Ontem</a> / 
                    <a title="Filtrar por Hoje" class="text-muted" href="#" onclick="valorData('<?php echo $data_hoje ?>', '<?php echo $data_hoje ?>')">Hoje</a> / 
                    <a title="Filtrar por Mês Atual" class="text-muted" href="#" onclick="valorData('<?php echo $data_inicio_mes ?>', '<?php echo $data_final_mes ?>')">Mês</a>
                </small>
            </div>
        </div>

        <div class="col-md-4 mb-2 d-flex align-items-center justify-content-center"> 
            <div> 
                <small>
                    <a title="Mostrar Todos" class="text-muted" href="#" onclick="buscarContas('')">Todos</a> / 
                    <a title="Mostrar Pendentes" class="text-muted" href="#" onclick="buscarContas('Não')">Pendentes</a> / 
                    <a title="Mostrar Pagos" class="text-muted" href="#" onclick="buscarContas('Sim')">Pagos</a>
                </small>
            </div>
        </div>
        
        <input type="hidden" id="buscar-contas">
    </div>

    <hr>
    <div id="listar">
        </div>
</div>


<div class="modal fade" id="modalForm" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span id="titulo_inserir"></span></h4>
                <button id="btn-fechar" type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-top: -20px">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Funcionário</label> 
                            <select class="form-control sel2" name="funcionario" style="width:100%;" required>
                                <?php foreach ($funcionarios as $func): ?>
                                    <option value="<?php echo $func['id'] ?>"><?php echo htmlspecialchars($func['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Cliente</label> 
                            <select class="form-control sel2" name="cliente" style="width:100%;" required>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?php echo $cli['id'] ?>"><?php echo htmlspecialchars($cli['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-5 form-group">
                             <label>Serviço</label> 
                             <select class="form-control sel2" name="servico" style="width:100%;" required>
                                <?php foreach ($servicos as $serv): ?>
                                    <option value="<?php echo $serv['id'] ?>"><?php echo htmlspecialchars($serv['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Valor</label> 
                            <input type="text" class="form-control" name="valor_serv" id="valor_serv" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Data Pagamento</label> 
                            <input type="date" class="form-control" name="data_pgto" id="data_pgto" value="<?php echo $data_hoje ?>">
                        </div>
                    </div>
                    
                    <input type="hidden" name="id" id="id">
                    <br>
                    <small><div id="mensagem" align="center"></div></small>
                </div>
                <div class="modal-footer">     
                    <button type="submit" class="btn btn-primary" id="btn-salvar">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">var pag = "<?=$pag?>"</script>

<script src="js/ajax.js"></script>


<script type="text/javascript">
    $(document).ready(function() {
        // Inicializa a listagem com a data de hoje
        listar();

        // Inicializa o plugin Select2 nos modais
        $('.sel2').select2({
            dropdownParent: $('#modalForm')
        });

        // Adiciona gatilhos para os filtros de data
        $('#data-inicial-caixa, #data-final-caixa').on('change', function(){
            listar();
        });
    });

    // Função para preencher as datas e chamar a listagem
    function valorData(dataInicio, dataFinal){
        $('#data-inicial-caixa').val(dataInicio);
        $('#data-final-caixa').val(dataFinal); 
        listar();
    }

    // Função para filtrar por status (pago/pendente/todos)
    function buscarContas(status){
        $('#buscar-contas').val(status);
        listar();
    }

    // Função principal para listar os dados via AJAX
    function listar(){
        // Mostra um loader enquanto carrega
        $('#listar').html('Carregando...');

        $.ajax({
            url: 'paginas/' + pag + "/listar.php",
            method: 'POST',
            data: {
                dataInicial: $('#data-inicial-caixa').val(),
                dataFinal: $('#data-final-caixa').val(),
                status: $('#buscar-contas').val()
            },
            dataType: "html",
            success:function(result){
                $("#listar").html(result);
            },
            error: function(){
                $("#listar").html('<div class="text-danger text-center">Erro ao carregar dados.</div>');
            }
        });
    }

    // Função para tratar o envio do formulário do modal
    // Esta função provavelmente está no seu arquivo js/ajax.js,
    // se não estiver, você pode descomentar e usar esta versão melhorada.
    /*
    $('#form').submit(function(event){
        event.preventDefault();
        $('#btn-salvar').prop('disabled', true).text('Salvando...');

        var formData = new FormData(this);

        $.ajax({
            url: 'paginas/' + pag + "/salvar.php",
            method: 'POST',
            data: formData,
            success: function(response){
                if(response.trim() === 'Salvo com Sucesso'){
                    $('#btn-fechar').click();
                    listar();
                } else {
                    $('#mensagem').addClass('text-danger').text(response);
                }
            },
            cache: false,
            contentType: false,
            processData: false,
            complete: function(){
                 $('#btn-salvar').prop('disabled', false).text('Salvar');
            }
        });
    });
    */
</script>