<?php
require_once("c:/wamp64/www/PuroStylo-Agendamentos/sistema/conexao.php");

try {
    $pdo->query("ALTER TABLE receber ADD COLUMN forma_pgto VARCHAR(50) NULL");
    echo "Coluna 'forma_pgto' adicionada com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
