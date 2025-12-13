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
        
        // --- LÓGICA DE VISUALIZAÇÃO (ATUALIZADA) ---
        
        $acoes_html = '';
        $badge_status_html = '';
        $classe_borda_status = '';
        $status = $agendamento['status'];

        // Normalizar status antigos ou diferentes
        if ($status == 'Agendado' || $status == 'Confirmado') {
             $classe_borda_status = 'status-agendado'; // Azul
             $status_texto = 'Confirmado';
             $badge_class = 'badge-primary';
             
             // Botões para Confirmado
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

        } else if ($status == 'Aguardando Aprovação') {
             $classe_borda_status = 'status-aguardando'; // Amarelo (precisa criar CSS)
             $status_texto = 'Aguardando Aprovação';
             $badge_class = 'badge-warning text-dark';

             // Dados para o WhatsApp
             $tel_cliente = preg_replace('/[^0-9]/', '', $agendamento['tel_cliente']);
             $msg_whats = "Olá {$nome_cliente_html}, seu agendamento para *{$nome_servico_html}* no dia *" . date('d/m/Y', strtotime($data_agenda)) . "* às *{$horaF}* foi confirmado!";
             $link_whats = "http://api.whatsapp.com/send?phone=55{$tel_cliente}&text=" . urlencode($msg_whats);
             
             // Escape para uso seguro no JS (evita que aspas quebrem o HTML)
             $link_whats_escaped = htmlspecialchars($link_whats, ENT_QUOTES, 'UTF-8');

             // Botões para Aguardando
             $acoes_html = <<<HTML
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v fa-lg"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li>
                        <a class="dropdown-item" href="#" onclick="confirmarAgendamento({$agendamento['id']}, '{$link_whats_escaped}')">
                            <i class="fa fa-thumbs-up text-primary"></i> Confirmar Agendamento
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
            // CONCLUÍDO / FINALIZADO
            $classe_borda_status = 'status-concluido'; // Verde
            $status_texto = 'Finalizado';
             $badge_class = 'badge-success';
            
            // Botões para Finalizado
            $acoes_html = <<<HTML
                <a class="acao-unica" href="#" onclick="excluir({$agendamento['id']}, '{$horaF}')" title="Excluir Agendamento">
                    <i class="fa fa-trash-o text-danger fa-lg"></i>
                </a>
            HTML;
        }

        $badge_status_html = "<span class='badge {$badge_class} ml-2'>{$status_texto}</span>";

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