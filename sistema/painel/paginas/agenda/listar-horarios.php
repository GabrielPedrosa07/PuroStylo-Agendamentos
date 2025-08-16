<?php
require_once("../../../conexao.php");
@session_start();

$response = []; // Array para a resposta JSON

// --- VALIDAÇÃO DA ENTRADA ---
$id_funcionario = $_SESSION['id'] ?? 0;
$data = $_POST['data'] ?? '';

if (empty($data)) {
    $data = date('Y-m-d');
}
if (empty($id_funcionario)) {
    $response['status'] = 'error';
    $response['message'] = '<div class="text-center p-3"><small>Funcionário não autenticado!</small></div>';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// --- 1. VERIFICA SE O FUNCIONÁRIO TRABALHA NO DIA DA SEMANA ---
$diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado"];
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

$query_dia = $pdo->prepare("SELECT id FROM dias WHERE funcionario = :funcionario AND dia = :dia");
$query_dia->bindValue(':funcionario', $id_funcionario);
$query_dia->bindValue(':dia', $dia_procurado);
$query_dia->execute();
if ($query_dia->rowCount() == 0) {
    $response['status'] = 'error';
    $response['message'] = '<div class="text-center p-3">Este Funcionário não trabalha neste Dia!</div>';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// --- 2. BUSCA TODOS OS HORÁRIOS E A DISPONIBILIDADE ---
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

// --- 3. MONTA O HTML E A RESPOSTA JSON ---
if (count($horarios) > 0) {
    $html_content = '<div class="row">';
    $horarios_disponiveis = false;

    foreach ($horarios as $horario) {
        $hora = $horario['horario'];
        $horaF = (new DateTime($hora))->format('H:i');
        $esta_agendado = ($horario['agendamento_id'] !== null);
        $hora_desabilitada = $esta_agendado ? 'disabled' : '';
        $texto_hora = $esta_agendado ? 'text-danger' : '';

        if (!$esta_agendado) {
            $horarios_disponiveis = true;
        }

        $html_content .= '
            <div class="col-md-2 col-4 mb-2">
                <div class="form-check">
                    <input class.form-check-input" type="radio" name="hora" id="hora-'.$horaF.'" value="'.htmlspecialchars($hora).'" '.$hora_desabilitada.'>
                    <label class="form-check-label '.$texto_hora.'" for="hora-'.$horaF.'">'.$horaF.'</label>
                </div>
            </div>';
    }
    $html_content .= '</div>';

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