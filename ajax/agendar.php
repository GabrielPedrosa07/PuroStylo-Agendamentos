<?php
@session_start();
require_once("../sistema/conexao.php");

// --- 1. RECEBENDO E VALIDANDO OS DADOS DO FORMULÁRIO ---
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

$limite_diario = 5; // Defina o limite que você quer aqui!

// Inicia o bloco principal de execução
try {
    
    // --- 2. NOVA VERIFICAÇÃO DE LIMITE DE AGENDAMENTOS ---
    // (só executa se for um novo agendamento, não uma edição)
    if (empty($id)) {
        
        $id_cliente_check = null;

        if(isset($_SESSION['id_cliente'])){
             $id_cliente_check = $_SESSION['id_cliente'];
        } else {
            // Busca o ID do cliente pelo telefone fornecido
            $query_cliente_check = $pdo->prepare("SELECT id FROM clientes WHERE telefone = :telefone");
            $query_cliente_check->bindValue(':telefone', $telefone);
            $query_cliente_check->execute();
            $cliente_dados = $query_cliente_check->fetch(PDO::FETCH_ASSOC);
            if ($cliente_dados) {
                $id_cliente_check = $cliente_dados['id'];
            }
        }

        if ($id_cliente_check) {
            // Conta quantos agendamentos esse cliente já tem para o dia
            $query_limite = $pdo->prepare("SELECT COUNT(id) as total_hoje FROM agendamentos WHERE cliente = :cliente AND data = :data");
            $query_limite->execute([':cliente' => $id_cliente_check, ':data' => $data]);
            $resultado = $query_limite->fetch(PDO::FETCH_ASSOC);
            $agendamentos_feitos = $resultado['total_hoje'] ?? 0;

            if ($agendamentos_feitos >= $limite_diario) {
                // Lança uma exceção em vez de 'echo' para ser capturada pelo 'catch'
                throw new Exception('Você já atingiu o limite de ' . $limite_diario . ' agendamentos para este dia. Por favor, entre em contato.');
            }
        }
    }

    // --- Inicia a transação APÓS as verificações iniciais ---
    $pdo->beginTransaction();

    // --- 3. VERIFICA SE O HORÁRIO JÁ ESTÁ OCUPADO ---
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

    // --- 4. VERIFICA SE O CLIENTE EXISTE OU CADASTRA UM NOVO ---
    if(isset($_SESSION['id_cliente'])){
        $id_cliente = $_SESSION['id_cliente'];
    } else {
        $query_cliente = $pdo->prepare("SELECT id FROM clientes WHERE telefone = :telefone");
        $query_cliente->bindValue(':telefone', $telefone);
        $query_cliente->execute();
        $cliente_existente = $query_cliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente_existente) {
            $id_cliente = $cliente_existente['id'];
        } else {
            $query_insert_cli = $pdo->prepare("INSERT INTO clientes SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', alertado = 'Não'");
            $query_insert_cli->bindValue(":nome", $nome);
            $query_insert_cli->bindValue(":telefone", $telefone);
            $query_insert_cli->execute();
            $id_cliente = $pdo->lastInsertId();
        }
    }

    // --- 5. INSERE OU ATUALIZA O AGENDAMENTO ---
    $params_agendamento = [
        ':func' => $funcionario,
        ':cli' => $id_cliente,
        ':hora' => $hora,
        ':data' => $data,
        ':obs' => $obs,
        ':serv' => $servico
    ];

    if (empty($id)) {
        $sql = "INSERT INTO agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, usuario = '0', status = 'Agendado', obs = :obs, data_lanc = curDate(), servico = :serv";
        $mensagem_sucesso = 'Agendado com Sucesso';
    } else {
        $sql = "UPDATE agendamentos SET funcionario = :func, cliente = :cli, hora = :hora, data = :data, obs = :obs, servico = :serv WHERE id = :id";
        $params_agendamento[':id'] = $id;
        $mensagem_sucesso = 'Editado com Sucesso';
    }
    
    $query_agendamento = $pdo->prepare($sql);
    $query_agendamento->execute($params_agendamento);

    // Se tudo deu certo, confirma as alterações no banco de dados
    $pdo->commit();
    
    // Busca telefone do funcionário
    $query_tel_func = $pdo->query("SELECT telefone FROM usuarios WHERE id = '$funcionario'");
    $res_tel = $query_tel_func->fetch(PDO::FETCH_ASSOC);
    $tel_profissional = $res_tel['telefone'] ?? '';

    // Busca nome do serviço
    $query_nome_serv = $pdo->query("SELECT nome FROM servicos WHERE id = '$servico'");
    $res_serv = $query_nome_serv->fetch(PDO::FETCH_ASSOC);
    $nome_servico = $res_serv['nome'] ?? '';

    // Formata a mensagem
    $msg_whatsapp = "Olá, o cliente *$nome* acabou de agendar o serviço *$nome_servico* para o dia *" . date('d/m/Y', strtotime($data)) . "* às *$hora*.";

    // Retorna JSON
    echo json_encode([
        'status' => 'success',
        'message' => $mensagem_sucesso,
        'whatsapp_link' => "http://api.whatsapp.com/send?1=pt_BR&phone=55$tel_profissional&text=" . urlencode($msg_whatsapp)
    ]);

} catch (PDOException $e) {
    // Se ocorrer um erro no banco de dados, desfaz tudo
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    error_log("Erro ao salvar agendamento: " . $e->getMessage());
    echo 'Falha ao salvar no banco de dados. Por favor, tente novamente.';

} catch (Exception $e) {
    // Captura outras exceções (como "Horário indisponível" ou "Limite atingido")
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo $e->getMessage();
}
?>