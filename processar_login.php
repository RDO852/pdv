<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json'); // Mantém para a resposta JSON

$input = json_decode(file_get_contents('php://input'), true);

$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha usuário e senha.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password_hash, nome_completo FROM usuarios WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nome_completo'] = $user['nome_completo'];

        // Redirecionamento para o menu.php
        echo json_encode(['success' => true, 'redirect' => 'menu.php']); // <-- ESTA LINHA É CRUCIAL
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha inválidos.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>