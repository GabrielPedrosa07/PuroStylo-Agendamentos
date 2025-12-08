<?php
require_once('../conexao.php');

try {
    // Array of columns to add
    $columns = [
        'cor_sistema' => "VARCHAR(255) DEFAULT '#000000'",
        'cor_icone' => "VARCHAR(255) DEFAULT '#d4c7a3'" 
    ];

    foreach ($columns as $col => $def) {
        $query = $pdo->query("SHOW COLUMNS FROM config LIKE '$col'");
        $column = $query->fetch(PDO::FETCH_ASSOC);

        if (!$column) {
            $pdo->query("ALTER TABLE config ADD $col $def");
            echo "Column '$col' added successfully.<br>";
        } else {
            echo "Column '$col' already exists.<br>";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
