<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // Verifica se é uma requisição AJAX
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Se for AJAX, envia uma resposta JSON de erro
        header('Content-Type: application/json');
        http_response_code(401); // 401 Unauthorized
        echo json_encode(['sucesso' => false, 'erro' => 'Não autorizado. Por favor, faça login novamente.']);
        exit;
    } else {
        // Se não for AJAX, redireciona para a página de login
        header("Location: login.html");
        exit;
    }
}
