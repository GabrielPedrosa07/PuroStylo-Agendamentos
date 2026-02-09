<?php
require_once("../../../conexao.php");

@session_start();
$usuario_logado = $_SESSION['id'] ?? 0;

// --- Recebendo dados do formulário ---
$cliente_id = $_POST['cliente_agd'];
$data_pgto = $_POST['data_pgto'];
$id_agd = $_POST['id_agd'] ?? 0;
$valor_serv_pago = $_POST['valor_serv_agd'];
$funcionario_id = $_POST['funcionario_agd'];
$servico_id = $_POST['servico_agd'];
$forma_pgto = $_POST['forma_pgto'];

// Inicia uma transação. Todas as queries a seguir devem ter sucesso.
// Se qualquer uma falhar, todas serão revertidas (rollback).
try {
    $pdo->beginTransaction();

    // 1. BUSCA DADOS DO SERVIÇO DE FORMA SEGURA E UMA ÚNICA VEZ
    $query_serv = $pdo->prepare("SELECT nome, valor, comissao, dias_retorno FROM servicos WHERE id = :servico_id");
    $query_serv->bindValue(':servico_id', $servico_id);
    $query_serv->execute();
    $servico_dados = $query_serv->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico_dados) {
        throw new Exception("Serviço não encontrado!");
    }

    $valor_base_servico = $servico_dados['valor'];
    $comissao_percentual = $servico_dados['comissao'];
    $nome_servico = $servico_dados['nome'];
    $dias_retorno = $servico_dados['dias_retorno'];
    $descricao_comissao = 'Comissão - ' . $nome_servico;

    // 2. CALCULA A COMISSÃO
    if ($tipo_comissao == 'Porcentagem') {
        $valor_comissao = ($comissao_percentual * $valor_base_servico) / 100;
    } else {
        $valor_comissao = $comissao_percentual;
    }

    // 3. VERIFICA O STATUS DO PAGAMENTO E LANÇA A COMISSÃO SE NECESSÁRIO
    $data_atual = date('Y-m-d');
    $pago_status = 'Não';
    $data_pgto_db = null; // Usar null para datas vazias é melhor
    $usuario_baixa_db = null;

    if (strtotime($data_pgto) <= strtotime($data_atual)) {
        $pago_status = 'Sim';
        $data_pgto_db = $data_pgto;
        $usuario_baixa_db = $usuario_logado;

        // Lança a comissão como uma conta a pagar (de forma segura)
        $query_comissao = $pdo->prepare("INSERT INTO pagar SET descricao = :desc, tipo = 'Comissão', valor = :val, data_lanc = :data_lanc, data_venc = :data_venc, usuario_lanc = :usu_lanc, foto = 'sem-foto.jpg', pago = 'Não', funcionario = :func, servico = :serv, cliente = :cli");
        $query_comissao->execute([
            ':desc' => $descricao_comissao,
            ':val' => $valor_comissao,
            ':data_lanc' => $data_pgto,
            ':data_venc' => $data_pgto,
            ':usu_lanc' => $usuario_logado,
            ':func' => $funcionario_id,
            ':serv' => $servico_id,
            ':cli' => $cliente_id
        ]);
    }

    // 4. INSERE A CONTA A RECEBER PELO SERVIÇO (DE FORMA SEGURA)
    $query_receber = $pdo->prepare("INSERT INTO receber SET descricao = :desc, tipo = 'Serviço', valor = :val, data_lanc = curDate(), data_venc = :data_venc, data_pgto = :data_pgto, usuario_lanc = :usu_lanc, usuario_baixa = :usu_baixa, foto = 'sem-foto.jpg', pessoa = :pessoa, pago = :pago, servico = :serv, funcionario = :func, forma_pgto = :forma_pgto");
    $query_receber->execute([
        ':desc' => $nome_servico,
        ':val' => $valor_serv_pago,
        ':data_venc' => $data_pgto,
        ':data_pgto' => $data_pgto_db,
        ':usu_lanc' => $usuario_logado,
        ':usu_baixa' => $usuario_baixa_db,
        ':pessoa' => $cliente_id,
        ':pago' => $pago_status,
        ':serv' => $servico_id,
        ':func' => $funcionario_id,
        ':forma_pgto' => $forma_pgto
    ]);

    // 5. ATUALIZA O STATUS DO AGENDAMENTO (DE FORMA SEGURA)
    $query_agd = $pdo->prepare("UPDATE agendamentos SET status = 'Concluído' WHERE id = :id_agd");
    $query_agd->bindValue(':id_agd', $id_agd);
    $query_agd->execute();

    // 6. ATUALIZA OS DADOS DO CLIENTE (DE FORMA SEGURA)
    // Busca o total de cartões atual do cliente
    $query_cli = $pdo->prepare("SELECT cartoes FROM clientes WHERE id = :id_cli");
    $query_cli->bindValue(':id_cli', $cliente_id);
    $query_cli->execute();
    $cliente_dados = $query_cli->fetch(PDO::FETCH_ASSOC);
    $total_cartoes_atual = $cliente_dados['cartoes'] ?? 0;

    $novo_total_cartoes = ($total_cartoes_atual >= $quantidade_cartoes) ? 0 : $total_cartoes_atual + 1;
    $data_retorno = date('Y-m-d', strtotime("+$dias_retorno days", strtotime($data_atual)));

    $query_update_cli = $pdo->prepare("UPDATE clientes SET cartoes = :cartoes, data_retorno = :data_retorno, ultimo_servico = :ultimo_servico, alertado = 'Não' WHERE id = :id_cli");
    $query_update_cli->execute([
        ':cartoes' => $novo_total_cartoes,
        ':data_retorno' => $data_retorno,
        ':ultimo_servico' => $servico_id,
        ':id_cli' => $cliente_id
    ]);

    // Se tudo deu certo, confirma as alterações no banco de dados
    $pdo->commit();
    echo 'Salvo com Sucesso';

} catch (PDOException $e) {
    // Se algo deu errado, desfaz todas as alterações
    $pdo->rollBack();
    echo 'Falha ao salvar no banco de dados. Erro: ' . $e->getMessage();
}
?>