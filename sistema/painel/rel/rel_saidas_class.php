<?php

// Inclui a conexão e as configurações globais
include_once '../../conexao.php';

// Suppress warnings that break DomPDF output
error_reporting(0);
ini_set('display_errors', 0);

// Recebe os dados do formulário via POST
$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$filtro = $_POST['filtro']; 

// Se o tipo de relatório não for PDF, podemos gerar o HTML e parar.
// Isso é útil para depuração.
if ($tipo_rel != 'PDF') {
    include 'rel_saidas.php';
    exit();
}

// --- GERAÇÃO DO PDF ---

// 1. CARREGAR A BIBLIOTECA DOMPDF
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// 2. CAPTURAR O HTML DO RELATÓRIO USANDO OUTPUT BUFFERING
ob_start();

// As variáveis enviadas já estão disponíveis
include 'rel_saidas.php';

$html = ob_get_contents(); 
ob_end_clean(); 

// 3. INICIALIZAR E CONFIGURAR O DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true); 

$pdf = new Dompdf($options);

// 4. CARREGAR O HTML NO DOMPDF
$pdf->load_html($html);

// 5. DEFINIR O TAMANHO E A ORIENTAÇÃO DO PAPEL
$pdf->set_paper('A4', 'portrait');

// 6. RENDERIZAR O HTML PARA PDF
$pdf->render();

// 7. ENVIAR O PDF PARA O NAVEGADOR
$pdf->stream(
    'relatorio_saidas.pdf',
    array("Attachment" => false) 
);

?>