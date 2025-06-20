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

    // --- Validação de Dados Recebidos e Conversão de Tipo ---
    // Valida a presença e tipos básicos
    if (!isset($dadosVenda['totalVenda']) || !is_numeric($dadosVenda['totalVenda']) ||
        !isset($dadosVenda['formaPagamento']) || !is_string($dadosVenda['formaPagamento']) || empty($dadosVenda['formaPagamento']) ||
        !isset($dadosVenda['itens']) || !is_array($dadosVenda['itens']) || empty($dadosVenda['itens'])) {
        $response['erro'] = 'Dados da venda inválidos ou incompletos. Verifique o total da venda, forma de pagamento e itens.';
        echo json_encode($response);
        exit();
    }

    $totalVenda = (float) $dadosVenda['totalVenda'];
    $formaPagamento = trim($dadosVenda['formaPagamento']); // Remove espaços em branco

    $detalhesPagamento = null; // Inicializa com null
    if (isset($dadosVenda['detalhesPagamento'])) {
        // Verifica se é uma string antes de aplicar trim, conforme o erro de TypeError
        if (is_string($dadosVenda['detalhesPagamento'])) {
            $detalhesPagamento = trim($dadosVenda['detalhesPagamento']);
        } else {
            // Se não for uma string (ex: array, objeto), loga um aviso e define como null ou string vazia
            error_log("Aviso: 'detalhesPagamento' recebido não é uma string. Tipo: " . gettype($dadosVenda['detalhesPagamento']) . " | Venda Data: " . json_encode($dadosVenda));
            $detalhesPagamento = null; // Ou ''; dependendo da lógica de negócio
            // Você também pode adicionar um erro na resposta para o cliente se este campo for crítico
            // $response['erro'] = 'Detalhes de pagamento inválidos. Esperava uma string, mas recebeu um ' . gettype($dadosVenda['detalhesPagamento']) . '.';
            // echo json_encode($response);
            // exit();
        }
    }
    $itens = $dadosVenda['itens'];


    // Validação mais específica para totalVenda e formaPagamento
    if ($totalVenda < 0) {
        $response['erro'] = 'O total da venda não pode ser negativo.';
        echo json_encode($response);
        exit();
    }

    // Exemplo: Valide formaPagamento contra valores permitidos
    $allowedPaymentMethods = ['credito', 'debito', 'dinheiro', 'pix', 'boleto']; // Defina seus métodos de pagamento permitidos
    if (!in_array(strtolower($formaPagamento), $allowedPaymentMethods)) {
        $response['erro'] = 'Forma de pagamento inválida. Métodos permitidos: ' . implode(', ', $allowedPaymentMethods) . '.';
        echo json_encode($response);
        exit();
    }

    try {
        $pdo->beginTransaction(); // Inicia a transação

        // Inserir na tabela 'vendas'
        $stmtVenda = $pdo->prepare("INSERT INTO vendas (data_venda, total_venda, forma_pagamento, detalhes_pagamento) VALUES (DATETIME('now'), ?, ?, ?)");
        $stmtVenda->execute([$totalVenda, $formaPagamento, $detalhesPagamento]);
        $vendaId = $pdo->lastInsertId();

        // Preparar para inserir na tabela 'itens_venda'
        $stmtItem = $pdo->prepare("INSERT INTO itens_venda (venda_id, codigo_produto, descricao, quantidade, valor_unitario, total_item) VALUES (?, ?, ?, ?, ?, ?)");
        $invalidItemsCount = 0;
        $processedItems = [];

        foreach ($itens as $index => $item) {
            // Valida e converte tipos das propriedades do item
            $codigo = isset($item['codigo']) ? trim($item['codigo']) : '';
            $descricao = isset($item['descricao']) ? trim($item['descricao']) : '';
            $quantidade = (int) ($item['quantidade'] ?? 0);
            $valorUnitario = (float) ($item['valorUnitario'] ?? 0.0);
            $totalItem = (float) ($item['totalItem'] ?? 0.0);

            // Validação detalhada do item
            if (empty($codigo) || empty($descricao) || $quantidade <= 0 || $valorUnitario <= 0 || $totalItem <= 0) {
                $invalidItemsCount++;
                // Log ou colete informações sobre itens inválidos
                error_log("Item inválido na venda ID {$vendaId} (index {$index}): Codigo='{$codigo}', Descricao='{$descricao}', Quantidade='{$quantidade}', ValorUnitario='{$valorUnitario}', TotalItem='{$totalItem}'");
                continue; // Pula este item inválido
            }

            $stmtItem->execute([
                $vendaId,
                $codigo,
                $descricao,
                $quantidade,
                $valorUnitario,
                $totalItem
            ]);
            $processedItems[] = $item; // Mantém o controle dos itens processados com sucesso
        }

        if (empty($processedItems) && !empty($itens)) {
            // Se todos os itens foram inválidos, pode indicar um problema com a requisição
            $pdo->rollBack();
            $response['erro'] = 'Nenhum item válido foi encontrado na sua solicitação de venda. Por favor, verifique os dados dos itens.';
            echo json_encode($response);
            exit();
        }

        $pdo->commit(); // Confirma a transação
        $response['sucesso'] = true;
        $response['vendaId'] = $vendaId;
        $response['mensagem'] = 'Venda finalizada com sucesso!';
        if ($invalidItemsCount > 0) {
            $response['aviso'] = "Atenção: {$invalidItemsCount} item(ns) inválido(s) foram ignorados durante o processamento da venda.";
        }

    } catch (PDOException $e) {
        $pdo->rollBack(); // Em caso de erro, desfaz todas as operações da transação
        $response['erro'] = 'Erro ao salvar venda: ' . $e->getMessage();
        // Em ambiente de produção, é crucial logar o erro completo
        error_log("Erro em finaliza_venda.php: " . $e->getMessage() . " | SQLSTATE: " . $e->getCode() . " | Dados da Requisição: " . json_encode($dadosVenda));
    }
} else {
    // Definir o código de status HTTP 405 Method Not Allowed para requisições que não sejam POST
    http_response_code(405);
    $response['erro'] = 'Método de requisição não permitido.';
}

echo json_encode($response);
?>