<?php
require_once 'check_login.php'; // Inclui a verificação de login no topo
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Vendas Finalizadas</title>
    <link rel="stylesheet" href="styles.css"> <style>
        /* Estilos específicos para esta tela, se necessário (pode estar no styles.css) */
        #vendasTable {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        #vendasTable th, #vendasTable td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        #vendasTable th {
            background-color: #f2f2f2;
        }
        .container-consulta {
            padding: 20px;
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .container-consulta h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .btn-voltar {
            display: block;
            width: 150px; /* Aumentado para melhor visual */
            padding: 10px;
            margin: 20px auto 0;
            background-color: #007bff;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-voltar:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container-consulta">
        <h2>Vendas Finalizadas</h2>
        <table id="vendasTable">
            <thead>
                <tr>
                    <th>ID da Venda</th>
                    <th>Data/Hora</th>
                    <th>Valor Total</th>
                    <th>Forma de Pagamento</th>
                    </tr>
            </thead>
            <tbody id="vendasTableBody">
                </tbody>
        </table>
        <button class="btn-voltar" onclick="window.location.href='pdv.php'">Voltar ao PDV</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            carregarVendas();
        });

        function carregarVendas() {
            fetch('buscar_vendas_finalizadas.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na rede: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(vendas => {
                    const tbody = document.getElementById('vendasTableBody');
                    tbody.innerHTML = ''; // Limpa a tabela antes de preencher
                    if (vendas.erro) {
                        const row = tbody.insertRow();
                        const cell = row.insertCell();
                        cell.colSpan = 4; // Ajuste para o número de colunas
                        cell.textContent = vendas.erro;
                        cell.style.textAlign = 'center';
                        return;
                    }
                    if (vendas.length === 0) {
                        const row = tbody.insertRow();
                        const cell = row.insertCell();
                        cell.colSpan = 4; // Ajuste para o número de colunas
                        cell.textContent = 'Nenhuma venda finalizada encontrada.';
                        cell.style.textAlign = 'center';
                        return;
                    }
                    vendas.forEach(venda => {
                        const row = tbody.insertRow();
                        row.insertCell().textContent = venda.id_venda;
                        row.insertCell().textContent = venda.data_hora;
                        row.insertCell().textContent = parseFloat(venda.valor_total).toFixed(2);
                        row.insertCell().textContent = venda.forma_pagamento;
                        // Adicione mais células para outras informações, se houver
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar vendas:', error);
                    const tbody = document.getElementById('vendasTableBody');
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Erro ao carregar vendas: ${error.message}</td></tr>`;
                });
        }
    </script>
</body>
</html>