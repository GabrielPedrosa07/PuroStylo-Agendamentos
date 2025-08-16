<?php

// Inclui a conexão e as configurações globais (onde $url_sistema e $tipo_rel devem estar)
include_once '../../conexao.php';

// Recebe os dados do formulário via POST
$dataInicial = $_POST['dataInicial'];
$dataFinal = $_POST['dataFinal'];
$filtro = $_POST['filtro']; // Não precisa de urlencode aqui

// Se o tipo de relatório não for PDF, podemos gerar o HTML e parar.
// Isso é útil para depuração.
if ($tipo_rel != 'PDF') {
    // Para depurar, podemos incluir o arquivo do relatório diretamente
    include 'rel_saidas.php';
    exit();
}

// --- GERAÇÃO DO PDF ---

// 1. CARREGAR A BIBLIOTECA DOMPDF
require_once '../../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// 2. CAPTURAR O HTML DO RELATÓRIO USANDO OUTPUT BUFFERING
// Este método é mais rápido, seguro e confiável.
ob_start();

// As variáveis $dataInicial, $dataFinal e $filtro já estão disponíveis
// para o arquivo incluído abaixo, sem precisar passar pela URL.
include 'rel_saidas.php';

$html = ob_get_contents(); // Pega todo o HTML gerado e armazena na variável

ob_end_clean(); // Limpa o buffer de saída

// 3. INICIALIZAR E CONFIGURAR O DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true); // Permite carregar imagens de URLs externas

$pdf = new Dompdf($options);

// 4. CARREGAR O HTML NO DOMPDF
$pdf->load_html($html);

// 5. DEFINIR O TAMANHO E A ORIENTAÇÃO DO PAPEL
$pdf->set_paper('A4', 'portrait');

// 6. RENDERIZAR O HTML PARA PDF
$pdf->render();

// 7. ENVIAR O PDF PARA O NAVEGADOR
// A função stream() já configura os headers HTTP corretos (como Content-Type: application/pdf)
$pdf->stream(
    'relatorio_saidas.pdf',
    array("Attachment" => false) // false = visualizar no navegador, true = forçar download
);

?>