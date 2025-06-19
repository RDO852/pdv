<?php
require_once 'check_login.php'; // Protege este script também
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    // Consulta para selecionar todas as vendas finalizadas
    // Adapte o SELECT e a tabela conforme a sua estrutura de banco de dados
    $stmt = $pdo->prepare("SELECT id_venda, data_hora, valor_total, forma_pagamento FROM vendas ORDER BY data_hora DESC");
    $stmt->execute();

    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($vendas) {
        echo json_encode($vendas);
    } else {
        echo json_encode(['erro' => 'Nenhuma venda finalizada encontrada.']);
    }

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao consultar o banco de dados para vendas: ' . $e->getMessage()]);
}
?>