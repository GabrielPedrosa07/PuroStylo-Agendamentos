<?php
require_once("../sistema/conexao.php");

// --- Recebendo e validando os dados do formulário ---
$telefone = $_POST['telefone'];
$nome = $_POST['nome'];
$funcionario = $_POST['funcionario'];
$hora = $_POST['hora'] ?? '';
$servico = $_POST['servico'];
$obs = $_POST['obs'];
$data = $_POST['data'];
$id = $_POST['id'] ?? ''; // ID do agendamento, se for uma edição

if (empty($hora)) {
    echo 'Escolha um Horário para Agendar!';
    exit();
}
if (empty($nome) || empty($telefone) || empty($funcionario) || empty($servico)) {
    echo 'Todos os campos obrigatórios devem ser preenchidos!';
    exit();
}


// Inicia uma transação. Ou tudo funciona, ou nada é salvo.
try {
    $pdo->beginTransaction();

    // --- 1. VERIFICA SE O HORÁRIO JÁ ESTÁ OCUPADO (DE FORMA SEGURA) ---
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
        throw new Exception('Este horário não está disponível!');
    }

    // --- 2. VERIFICA SE O CLIENTE EXISTE OU CADASTRA UM NOVO (DE FORMA SEGURA) ---
    $query_cliente = $pdo->prepare("SELECT id FROM clientes WHERE telefone = :telefone");
    $query_cliente->bindValue(':telefone', $telefone);
    $query_cliente->execute();
    $cliente_existente = $query_cliente->fetch(PDO::FETCH_ASSOC);

    $id_cliente = 0;
    if ($cliente_existente) {
        // Cliente já existe, pega o ID
        $id_cliente = $cliente_existente['id'];
    } else {
        // Cliente não existe, cadastra e pega o novo ID
        $query_insert_cli = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', alertado = 'Não'");
        $query_insert_cli->bindValue(":nome", $nome);
        $query_insert_cli->bindValue(":telefone", $telefone);
        $query_insert_cli->execute();
        $id_cliente = $pdo->lastInsertId();
    }


    // --- 3. INSERE OU ATUALIZA O AGENDAMENTO (DE FORMA SEGURA) ---
    $params_agendamento = [
        ':func' => $funcionario,
        ':cli' => $id_cliente,
        ':hora' => $hora,
        ':data' => $data,
        ':obs' => $obs,
        ':serv' => $servico
    ];

    if (empty($id)) {
        // --- CADASTRAR NOVO AGENDAMENTO ---
        $sql = "INSERT INTO agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, usuario = '0', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = :serv";
        $mensagem_sucesso = 'Agendado com Sucesso';
    } else {
        // --- EDITAR AGENDAMENTO EXISTENTE ---
        $sql = "UPDATE agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, obs = :obs, servico = :serv WHERE id = :id";
        $params_agendamento[':id'] = $id;
        $mensagem_sucesso = 'Editado com Sucesso';
    }
    
    $query_agendamento = $pdo->prepare($sql);
    $query_agendamento->execute($params_agendamento);

    // Se tudo deu certo até aqui, confirma as alterações no banco de dados
    $pdo->commit();
    echo $mensagem_sucesso;

} catch (PDOException $e) {
    // Se ocorrer um erro no banco de dados, desfaz tudo
    $pdo->rollBack();
    error_log("Erro ao salvar agendamento: " . $e->getMessage()); // Loga o erro real para o admin
    echo 'Falha ao salvar no banco de dados. Por favor, tente novamente.';

} catch (Exception $e) {
    // Captura outras exceções (como "Horário indisponível")
    echo $e->getMessage();
}

?>