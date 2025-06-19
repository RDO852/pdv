<?php
// Protege esta página para que só usuários logados possam acessá-la
require_once 'check_login.php';

// Opcional: Pegar o nome do usuário para exibir na tela de boas-vindas
$nomeUsuario = $_SESSION['nome_completo'] ?? $_SESSION['username'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal - Sistema PDV</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos específicos para a tela do menu */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .menu-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            max-width: 90%;
        }

        .menu-container h2 {
            margin-bottom: 30px;
            color: #333;
        }

        .menu-buttons {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .menu-buttons button {
            padding: 15px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .menu-buttons button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .logout-button {
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #dc3545; /* Cor de perigo para o botão de sair */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <h2>Bem-vindo(a), <?php echo htmlspecialchars($nomeUsuario); ?>!</h2>
        <div class="menu-buttons">
            <button onclick="window.location.href='pdv.php'">PDV</button>
            <button onclick="window.location.href='consultar_vendas.php'">Consultar Vendas</button>
        </div>
        <button class="logout-button" onclick="window.location.href='logout.php'">Sair</button>
    </div>
</body>
</html>