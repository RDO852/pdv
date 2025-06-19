<?php
require_once 'check_login.php'; // Protege este script também
require_once 'conexao.php';

header('Content-Type: application/json');

$dados = json_decode(file_get_contents('php://input'), true);

if ($dados) {
    $valorTotal = $dados['valor_total'];
    $formaPagamento = $dados['forma_pagamento'];
    $itens = $dados['itens']; // Array de itens da venda

    try {
        $pdo->beginTransaction(); // Inicia uma transação para garantir atomicidade

        // 1. Inserir a venda principal
        $stmtVenda = $pdo->prepare("INSERT INTO vendas (valor_total, forma_pagamento, data_hora) VALUES (:valor_total, :forma_pagamento, NOW())");
        $stmtVenda->bindParam(':valor_total', $valorTotal);
        $stmtVenda->bindParam(':forma_pagamento', $formaPagamento);
        $stmtVenda->execute();

        $idVenda = $pdo->lastInsertId(); // Pega o ID da venda recém-inserida

        // 2. Inserir os itens individuais da venda
        foreach ($itens as $item) {
            $stmtItem = $pdo->prepare("INSERT INTO itens_venda (id_venda, codigo_produto, descricao_produto, valor_unitario, quantidade) VALUES (:id_venda, :codigo, :descricao, :valor_unitario, :quantidade)");
            $stmtItem->bindParam(':id_venda', $idVenda);
            $stmtItem->bindParam(':codigo', $item['codigo']);
            $stmtItem->bindParam(':descricao', $item['descricao']);
            $stmtItem->bindParam(':valor_unitario', $item['valorUnitario']);
            $stmtItem->bindParam(':quantidade', $item['quantidade']);
            $stmtItem->execute();
        }

        $pdo->commit(); // Confirma a transação

        echo json_encode(['sucesso' => true, 'mensagem' => 'Venda registrada com sucesso!', 'id_venda' => $idVenda]);

    } catch (PDOException $e) {
        $pdo->rollBack(); // Em caso de erro, desfaz a transação
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao salvar venda no banco de dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados da venda não recebidos.']);
}
?>