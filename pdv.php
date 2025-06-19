<?php
// Inclui o arquivo de verificação de login no topo.
// Isso garante que esta página só será acessível se o usuário estiver logado.
require_once 'check_login.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sistema PDV</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Estilos básicos para o layout do PDV. Você pode mover para styles.css se desejar. */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        display: flex; /* Para centralizar o conteúdo do PDV */
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        overflow: hidden; /* Para evitar scrollbar se popups estiverem fora da tela */
    }

    #containerPDV {
        display: flex; /* Garante que o PDV seja exibido. Será flex para usar flexbox interno. */
        width: 90%; /* Ajuste a largura conforme necessário */
        max-width: 1200px; /* Largura máxima para o layout do PDV */
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
        background-color: #fff;
        height: 90vh; /* Ajuste a altura conforme necessário */
    }

    .container-pdv {
        display: flex;
        height: 100%;
        width: 100%; /* Garante que o container-pdv ocupe a largura total do containerPDV */
    }

    .painel-esquerdo, .painel-direito {
        padding: 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }

    .painel-esquerdo {
        flex: 2; /* Ocupa 2/3 do espaço */
        border-right: 1px solid #eee;
        background-color: #f9f9f9;
    }

    .painel-direito {
        flex: 1; /* Ocupa 1/3 do espaço */
        background-color: #fff;
    }

    h2 {
        color: #333;
        margin-bottom: 20px;
        text-align: center;
    }

    .campo-grupo {
        margin-bottom: 15px;
    }

    .campo-grupo label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }

    .campo-grupo input[type="text"] {
        width: calc(100% - 22px); /* Considerando padding e border */
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .campo-display {
        background-color: #e9e9e9;
        padding: 10px;
        border-radius: 4px;
        font-size: 16px;
        color: #333;
        min-height: 20px; /* Para manter o espaçamento */
    }

    .formas-pagamento {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .formas-pagamento button {
        flex: 1 1 calc(50% - 10px); /* Dois botões por linha */
        padding: 12px;
        background-color: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .formas-pagamento button:hover {
        background-color: #218838;
    }

    #caixaProdutos {
        border: 1px solid #ddd;
        min-height: 250px;
        max-height: 400px; /* Altura máxima para rolagem */
        overflow-y: auto;
        padding: 10px;
        margin-bottom: 15px;
        background-color: #fdfdfd;
        flex-grow: 1; /* Permite que a caixa de produtos cresça */
    }

    .produto-item-caixa {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #eee;
        font-size: 0.9em;
    }

    .produto-item-caixa:last-child {
        border-bottom: none;
    }

    .info-produto {
        color: #444;
    }

    .preco-total-item {
        font-weight: bold;
        color: #007bff;
    }

    #totalVendaContainer {
        border-top: 2px solid #007bff;
        padding-top: 10px;
        margin-top: auto; /* Empurra para o final do painel-direito */
        font-size: 1.2em;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .menu-pdv {
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .menu-pdv button {
        padding: 12px;
        background-color: #6c757d;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 1.1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .menu-pdv button:hover {
        background-color: #5a6268;
    }

    /* Popups */
    .popup {
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 2000;
    }

    .popup.hidden {
        display: none;
    }

    .popup-content {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        text-align: center;
        width: 350px;
    }

    .popup-content h3 {
        margin-bottom: 20px;
        color: #333;
    }

    .popup-content label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }

    .popup-content input[type="text"] {
        width: calc(100% - 22px);
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        text-align: right; /* Para valores monetários */
    }

    .botoes-popup {
        display: flex;
        justify-content: space-around;
        margin-top: 20px;
        gap: 10px;
    }

    .botoes-popup button {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 5px;
        font-size: 1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .botoes-popup button:first-child { /* Cancelar/Fechar */
        background-color: #dc3545;
        color: white;
    }

    .botoes-popup button:first-child:hover {
        background-color: #c82333;
    }

    .botoes-popup button:last-child { /* Confirmar */
        background-color: #007bff;
        color: white;
    }

    .botoes-popup button:last-child:hover {
        background-color: #0056b3;
    }

    /* Estilos para itens cancelados */
    .item-totalmente-cancelado {
        opacity: 0.5; /* Torna o item semi-transparente */
        text-decoration: line-through; /* Risca o texto */
    }

    .qtd-info-parcial-cancelada, .qtd-info {
        font-size: 0.8em;
        color: #888;
    }
  </style>
</head>
<body>
  <div id="containerPDV">
    <div class="container-pdv">
      <div class="painel-esquerdo">
        <h2>Informações do Último Produto</h2>
        <div class="campo-grupo">
          <label for="codigoProdutoInput">Código (ou Código*Qtd / 'R' para cancelar):</label>
          <input type="text" id="codigoProdutoInput" placeholder="Ex: 2x001 ou R + Enter" autofocus/>
        </div>
        <div class="campo-grupo">
          <label>Descrição do Produto:</label>
          <div id="descricaoProdutoDiv" class="campo-display"></div>
        </div>
        <div class="campo-grupo">
          <label>Valor Unitário (R$):</label>
          <div id="valorUnitarioProdutoDiv" class="campo-display"></div>
        </div>
        <div class="campo-grupo">
          <label>Valor Total do Item Adicionado (R$):</label>
          <div id="valorTotalItemDiv" class="campo-display"></div>
        </div>
        <div class="campo-grupo">
          <label>Forma de Pagamento:</label>
          <div class="formas-pagamento">
            <button id="btnF1">F1 - Dinheiro</button>
            <button id="btnF2">F2 - Pix</button>
            <button id="btnF3">F3 - Cartão</button>
            <button id="btnF4">F4 - Conta</button>
          </div>
        </div>
      </div>

      <div class="painel-direito">
        <h2>Produtos na Venda</h2>
        <div id="caixaProdutos"></div>
        <div id="totalVendaContainer">
          <strong>Quantidade de Itens: <span id="totalItensSpan">0</span></strong>
          <strong>Total da Venda: R$ <span id="totalVendaSpan">0.00</span></strong>
        </div>
        <div class="menu-pdv">
            <button onclick="window.location.href='consultar_vendas.php'">Consultar Vendas</button>
            <button onclick="fazerLogout()">Sair</button> </div>
      </div>
    </div>
  </div>

  <div id="popupDinheiro" class="popup hidden">
    <div class="popup-content">
      <h3>Pagamento em Dinheiro</h3>
      <label>Valor Total da Venda:</label>
      <input type="text" id="valorTotalPopup" readonly />
      <label>Dinheiro:</label>
		<input type="text" id="valorDinheiroInput" />
      <label>Troco:</label>
      <input type="text" id="valorTrocoPopup" readonly />
      <div class="botoes-popup">
        <button onclick="fecharPopupDinheiro()">Cancelar</button>
        <button onclick="confirmarPagamentoDinheiro()">Confirmar</button>
      </div>
    </div>
  </div>

  <div id="popupPix" class="popup hidden">
  <div class="popup-content">
    <h3>Pagamento via Pix</h3>
    <label>Valor Total da Venda:</label>
    <input type="text" id="valorPixPopup" readonly />
    <label>QR Code Pix:</label>
    <img id="qrcodePixImg" src="" alt="QR Code Pix" style="width: 250px; height: 250px; margin: auto;" />
    <div class="botoes-popup">
      <button onclick="fecharPopupPix()">Fechar</button>
    </div>
  </div>
</div>

  <script src="scripts.js"></script>
</body>
</html>