<?php
// Arquivo: ajax/listar-horarios.php
require_once("../sistema/conexao.php"); // Verifique o caminho da sua conexão
@session_start();

$response = []; // Array para a resposta JSON

// --- VALIDAÇÃO DA ENTRADA ---
$id_funcionario = $_POST['funcionario'] ?? '';
$data = $_POST['data'] ?? '';
$hora_rec = $_POST['hora'] ?? '';

// ... (Resto da lógica de verificação de dia de trabalho e a consulta com LEFT JOIN, que já estava correta)...

// --- 3. MONTA O HTML E A RESPOSTA JSON ---
if (count($horarios) > 0) {
    $html_content = ''; // Não precisa mais da <div class="row"> aqui
    $horarios_disponiveis = false;

    foreach ($horarios as $horario) {
        $hora_atual_loop = $horario['horario'];
        $horaF = (new DateTime($hora_atual_loop))->format('H:i');
        
        $esta_agendado = ($horario['agendamento_id'] !== null);
        $e_o_horario_salvo = (!empty($hora_rec) && strtotime($hora_rec) == strtotime($hora_atual_loop));

        $pode_selecionar = !$esta_agendado || $e_o_horario_salvo;

        $hora_desabilitada = $pode_selecionar ? '' : 'disabled';
        $texto_hora = $pode_selecionar ? '' : 'text-danger';
        $checado = $e_o_horario_salvo ? 'checked' : '';

        if ($pode_selecionar) {
            $horarios_disponiveis = true;
        }

        // ===== MUDANÇA PRINCIPAL AQUI =====
        // Removemos a div de coluna e usamos a nova classe 'horario-item'
        $html_content .= '
            <div class="horario-item">
                <input class="form-check-input" type="radio" name="hora" id="hora-'.$horaF.'" value="'.htmlspecialchars($hora_atual_loop).'" '.$hora_desabilitada.' '.$checado.' required>
                <label class="form-check-label '.$texto_hora.'" for="hora-'.$horaF.'">
                    '.$horaF.'
                </label>
            </div>';
    }
    
    // ... (Resto da lógica para montar a resposta JSON, que já estava correta) ...

    if (!$horarios_disponiveis) {
        $response['status'] = 'error';
        $html_content .= '<div class="text-center text-danger p-3 small">Não há horários disponíveis para esta data.</div>';
        $response['message'] = $html_content;
    } else {
        $response['status'] = 'success';
        $response['html'] = $html_content;
    }

} else {
    $response['status'] = 'error';
    $response['message'] = '<div class="col-12 text-center p-3">Nenhum horário de trabalho cadastrado.</div>';
}

header('Content-Type: application/json');
echo json_encode($response);
?>