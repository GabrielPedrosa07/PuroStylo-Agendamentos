<?php
// Arquivo: paginas/agendamentos/listar.php
require_once("../../../conexao.php");
@session_start();

$id_funcionario = $_POST['funcionario'] ?? $_SESSION['id'];
$data_agenda = $_POST['data'];

// --- CONSULTA ÚNICA E SEGURA ---
$query_sql = "
    SELECT 
        ag.id, ag.hora, ag.status, ag.cliente as cliente_id, ag.servico as servico_id,
        cli.nome AS nome_cliente, cli.telefone AS tel_cliente,
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
        $tel_cliente_html = htmlspecialchars($agendamento['tel_cliente'] ?? 'Não informado');
        
        // Prepara dados para os botões de forma segura
        $dados_finalizar_json = htmlspecialchars(json_encode([
            'id' => $agendamento['id'], 'cliente' => $agendamento['cliente_id'],
            'servico' => $agendamento['servico_id'], 'valor' => $agendamento['valor_servico'] ?? 0,
            'funcionario' => $id_funcionario, 'nome_serv' => $agendamento['nome_servico'] ?? 'N/A'
        ]), ENT_QUOTES, 'UTF-8');

        // --- LÓGICA DE VISUALIZAÇÃO (AQUI ESTÃO AS MUDANÇAS) ---
        
        $acoes_html = '';
        $badge_status_html = '';
        $classe_borda_status = '';

        if ($agendamento['status'] == 'Agendado') {
            // SE O STATUS FOR 'AGENDADO'
            $classe_borda_status = 'status-agendado';
            
            // NOVO: Badge Azul "Agendado"
            $badge_status_html = '<span class="badge badge-primary ml-2">Agendado</span>';

            // Menu completo com as duas opções: Finalizar e Excluir
            $acoes_html = <<<HTML
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v fa-lg"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a class="dropdown-item" href="#" onclick='fecharServico({$dados_finalizar_json})'>
                            <i class="fa fa-check text-success"></i> Finalizar Serviço
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="excluir({$agendamento['id']}, '{$horaF}')">
                            <i class="fa fa-trash-o text-danger"></i> Excluir
                        </a>
                    </li>
                </ul>
            HTML;

        } else {
            // SE O STATUS FOR 'CONCLUÍDO' (OU QUALQUER OUTRO)
            $classe_borda_status = 'status-concluido';
            
            // NOVO: Badge Verde "Finalizado"
            $badge_status_html = '<span class="badge badge-success ml-2">Finalizado</span>';

            // Mostra APENAS o botão de excluir, sem o menu dropdown
            $acoes_html = <<<HTML
                <a class="acao-unica" href="#" onclick="excluir({$agendamento['id']}, '{$horaF}')" title="Excluir Agendamento">
                    <i class="fa fa-trash-o text-danger fa-lg"></i>
                </a>
            HTML;
        }

        // --- HTML FINAL DO CARD ---
        echo <<<HTML
            <div class="agenda-card {$classe_borda_status}">
                <div class="card-body">
                    <div class="info-principal">
                        <div class="hora">
                            {$horaF}
                        </div>
                        <div class="detalhes">
                            <span><strong><i class="fa fa-user"></i> {$nome_cliente_html}</strong></span>
                            <span><i class="fa fa-cut"></i> {$nome_servico_html}</span>
                            <span><i class="fa fa-phone"></i> {$tel_cliente_html}</span>
                        </div>
                        <div class="status-badge">
                           {$badge_status_html} </div>
                    </div>
                    <div class="menu-acoes dropdown">
                        {$acoes_html} </div>
                </div>
            </div>
        HTML;
    }
} else {
    echo '<div class="text-center p-5"><i class="fa fa-calendar-check-o fa-3x text-muted mb-2"></i><br>Nenhum agendamento para este dia.</div>';
}
?>