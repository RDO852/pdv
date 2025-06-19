<?php
header('Content-Type: application/json'); // Garante que a resposta será JSON

require_once 'check_login.php'; // Protege este script
require_once 'conexao.php';    // Inclui o arquivo de conexão com o banco de dados

$response = ['sucesso' => false, 'erro' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $dadosVenda = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['erro'] = 'Erro ao decodificar JSON: ' . json_last_error_msg();
        echo json_encode($response);
        exit();
    }

    // Validação de dados recebidos e conversão de tipo
    if (!isset($dadosVenda['totalVenda']) || !is_numeric($dadosVenda['totalVenda']) || $dadosVenda['totalVenda'] < 0 ||
        !isset($dadosVenda['formaPagamento']) || empty($dadosVenda['formaPagamento']) ||
        !isset($dadosVenda['itens']) || !is_array($dadosVenda['itens']) || empty($dadosVenda['itens'])) {
        $response['erro'] = 'Dados da venda inválidos ou incompletos. Verifique o total da venda, forma de pagamento e itens.';
        echo json_encode($response);
        exit();
    }

    $totalVenda = (float) $dadosVenda['totalVenda'];
    $formaPagamento = $dadosVenda['formaPagamento'];
    $itens = $dadosVenda['itens'];
    $detalhesPagamento = $dadosVenda['detalhesPagamento'] ?? null; // Adiciona suporte a detalhes de pagamento

    try {
        $pdo->beginTransaction(); // Inicia a transação

        // Inserir na tabela 'vendas'
        $stmtVenda = $pdo->prepare("INSERT INTO vendas (data_venda, total_venda, forma_pagamento, detalhes_pagamento) VALUES (NOW(), ?, ?, ?)");
        $stmtVenda->execute([$totalVenda, $formaPagamento, $detalhesPagamento]);
        $vendaId = $pdo->lastInsertId();

        // Inserir na tabela 'itens_venda'
        $stmtItem = $pdo->prepare("INSERT INTO itens_venda (venda_id, codigo_produto, descricao, quantidade, valor_unitario, total_item) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($itens as $item) {
            $codigo = $item['codigo'];
            $descricao = $item['descricao'];
            $quantidade = (int) ($item['quantidade'] ?? 0); // Garante que é inteiro
            $valorUnitario = (float) ($item['valorUnitario'] ?? 0.0); // Garante que é float
            $totalItem = (float) ($item['totalItem'] ?? 0.0); // Garante que é float

            // Basic item validation
            if (empty($codigo) || empty($descricao) || $quantidade <= 0 || $valorUnitario <= 0) {
                // Você pode querer logar isso ou retornar um erro mais específico
                // Por enquanto, itens inválidos serão ignorados
                continue;
            }

            $stmtItem->execute([
                $vendaId,
                $codigo,
                $descricao,
                $quantidade,
                $valorUnitario,
                $totalItem
            ]);
        }

        $pdo->commit(); // Confirma a transação
        $response['sucesso'] = true;
        $response['vendaId'] = $vendaId;
        $response['mensagem'] = 'Venda finalizada com sucesso!';

    } catch (PDOException $e) {
        $pdo->rollBack(); // Em caso de erro, desfaz todas as operações da transação
        $response['erro'] = 'Erro ao salvar venda: ' . $e->getMessage();
        // Em ambiente de produção, é crucial logar o erro completo
        error_log("Erro no finalizar_venda.php: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode());
    }
} else {
    // Definir o código de status HTTP 405 Method Not Allowed para requisições que não sejam POST
    http_response_code(405);
    $response['erro'] = 'Método de requisição não permitido.';
}

echo json_encode($response);
// Não há necessidade da tag de fechamento ?> em arquivos que contêm apenas código PHP
// exit(); // O exit() já está no final, mas pode ser usado para parar a execução explicitamente em pontos específicos