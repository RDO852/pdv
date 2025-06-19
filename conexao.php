<?php
$databaseFile = 'C:\Users\User\Desktop\pdv projeto\SQLite\pdv.sqlite'; // Define o caminho para o arquivo do banco de dados SQLite

try {
    // Para SQLite, o DSN (Data Source Name) é 'sqlite:' seguido do caminho para o arquivo do banco de dados
    $pdo = new PDO("sqlite:$databaseFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Para SQLite, a configuração de charset geralmente não é necessária no DSN.
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
