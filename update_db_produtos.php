<?php
require_once("sistema/conexao.php");

try {
    $pdo->query("ALTER TABLE produtos ADD COLUMN usuario INT NOT NULL DEFAULT 0");
    echo "Column 'usuario' added successfully.";
} catch (Exception $e) {
    echo "Error or column already exists: " . $e->getMessage();
}
?>
