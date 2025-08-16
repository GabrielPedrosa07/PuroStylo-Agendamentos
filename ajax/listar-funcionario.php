<?php 
require_once("../sistema/conexao.php");

// Recebe e valida a entrada
$funcionario_id = $_POST['func'] ?? 0;

if (empty($funcionario_id)) {
    echo 'ID não fornecido';
    exit();
}

// Prepara um valor padrão para a resposta
$nome_funcionario = 'Funcionário não encontrado';

try {
    // --- CONSULTA SEGURA COM PREPARED STATEMENT ---
    // Selecionamos apenas a coluna 'nome' por eficiência
    $query = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id");
    
    // Associamos o ID recebido ao placeholder :id de forma segura
    $query->bindValue(':id', $funcionario_id);
    
    // Executamos a consulta
    $query->execute();
    
    // fetch() é mais adequado aqui, pois esperamos apenas um resultado
    $resultado = $query->fetch(PDO::FETCH_ASSOC);

    // Se a consulta encontrou um resultado, atualizamos a variável $nome_funcionario
    if ($resultado) {
        $nome_funcionario = $resultado['nome'];
    }

} catch (PDOException $e) {
    // Em caso de erro de conexão ou sintaxe, podemos logar o erro
    // e retornar uma mensagem genérica para o usuário.
    error_log("Erro ao buscar nome de funcionário: " . $e->getMessage());
    $nome_funcionario = 'Erro ao consultar';
}

// Retorna o nome encontrado ou a mensagem padrão
echo $nome_funcionario;

?>