<?php
require_once("sistema/conexao.php");

try {
    // Add email field if it doesn't exist
    $pdo->query("ALTER TABLE clientes ADD COLUMN email VARCHAR(100) NULL AFTER telefone");
    
    // Add password field (senha)
    // Using simple hashing or just storing for now (based on user system, md5 seems to be used)
    $pdo->query("ALTER TABLE clientes ADD COLUMN senha VARCHAR(100) NULL AFTER email");
    
    // Add senha_crip for MD5 matching existing pattern
     $pdo->query("ALTER TABLE clientes ADD COLUMN senha_crip VARCHAR(100) NULL AFTER senha");

    echo "Tabela clientes atualizada com sucesso!";
} catch (Exception $e) {
    echo "Erro (pode ser que já existam): " . $e->getMessage();
}
?>
