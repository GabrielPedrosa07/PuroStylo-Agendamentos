<?php
$tabela = 'clientes';
require_once("../sistema/conexao.php");

// --- Recebendo e validando os dados ---
$nome = $_POST['nome'];
$telefone = $_POST['telefone'];

if (empty($nome) || empty($telefone)) {
    echo 'Nome e telefone são obrigatórios!';
    exit();
}

try {
    // --- VERIFICA SE O TELEFONE JÁ EXISTE (DE FORMA SEGURA) ---
    // Usamos prepare() para a verificação, selecionando apenas o ID por performance.
    $query_verif = $pdo->prepare("SELECT id FROM $tabela WHERE telefone = :telefone");
    $query_verif->bindValue(':telefone', $telefone);
    $query_verif->execute();

    // rowCount() é uma forma eficiente de verificar se a consulta retornou alguma linha.
    if ($query_verif->rowCount() > 0) {
        echo 'Telefone já Cadastrado, você já possui um cadastro!';
        exit();
    }


    // --- INSERE O NOVO CLIENTE (SEU CÓDIGO JÁ ESTAVA CORRETO E SEGURO AQUI) ---
    $query = $pdo->prepare("INSERT INTO $tabela SET nome = :nome, telefone = :telefone, data_cad = curDate(), cartoes = '0', alertado = 'Não'");
    $query->bindValue(":nome", $nome);
    $query->bindValue(":telefone", $telefone);
    $query->execute();

    echo 'Salvo com Sucesso';

} catch (PDOException $e) {
    // Captura qualquer erro do banco de dados e retorna uma mensagem amigável.
    error_log("Erro ao cadastrar cliente: " . $e->getMessage()); // Loga o erro para o admin.
    echo 'Ocorreu um erro ao tentar salvar. Por favor, tente novamente.';
}
?>