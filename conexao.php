<?php
$host = 'localhost';
$db = 'pdv';
$user = 'root';
$pass = 'vtgd65aoty';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Para requisições AJAX, retorne JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(500); // 500 Internal Server Error
        echo json_encode(['sucesso' => false, 'erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
        exit;
    } else {
        // Para requisições normais (navegador), use die() ou uma página de erro
        die("Erro na conexão com o banco de dados: " . $e->getMessage());
    }
}
// Não há necessidade da tag de fechamento ?> em arquivos que contêm apenas código PHP