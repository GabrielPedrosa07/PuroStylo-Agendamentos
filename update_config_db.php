<?php
require_once("sistema/conexao.php");

try {
    $pdo->exec("ALTER TABLE config ADD COLUMN termos_uso TEXT");
    echo "Coluna termos_uso criada com sucesso.<br>";
} catch (Exception $e) {
    echo "Coluna termos_uso já existe ou erro: " . $e->getMessage() . "<br>";
}

try {
    $pdo->exec("ALTER TABLE config ADD COLUMN politica_privacidade TEXT");
    echo "Coluna politica_privacidade criada com sucesso.<br>";
} catch (Exception $e) {
    echo "Coluna politica_privacidade já existe ou erro: " . $e->getMessage() . "<br>";
}

// Populate with default text if empty
$default_termos = "<h2>Termos de Uso</h2><p>Bem-vindo ao nosso sistema de agendamentos. Ao utilizar nossos serviços, você concorda com os seguintes termos...</p>";
$default_politica = "<h2>Política de Privacidade</h2><p>Sua privacidade é importante para nós. Esta política descreve como coletamos e usamos suas informações...</p>";

try {
	$pdo->exec("UPDATE config SET termos_uso = '$default_termos', politica_privacidade = '$default_politica' WHERE termos_uso IS NULL OR termos_uso = ''");
	echo "Textos padrão inseridos.";
} catch (Exception $e) {
	echo "Erro ao inserir textos padrão: " . $e->getMessage();
}

?>
