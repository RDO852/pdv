<?php
header('Content-Type: application/json');

require 'conexao.php';

$stmt = $pdo->query('SELECT * FROM produtos ORDER BY descricao ASC');

$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($produtos);
?>
