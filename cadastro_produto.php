<?php
// Protege esta página para que só usuários logados possam acessá-la
require_once 'check_login.php';
require_once 'conexao.php'; // Para conexão com o banco de dados

$mensagem = ''; // Para exibir mensagens de sucesso ou erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $nome_produto_form = trim($_POST['nome_produto'] ?? '');
    $codigo_barras_form = trim($_POST['codigo_barras'] ?? null);
    $preco_venda_form = str_replace(',', '.', trim($_POST['preco_venda'] ?? '0'));
    $estoque_form = (int)($_POST['estoque_atual'] ?? 0);

    // Novos campos
    $custo_compra_form = str_replace(',', '.', trim($_POST['custo_compra'] ?? '0'));
    $unidade_medida_form = trim($_POST['unidade_medida'] ?? 'Unidade');
    $categoria_form = trim($_POST['categoria'] ?? null);
    $ativo_form = isset($_POST['ativo']) ? 1 : 0;

    // Validação básica
    if (empty($nome_produto_form) || !is_numeric($preco_venda_form) || $preco_venda_form <= 0 || !is_numeric($estoque_form) || $estoque_form < 0) {
        $mensagem = "<p class='mensagem-erro'>Erro: Nome do produto, Preço de Venda e Estoque são campos obrigatórios e devem ser válidos.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO produtos (codigo, descricao, valor_unitario, estoque, custo_compra, unidade_medida, categoria, ativo) VALUES (:codigo_barras, :nome_produto, :preco_venda, :estoque, :custo_compra, :unidade_medida, :categoria, :ativo)");

            $stmt->bindParam(':codigo_barras', $codigo_barras_form);
            $stmt->bindParam(':nome_produto', $nome_produto_form);
            $stmt->bindParam(':preco_venda', $preco_venda_form);
            $stmt->bindParam(':estoque', $estoque_form);
            $stmt->bindParam(':custo_compra', $custo_compra_form);
            $stmt->bindParam(':unidade_medida', $unidade_medida_form);
            $stmt->bindParam(':categoria', $categoria_form);
            $stmt->bindParam(':ativo', $ativo_form, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $mensagem = "<p class='mensagem-sucesso'>Produto '$nome_produto_form' cadastrado com sucesso!</p>";
                $_POST = array(); // Limpa os campos do formulário
            } else {
                $mensagem = "<p class='mensagem-erro'>Erro ao cadastrar produto. Tente novamente.</p>";
            }

        } catch (PDOException $e) {
            $mensagem = "<p class='mensagem-erro'>Erro no banco de dados: " . $e->getMessage() . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produto</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos ajustados para esta tela de cadastro */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-y: auto; /* Garante a barra de rolagem se o conteúdo ainda for muito grande */
            -webkit-overflow-scrolling: touch;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .container-cadastro {
            padding: 30px;
            max-width: 800px; /* Aumenta um pouco a largura máxima do container */
            width: 90%;
            margin: 20px auto; /* Reduz margem vertical superior/inferior para otimizar espaço */
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }
        .container-cadastro h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        /* Flexbox para agrupar campos */
        .form-row {
            display: flex;
            flex-wrap: wrap; /* Permite que os itens quebrem a linha */
            gap: 20px; /* Espaço entre os campos na mesma linha */
            margin-bottom: 15px; /* Espaço entre as linhas de campos */
        }

        .form-row .form-group {
            flex: 1; /* Faz os form-groups crescerem para preencher o espaço */
            min-width: 250px; /* Largura mínima para cada campo */
            margin-bottom: 0; /* Remove a margem inferior padrão para gerenciar com gap na linha */
        }
        /* Ajuste específico para campos únicos que ocupam a linha inteira */
        .form-row.full-width .form-group {
            flex-basis: 100%; /* Ocupa 100% da largura da linha */
            min-width: unset; /* Remove min-width */
        }
        /* Estilo para inputs e selects dentro do form-group */
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
        }
        /* Estilo para textarea (descrição) */
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
            resize: vertical; /* Permite redimensionar verticalmente */
            min-height: 60px; /* Altura mínima para a descrição */
        }
        .form-group input[type="checkbox"] {
            margin-right: 8px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 25px;
        }
        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            flex-grow: 1;
            min-width: 150px;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .mensagem-sucesso {
            color: green;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .mensagem-erro {
            color: red;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        /* Media Queries para telas menores (celulares) */
        @media (max-width: 768px) {
            .form-row .form-group {
                min-width: 100%; /* Em telas menores, cada campo ocupa uma linha inteira */
            }
            .container-cadastro {
                padding: 15px;
                margin: 10px auto;
            }
        }
    </style>
</head>
<body>
    <div class="container-cadastro">
        <h2>Cadastro de Novo Produto</h2>
        <?php echo $mensagem; ?>
        <form action="cadastro_produto.php" method="POST">

            <div class="form-row full-width">
                <div class="form-group">
                    <label for="nome_produto">Nome do Produto: *</label>
                    <input type="text" id="nome_produto" name="nome_produto" required value="<?php echo htmlspecialchars($_POST['nome_produto'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="codigo_barras">Código de Barras (EAN):</label>
                    <input type="text" id="codigo_barras" name="codigo_barras" value="<?php echo htmlspecialchars($_POST['codigo_barras'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="preco_venda">Preço de Venda: *</label>
                    <input type="number" id="preco_venda" name="preco_venda" step="0.01" min="0.01" required value="<?php echo htmlspecialchars($_POST['preco_venda'] ?? '0.00'); ?>">
                </div>
            </div>

            <div class="form-row">
                 <div class="form-group">
                    <label for="custo_compra">Custo de Compra:</label>
                    <input type="number" id="custo_compra" name="custo_compra" step="0.01" min="0" value="<?php echo htmlspecialchars($_POST['custo_compra'] ?? '0.00'); ?>">
                </div>

                <div class="form-group">
                    <label for="estoque_atual">Estoque Atual: *</label>
                    <input type="number" id="estoque_atual" name="estoque_atual" min="0" required value="<?php echo htmlspecialchars($_POST['estoque_atual'] ?? '0'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="unidade_medida">Unidade de Medida:</label>
                    <select id="unidade_medida" name="unidade_medida">
                        <option value="Unidade" <?php echo (($_POST['unidade_medida'] ?? '') == 'Unidade') ? 'selected' : ''; ?>>Unidade</option>
                        <option value="Kg" <?php echo (($_POST['unidade_medida'] ?? '') == 'Kg') ? 'selected' : ''; ?>>Kg</option>
                        <option value="Litro" <?php echo (($_POST['unidade_medida'] ?? '') == 'Litro') ? 'selected' : ''; ?>>Litro</option>
                        <option value="Metro" <?php echo (($_POST['unidade_medida'] ?? '') == 'Metro') ? 'selected' : ''; ?>>Metro</option>
                        <option value="Caixa" <?php echo (($_POST['unidade_medida'] ?? '') == 'Caixa') ? 'selected' : ''; ?>>Caixa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoria:</label>
                    <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($_POST['categoria'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row full-width" style="margin-top: 5px;">
                <div class="form-group" style="flex-basis: auto; flex-grow: 0;">
                    <input type="checkbox" id="ativo" name="ativo" value="1" <?php echo (!isset($_POST['ativo']) || $_POST['ativo'] == '1') ? 'checked' : ''; ?>>
                    <label for="ativo" style="display: inline-block;">Produto Ativo</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Salvar Produto</button>
                <button type="button" class="btn-cancel" onclick="window.location.href='menu.php'">Voltar ao Menu</button>
            </div>
        </form>
    </div>
</body>
</html>