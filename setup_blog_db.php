<?php
require_once("sistema/conexao.php");

try {
    $pdo->query("CREATE TABLE IF NOT EXISTS blog (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(150) NOT NULL,
        nome_url VARCHAR(150) NOT NULL,
        descricao TEXT NOT NULL,
        imagem VARCHAR(100) NOT NULL,
        conteudo LONGTEXT NOT NULL,
        data DATE NOT NULL,
        palavras VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
        ativo VARCHAR(5) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    $pdo->query("CREATE TABLE IF NOT EXISTS imagens_blog (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_blog INT(11) NOT NULL,
        imagem VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    echo "Tabelas criadas com sucesso!";
} catch (Exception $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>
