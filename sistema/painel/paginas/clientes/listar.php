<?php
// Arquivo: paginas/clientes/listar.php
require_once("../../../conexao.php");

try {
    // --- CONSULTA ÚNICA E OTIMIZADA COM LEFT JOIN ---
    // Buscamos todos os clientes e o nome do último serviço em uma única consulta.
    $query = $pdo->query("
        SELECT 
            c.id, c.nome, c.telefone, c.endereco, c.data_nasc, c.cartoes, c.data_cad, c.data_retorno, c.email,
            s.nome AS nome_servico
        FROM 
            clientes c
        LEFT JOIN 
            servicos s ON c.ultimo_servico = s.id
        ORDER BY 
            c.id DESC
    ");

    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    $data_atual = date('Y-m-d');
    $dados_para_datatable = [];

    // Prepara os dados formatados para o DataTables
    foreach ($res as $row) {
        $data_cadF = (new DateTime($row['data_cad']))->format('d/m/Y');
        $data_nascF = ($row['data_nasc'] == '0000-00-00' || $row['data_nasc'] == null) ? 'Não Lançado' : (new DateTime($row['data_nasc']))->format('d/m/Y');
        $data_retornoF = ($row['data_retorno'] == '0000-00-00' || $row['data_retorno'] == null) ? 'Nenhum' : (new DateTime($row['data_retorno']))->format('d/m/Y');

        // Adiciona uma classe se o retorno estiver atrasado
        $classe_retorno = '';
        if ($row['data_retorno'] != null && strtotime($row['data_retorno']) < strtotime($data_atual)) {
            $classe_retorno = 'text-danger';
        }
        
        $whats_limpo = '55' . preg_replace('/[ ()-]+/', '', $row['telefone']);

        // Adiciona os dados formatados ao array
        $dados_para_datatable[] = [
            "nome" => $row['nome'],
            "telefone" => $row['telefone'],
            "data_cad" => $data_cadF,
            "data_nasc" => $data_nascF,
            "data_retorno" => $data_retornoF,
            "cartoes" => $row['cartoes'],
            "acoes" => $row['id'], // Apenas o ID para os botões. Os dados completos já estão na 'row'
            
            // Dados completos para os modais, evitando múltiplas consultas
            "id" => $row['id'],
            "endereco" => $row['endereco'],
            "email" => $row['email'],
            "data_nasc_raw" => $row['data_nasc'],
            "nome_servico" => $row['nome_servico'] ?? 'Nenhum!',
            "classe_retorno" => $classe_retorno,
            "whats" => $whats_limpo
        ];
    }
    
    // Retorna os dados em formato JSON que o DataTables espera
    header('Content-Type: application/json');
    echo json_encode(["data" => $dados_para_datatable]);

} catch (PDOException $e) {
    // Trata erros de banco de dados
    http_response_code(500);
    echo json_encode(["error" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>