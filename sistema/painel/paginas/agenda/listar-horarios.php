<?php
// Arquivo: paginas/agendamentos/listar-horarios.php (ou /agenda/listar-horarios.php)
require_once("../../../conexao.php"); 
@session_start();

$response = []; // Array para a resposta JSON

// --- VALIDAÇÃO DA ENTRADA ---
$id_funcionario = $_POST['funcionario'] ?? $_SESSION['id'];
$data = $_POST['data'] ?? date('Y-m-d');
$hora_rec = $_POST['hora'] ?? '';

if (empty($id_funcionario)) {
    // Retorna erro se nenhum funcionário for identificado
    $response['status'] = 'error';
    $response['message'] = '<div class="text-center p-3"><small>Selecione um Funcionário!</small></div>';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

try {
    // --- VERIFICA SE O FUNCIONÁRIO TRABALHA NO DIA (DE FORMA SEGURA) ---
    $diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado"];
    $diasemana_numero = date('w', strtotime($data));
    $dia_procurado = $diasemana[$diasemana_numero];

    $query_dia = $pdo->prepare("SELECT id FROM dias WHERE funcionario = :funcionario AND dia = :dia");
    $query_dia->execute([':funcionario' => $id_funcionario, ':dia' => $dia_procurado]);
    if ($query_dia->rowCount() == 0) {
        $response['status'] = 'error';
        $response['message'] = '<div class="text-center p-3">Este Funcionário não trabalha neste Dia!</div>';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // --- BUSCA TODOS OS HORÁRIOS E A DISPONIBILIDADE EM UMA ÚNICA CONSULTA ---
    $query_horarios = $pdo->prepare("
        SELECT h.horario, a.id as agendamento_id FROM horarios h
        LEFT JOIN agendamentos a ON h.horario = a.hora AND a.data = :data AND a.funcionario = :funcionario_ag
        WHERE h.funcionario = :funcionario_hr ORDER BY h.horario ASC
    ");
    $query_horarios->execute([
        ':data' => $data,
        ':funcionario_ag' => $id_funcionario,
        ':funcionario_hr' => $id_funcionario
    ]);
    $horarios = $query_horarios->fetchAll(PDO::FETCH_ASSOC);

    // --- MONTA O HTML E A RESPOSTA JSON ---
    if (count($horarios) > 0) {
        $html_content = '';
        $horarios_disponiveis = false;

        foreach ($horarios as $horario) {
            $hora_atual_loop = $horario['horario'];
            $horaF = (new DateTime($hora_atual_loop))->format('H:i');
            
            $esta_agendado = ($horario['agendamento_id'] !== null);
            $e_o_horario_salvo = (!empty($hora_rec) && strtotime($hora_rec) == strtotime($hora_atual_loop));
            $pode_selecionar = !$esta_agendado || $e_o_horario_salvo;

            $hora_desabilitada = $pode_selecionar ? '' : 'disabled';
            $texto_hora = $esta_agendado ? 'text-danger' : '';
            $checado = $e_o_horario_salvo ? 'checked' : '';

            if ($pode_selecionar) { $horarios_disponiveis = true; }

            // ===== ESTRUTURA HTML CORRETA =====
            $html_content .= '
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="hora" id="hora-'.$horaF.'" value="'.htmlspecialchars($hora_atual_loop).'" '.$hora_desabilitada.' '.$checado.' required>
                    <label class="form-check-label '.$texto_hora.'" for="hora-'.$horaF.'">'.$horaF.'</label>
                </div>';
        }
        
        if (!$horarios_disponiveis) {
            $response['status'] = 'error';
            $response['message'] = '<div class="text-center text-danger p-3 small">Não há horários disponíveis para esta data.</div>' . $html_content;
        } else {
            $response['status'] = 'success';
            $response['html'] = $html_content;
        }

    } else {
        $response['status'] = 'error';
        $response['message'] = '<div class="col-12 text-center p-3">Nenhum horário de trabalho cadastrado.</div>';
    }

} catch (PDOException $e) {
    $response['status'] = 'error';
    $response['message'] = 'Erro no Banco de Dados: ' . $e->getMessage();
}


header('Content-Type: application/json');
echo json_encode($response);
?>