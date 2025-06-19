<?php
session_start(); // Inicia a sessão para poder destruí-la

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se for preciso destruir também os cookies de sessão, use:
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }

// Finalmente, destrói a sessão
session_destroy();

// Redireciona para a página de login
header("Location: login.html");
exit;
?>