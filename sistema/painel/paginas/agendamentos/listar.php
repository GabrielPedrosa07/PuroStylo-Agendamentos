<?php
require_once("../../../conexao.php");
@session_start();

// --- VALIDAÇÃO DA ENTRADA ---
$id_usuario_logado = $_SESSION['id'] ?? 0; // Garante que a variável de sessão existe
$id_funcionario = $_POST['funcionario'] ?? '';
$data_agenda = $_POST['data'] ?? date('Y-m-d');

if (empty($id_funcionario)) {
    echo '<div class="text-center p-3">Por favor, selecione um funcionário.</div>';
    exit();
}

// --- CONSULTA ÚNICA, SEGURA E OTIMIZADA COM LEFT JOIN ---
// Buscamos todos os dados (agendamento, cliente, serviço, usuário) em uma única consulta.
$query_sql = "
    SELECT 
        ag.id, ag.hora, ag.obs, ag.status,
        ag.cliente as cliente_id, ag.servico as servico_id, ag.funcionario as funcionario_id,
        cli.nome AS nome_cliente, cli.cartoes AS total_cartoes,
        serv.nome AS nome_servico, serv.valor AS valor_servico,
        usu.nome AS nome_usuario
    FROM 
        agendamentos ag
    LEFT JOIN 
        clientes cli ON ag.cliente = cli.id
    LEFT JOIN 
        servicos serv ON ag.servico = serv.id
    LEFT JOIN 
        usuarios usu ON ag.usuario = usu.id
    WHERE 
        ag.funcionario = :funcionario AND ag.data = :data 
    ORDER BY 
        ag.hora ASC
";

$query = $pdo->prepare($query_sql);
$query->bindValue(':funcionario', $id_funcionario);
$query->bindValue(':data', $data_agenda);
$query->execute();
$agendamentos = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($agendamentos) > 0) {
    // Loop através dos resultados da consulta única
    foreach ($agendamentos as $agendamento) {
        $id_agendamento = $agendamento['id'];
        $horaF = (new DateTime($agendamento['hora']))->format('H:i');
        
        // Lógica de status
        $classe_linha = ($agendamento['status'] == 'Concluído') ? '' : 'text-muted';
        $imagem = ($agendamento['status'] == 'Agendado') ? 'icone-relogio.png' : 'icone-relogio-verde.png';
        $classe_status = ($agendamento['status'] == 'Agendado') ? '' : 'ocultar';

        // Tratamento de dados nulos (caso um cliente ou serviço seja excluído)
        $nome_cliente = $agendamento['nome_cliente'] ?? 'Cliente não encontrado';
        $nome_servico = $agendamento['nome_servico'] ?? 'Serviço não encontrado';
        $valor_servico = $agendamento['valor_servico'] ?? 0;
        $total_cartoes = $agendamento['total_cartoes'] ?? 0;

        // Lógica dos cartões fidelidade
        $ocultar_cartoes = ($total_cartoes >= $quantidade_cartoes && $agendamento['status'] == 'Agendado') ? '' : 'ocultar';

        // Prepara os dados para serem usados no HTML e JavaScript (prevenindo XSS)
        $id_cliente_js = htmlspecialchars($agendamento['cliente_id'], ENT_QUOTES);
        $id_servico_js = htmlspecialchars($agendamento['servico_id'], ENT_QUOTES);
        $valor_servico_js = htmlspecialchars($valor_servico, ENT_QUOTES);
        $id_funcionario_js = htmlspecialchars($agendamento['funcionario_id'], ENT_QUOTES);
        $nome_servico_js = htmlspecialchars($nome_servico, ENT_QUOTES);
        
        // Usamos htmlspecialchars para exibir dados no HTML de forma segura
        $nome_cliente_html = htmlspecialchars($nome_cliente);
        $nome_servico_html = htmlspecialchars($nome_servico);

        echo <<<HTML
            <div class="col-xs-12 col-md-4 widget cardTarefas">
                <div class="r3_counter_box">
                    <li class="dropdown head-dpdn2" style="list-style-type: none;">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <button type="button" class="close" title="Excluir agendamento" style="margin-top: -10px">
                                <span aria-hidden="true"><big>&times;</big></span>
                            </button>
                        </a>
                        <ul class="dropdown-menu" style="margin-left:-30px;">
                            <li>
                                <div class="notification_desc2">
                                    <p>Confirmar Exclusão? <a href="#" onclick="excluir('{$id_agendamento}', '{$horaF}')"><span class="text-danger">Sim</span></a></p>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <div class="row">
                        <div class="col-md-3">
                            <img class="icon-rounded-vermelho" src="img/{$imagem}" width="45px" height="45px">
                        </div>
                        <div class="col-md-9">
                            <h5><strong>{$horaF}</strong> 
                                <a href="#" onclick="fecharServico('{$id_agendamento}', '{$id_cliente_js}', '{$id_servico_js}', '{$valor_servico_js}', '{$id_funcionario_js}', '{$nome_servico_js}')" title="Finalizar Serviço" class="{$classe_status}"> 
                                    <img class="icon-rounded-vermelho" src="img/check-square.png" width="15px" height="15px">
                                </a>
                            </h5>
                        </div>
                    </div>
                    <hr style="margin-top:-2px; margin-bottom: 3px">
                    <div class="stats esc" align="center">
                        <span>
                            <small>
                                <span class="{$ocultar_cartoes}">
                                    <img class="icon-rounded-vermelho" src="img/presente.jpg" width="20px" height="20px">
                                </span> 
                                {$nome_cliente_html} (<i><span style="color:#061f9c">{$nome_servico_html}</span></i>)
                            </small>
                        </span>
                    </div>
                </div>
            </div>
HTML;
    }
} else {
    echo '<div class="text-center p-3">Nenhum horário agendado para esta data.</div>';
}
?>

<script type="text/javascript">
    function fecharServico(id, cliente, servico, valor_servico, funcionario, nome_serv){
        $('#id_agd').val(id);
        $('#cliente_agd').val(cliente);
        $('#servico_agd').val(servico);
        $('#valor_serv_agd').val(valor_servico);
        $('#funcionario_agd').val(funcionario).change();
        $('#titulo_servico').text(nome_serv);
        $('#descricao_serv_agd').val(nome_serv);
        $('#modalServico').modal('show');
    }
</script>