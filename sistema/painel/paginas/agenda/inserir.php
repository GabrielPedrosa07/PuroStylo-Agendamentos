<?php
require_once("../../../conexao.php");
@session_start();
$usuario_logado = $_SESSION['id'] ?? 0;

// --- Recebendo e validando os dados do formulário ---
// O funcionário é o próprio usuário logado nesta versão
$funcionario = $_SESSION['id'] ?? 0; 
$cliente = $_POST['cliente'];
$data = $_POST['data'];
$hora = $_POST['hora'] ?? '';
$obs = $_POST['obs'];
$id = $_POST['id']; // ID do agendamento, se for uma edição
$servico = $_POST['servico'];

// Validações iniciais
if (empty($funcionario)) {
    echo 'Erro: Você precisa estar logado como funcionário para agendar.';
    exit();
}
if (empty($hora)) {
    echo 'Selecione um Horário antes de agendar!'; // Corrigido erro de digitação
    exit();
}

try {
    // --- 1. VERIFICA SE O FUNCIONÁRIO TRABALHA NO DIA (DE FORMA SEGURA) ---
    $diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sabado"];
    $diasemana_numero = date('w', strtotime($data));
    $dia_procurado = $diasemana[$diasemana_numero];

    $query_dia = $pdo->prepare("SELECT id FROM dias WHERE funcionario = :funcionario AND dia = :dia");
    $query_dia->bindValue(':funcionario', $funcionario);
$query_dia->bindValue(':dia', $dia_procurado);    $query_dia->execute();
    if ($query_dia->rowCount() == 0) {
        echo 'Este Funcionário não trabalha neste Dia!';
        exit();
    }

    // --- 2. VERIFICA SE O HORÁRIO JÁ ESTÁ OCUPADO (DE FORMA SEGURA) ---
    // A consulta muda um pouco se estivermos editando (precisamos ignorar o próprio agendamento)
    $sql_disponibilidade = "SELECT id FROM agendamentos WHERE data = :data AND hora = :hora AND funcionario = :funcionario";
    $params_disponibilidade = [
        ':data' => $data,
        ':hora' => $hora,
        ':funcionario' => $funcionario
    ];

    if (!empty($id)) {
        // Se é uma edição, exclui o próprio ID da verificação de conflito
        $sql_disponibilidade .= " AND id != :id";
        $params_disponibilidade[':id'] = $id;
    }

    $query_disp = $pdo->prepare($sql_disponibilidade);
    $query_disp->execute($params_disponibilidade);
    if ($query_disp->rowCount() > 0) {
        echo 'Este horário não está disponível!';
        exit();
    }

    // --- 3. INSERE OU ATUALIZA O AGENDAMENTO (DE FORMA SEGURA) ---
    if (empty($id)) {
        // --- CADASTRAR NOVO AGENDAMENTO ---
        $query = $pdo->prepare("INSERT INTO agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, usuario = :usu, status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = :serv");
    } else {
        // --- EDITAR AGENDAMENTO EXISTENTE ---
        $query = $pdo->prepare("UPDATE agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, usuario = :usu, status = 'Agendado', obs = :obs, servico = :serv WHERE id = :id");
        $query->bindValue(':id', $id);
    }
    
    // Parâmetros que são comuns tanto para INSERT quanto para UPDATE
    $query->bindValue(':func', $funcionario);
    $query->bindValue(':cli', $cliente);
    $query->bindValue(':hora', $hora);
    $query->bindValue(':data', $data);
    $query->bindValue(':usu', $usuario_logado);
    $query->bindValue(':obs', $obs);
    $query->bindValue(':serv', $servico);
    $query->execute();

    echo 'Salvo com Sucesso';

} catch (PDOException $e) {
    echo 'Falha ao salvar. Erro: ' . $e->getMessage();
}
?>