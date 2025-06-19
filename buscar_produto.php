<?php
require_once 'check_login.php'; // Protege este script também
require_once 'conexao.php'; // Inclua o arquivo de conexão

header('Content-Type: application/json'); // Garante que a resposta será JSON

if (isset($_GET['codigo'])) {
    $codigo = $_GET['codigo'];

    try {
        // Prepara a consulta SQL para buscar o produto pelo código
        $stmt = $pdo->prepare("SELECT codigo, descricao, valor_unitario FROM produtos WHERE codigo = :codigo");
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();

        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            // Se o produto for encontrado, retorna os dados em JSON
            echo json_encode($produto);
        } else {
            // Se o produto não for encontrado, retorna uma mensagem de erro
            echo json_encode(['erro' => 'Produto não encontrado.']);
        }
    } catch (PDOException $e) {
        // Em caso de erro na consulta, retorna uma mensagem de erro
        echo json_encode(['erro' => 'Erro ao consultar o banco de dados: ' . $e->getMessage()]);
    }
} else {
    // Se o parâmetro 'codigo' não for enviado, retorna uma mensagem de erro
    echo json_encode(['erro' => 'Código do produto não fornecido.']);
}
?>