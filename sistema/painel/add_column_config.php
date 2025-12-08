<?php
require_once('../conexao.php');

try {
    // Check if column exists
    $query = $pdo->query("SHOW COLUMNS FROM config LIKE 'tipo_sistema'");
    $column = $query->fetch(PDO::FETCH_ASSOC);

    if (!$column) {
        $pdo->query("ALTER TABLE config ADD tipo_sistema VARCHAR(255) DEFAULT 'Barbearia'");
        echo "Column 'tipo_sistema' added successfully.";
    } else {
        echo "Column 'tipo_sistema' already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
