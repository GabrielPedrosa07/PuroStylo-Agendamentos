<?php
require_once("../../../conexao.php");

// Array que será convertido para JSON
$response = [];

// --- VALIDAÇÃO DA ENTRADA ---
$id_funcionario = $_POST['funcionario'] ?? '';
$data = $_POST['data'] ?? '';

if (empty($data)) {
    $data = date('Y-m-d');
}

if (empty($id_funcionario)) {
    $response['status'] = 'error';
    $response['message'] = '<div class="text-center p-3"><small>Selecione um Funcionário!</small></div>';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// --- 1. VERIFICA SE O FUNCIONÁRIO TRABALHA NO DIA DA SEMANA (DE FORMA SEGURA) ---
$diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado"];
$diasemana_numero = date('w', strtotime($data));
$dia_procurado = $diasemana[$diasemana_numero];

$query_dia = $pdo->prepare("SELECT id FROM dias WHERE funcionario = :funcionario AND dia = :dia");
$query_dia->bindValue(':funcionario', $id_funcionario);
$query_dia->bindValue(':dia', $dia_procurado);
$query_dia->execute();
if ($query_dia->rowCount() == 0) {
    // Se não trabalha, preparamos uma resposta de erro
    $response['status'] = 'error';
    $response['message'] = '<div class="text-center p-3">Este Funcionário não trabalha neste Dia!</div>';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// --- 2. BUSCA TODOS OS HORÁRIOS E VERIFICA A DISPONIBILIDADE EM UMA ÚNICA CONSULTA ---
$query_horarios = $pdo->prepare("
    SELECT 
        h.horario, 
        a.id as agendamento_id
    FROM 
        horarios h
    LEFT JOIN 
        agendamentos a ON h.horario = a.hora AND a.data = :data AND a.funcionario = :funcionario_ag
    WHERE 
        h.funcionario = :funcionario_hr
    ORDER BY 
        h.horario ASC
");
$query_horarios->bindValue(':data', $data);
$query_horarios->bindValue(':funcionario_ag', $id_funcionario);
$query_horarios->bindValue(':funcionario_hr', $id_funcionario);
$query_horarios->execute();
$horarios = $query_horarios->fetchAll(PDO::FETCH_ASSOC);

// --- 3. MONTA O HTML E A RESPOSTA JSON ---
if (count($horarios) > 0) {
    $html_content = '<div class="row">';
    $horarios_disponiveis = false; // Flag para verificar se há pelo menos um horário vago

    foreach ($horarios as $horario) {
        $hora = $horario['horario'];
        $horaF = (new DateTime($hora))->format('H:i');
        $esta_agendado = ($horario['agendamento_id'] !== null);
        $hora_desabilitada = $esta_agendado ? 'disabled' : '';
        $texto_hora = $esta_agendado ? 'text-danger' : '';

        if (!$esta_agendado) {
            $horarios_disponiveis = true; // Encontramos pelo menos um horário vago
        }

        $html_content .= '
            <div class="col-md-2 col-4 mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="hora" id="hora-'.$horaF.'" value="'.htmlspecialchars($hora).'" '.$hora_desabilitada.'>
                    <label class="form-check-label '.$texto_hora.'" for="hora-'.$horaF.'">
                        '.$horaF.'
                    </label>
                </div>
            </div>';
    }
    $html_content .= '</div>';

    // Se houver horários, mas NENHUM estiver disponível
    if (!$horarios_disponiveis) {
        $response['status'] = 'error'; // Tratamos como erro para desabilitar o botão
        $html_content .= '<div class="text-center text-danger p-3 small">Não há horários disponíveis para este profissional nesta data.</div>';
        $response['message'] = $html_content;
    } else {
        $response['status'] = 'success'; // Sucesso, pois há horários
        $response['html'] = $html_content;
    }

} else {
    // Caso não tenha horários de trabalho cadastrados
    $response['status'] = 'error';
    $response['message'] = '<div class="col-12 text-center p-3">Nenhum horário de trabalho cadastrado para este funcionário.</div>';
}

// Define o cabeçalho como JSON e envia a resposta final
header('Content-Type: application/json');
echo json_encode($response);
?>