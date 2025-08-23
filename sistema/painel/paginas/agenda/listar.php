<?php
// Arquivo: paginas/agendamentos/listar.php (ou /agenda/listar.php)
require_once("../../../conexao.php");
@session_start();

$id_funcionario = $_POST['funcionario'] ?? $_SESSION['id'];
$data_agenda = $_POST['data'];

// --- CONSULTA ÚNICA E SEGURA ---
$query_sql = "
    SELECT 
        ag.id, ag.hora, ag.status, ag.cliente as cliente_id, ag.servico as servico_id,
        cli.nome AS nome_cliente,
        serv.nome AS nome_servico, serv.valor AS valor_servico
    FROM agendamentos ag
    LEFT JOIN clientes cli ON ag.cliente = cli.id
    LEFT JOIN servicos serv ON ag.servico = serv.id
    WHERE ag.funcionario = :funcionario AND ag.data = :data 
    ORDER BY ag.hora ASC
";
$query = $pdo->prepare($query_sql);
$query->execute([':funcionario' => $id_funcionario, ':data' => $data_agenda]);
$agendamentos = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($agendamentos) > 0) {
    foreach ($agendamentos as $agendamento) {
        $horaF = (new DateTime($agendamento['hora']))->format('H:i');
        $nome_cliente_html = htmlspecialchars($agendamento['nome_cliente'] ?? 'Cliente Removido');
        $nome_servico_html = htmlspecialchars($agendamento['nome_servico'] ?? 'Serviço Removido');
        
        // Define as classes e textos de status
        $classe_status_borda = ($agendamento['status'] == 'Agendado') ? 'status-agendado' : 'status-concluido';
        $cor_status_texto = ($agendamento['status'] == 'Agendado') ? 'primary' : 'success';
        $texto_status = $agendamento['status'];
        $ocultar_finalizar = ($agendamento['status'] != 'Agendado') ? 'd-none' : '';
        
        // Prepara dados para os botões de forma segura
        $dados_finalizar = htmlspecialchars(json_encode([
            'id' => $agendamento['id'], 'cliente' => $agendamento['cliente_id'],
            'servico' => $agendamento['servico_id'], 'valor' => $agendamento['valor_servico'] ?? 0,
            'funcionario' => $id_funcionario, 'nome_serv' => $agendamento['nome_servico'] ?? 'N/A'
        ]), ENT_QUOTES, 'UTF-8');

        echo <<<HTML
            <div class="card mb-2 appointment-card {$classe_status_borda}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="time-block">
                                <div class="time">{$horaF}</div>
                                <span class="badge badge-{$cor_status_texto}">{$texto_status}</span>
                            </div>
                            <div class="details-block">
                                <h6 class="mb-0"><i class="fa fa-user mr-1 text-muted"></i> {$nome_cliente_html}</h6>
                                <small><i class="fa fa-cut mr-1 text-muted"></i> {$nome_servico_html}</small>
                            </div>
                        </div>
                        <div class="action-menu dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v fa-lg"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a class="dropdown-item {$ocultar_finalizar}" href="#" onclick='fecharServico({$dados_finalizar})'>
                                        <i class="fa fa-check text-success"></i> Finalizar Serviço
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="excluir({$agendamento['id']}, '{$horaF}')">
                                        <i class="fa fa-trash-o text-danger"></i> Excluir
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
HTML;
    }
} else {
    echo '<div class="text-center p-5"><i class="fa fa-calendar-check-o fa-3x text-muted mb-2"></i><br>Nenhum agendamento para este dia.</div>';
}
?>