<?php
require_once 'check_login.php'; // Protege este script também
require_once 'conexao.php';

header('Content-Type: application/json');

try {
    $limitePorPagina = 15;
    $paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($paginaAtual - 1) * $limitePorPagina;

    // Consulta para contar o total de vendas
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM vendas");
    $stmtCount->execute();
    $totalVendas = $stmtCount->fetchColumn();

    // Consulta para selecionar as vendas finalizadas com paginação
    $stmt = $pdo->prepare("SELECT id_venda, data_venda, total_venda, forma_pagamento, detalhes_pagamento FROM vendas ORDER BY data_venda DESC LIMIT :limite OFFSET :offset");
    $stmt->bindParam(':limite', $limitePorPagina, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepara os dados para incluir o total de páginas e a página atual
    $resposta = [
        'totalVendas' => $totalVendas,
        'vendas' => $vendas,
        'paginaAtual' => $paginaAtual,
        'limitePorPagina' => $limitePorPagina
    ];

    echo json_encode($resposta);

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro ao consultar o banco de dados para vendas: ' . $e->getMessage()]);
}
?>