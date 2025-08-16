<?php

// Inclui a conexão e as configurações globais
include_once '../../conexao.php';

// Recebe os dados do formulário via POST
$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$filtro = $_POST['filtro'];

// Se o tipo de relatório não for PDF, gera o HTML e para (útil para depuração)
if ($tipo_rel != 'PDF') {
    include 'rel_entradas.php';
    exit();
}

// --- GERAÇÃO DO PDF ---

// 1. CARREGAR A BIBLIOTECA DOMPDF
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// 2. CAPTURAR O HTML DO RELATÓRIO USANDO OUTPUT BUFFERING
ob_start();

// As variáveis $dataInicial, $dataFinal e $filtro já estão disponíveis para o arquivo incluído
include 'rel_entradas.php';

$html = ob_get_contents(); // Pega o HTML gerado e armazena na variável
ob_end_clean(); // Limpa o buffer

// 3. INICIALIZAR E CONFIGURAR O DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true); // Permite carregar imagens de URLs

$pdf = new Dompdf($options);

// 4. CARREGAR O HTML NO DOMPDF
$pdf->load_html($html);

// 5. DEFINIR O TAMANHO E A ORIENTAÇÃO DO PAPEL
$pdf->set_paper('A4', 'portrait');

// 6. RENDERIZAR O HTML PARA PDF
$pdf->render();

// 7. ENVIAR O PDF PARA O NAVEGADOR
// A função stream() já configura os headers HTTP corretos
$pdf->stream(
    'relatorio_entradas.pdf',
    array("Attachment" => false) // false = visualizar, true = forçar download
);

?>