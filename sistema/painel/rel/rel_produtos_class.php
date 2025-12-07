<?php
// O include da conexão provavelmente já está no arquivo do relatório,
// mas se não estiver, mantenha-o aqui.
include('../../conexao.php');

// Suppress warnings that break DomPDF output
error_reporting(0);
ini_set('display_errors', 0);

// Variáveis que o seu relatório precisa (ex: filtros de data, etc.)
// Devem ser definidas aqui para que o arquivo incluído possa usá-las.
// Ex: $data_inicial = $_GET['data_inicial'];

// 1. CAPTURAR HTML COM OUTPUT BUFFERING (MÉTODO RECOMENDADO)
ob_start(); // Inicia o buffer de saída

// Inclui o arquivo do relatório. Todo o HTML que ele gerar
// será armazenado no buffer em vez de ser enviado para o navegador.
include('rel_produtos.php'); 

$html = ob_get_contents(); // Pega o conteúdo do buffer e armazena na variável $html

ob_end_clean(); // Limpa e desativa o buffer de saída


// Se a intenção é apenas ver o HTML para depuração
if(isset($_GET['debug'])){
    echo $html;
    exit();
}


// 2. CARREGAR E CONFIGURAR O DOMPDF
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

//INICIALIZAR A CLASSE DO DOMPDF
$options = new Options();
// Habilitar o carregamento de imagens e CSS remotos (URLs)
$options->set('isRemoteEnabled', true); 

$pdf = new Dompdf($options);

//Definir o tamanho do papel e orientação da página
$pdf->set_paper('A4', 'portrait');

//CARREGAR O CONTEÚDO HTML
$pdf->load_html($html);

//RENDERIZAR O PDF
$pdf->render();

//NOMEAR E ENVIAR O PDF PARA O NAVEGADOR
// A função stream() JÁ CUIDA DOS HEADERS HTTP CORRETOS.
$pdf->stream(
'relatorio_produtos.pdf',
array("Attachment" => false) // false = abre no navegador, true = força o download
);

?>