<?php
require_once 'check_login.php'; // Inclui a verificação de login no topo
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Vendas Finalizadas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
        /* Estilos de Paginação */
        .btn-paginacao {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
        }
        .btn-paginacao:hover {
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
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Valor Total</th>
                    <th>Forma de Pagamento</th>
                </tr>
            </thead>
            <tbody id="vendasTableBody">
                </tbody>
        </table>
        <div id="paginationControls" style="text-align: center; margin-top: 20px;">
            <button id="prevPageBtn" class="btn-paginacao" style="display: none;">Anterior</button>
            <span id="pageInfo" style="margin: 0 10px;"></span>
            <button id="nextPageBtn" class="btn-paginacao" style="display: none;">Próxima</button>
        </div>
        <button class="btn-voltar" onclick="window.location.href='pdv.php'">Voltar ao PDV</button>
    </div>

    <script>
        let paginaAtual = 1; // Variável global para a página atual
        const limitePorPagina = 15; // Definido aqui e usado no backend

        document.addEventListener('DOMContentLoaded', function() {
            carregarVendas(paginaAtual); // Carrega a primeira página ao iniciar

            document.getElementById('prevPageBtn').addEventListener('click', function() {
                if (paginaAtual > 1) {
                    paginaAtual--;
                    carregarVendas(paginaAtual);
                }
            });

            document.getElementById('nextPageBtn').addEventListener('click', function() {
                paginaAtual++;
                carregarVendas(paginaAtual);
            });
        });

        function carregarVendas(pagina) {
            fetch(`buscar_vendas_finalizadas.php?page=${pagina}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na rede: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(dados => {
                    const tbody = document.getElementById('vendasTableBody');
                    tbody.innerHTML = ''; // Limpa a tabela antes de preencher

                    if (dados.erro) {
                        const row = tbody.insertRow();
                        const cell = row.insertCell();
                        cell.colSpan = 4;
                        cell.textContent = dados.erro;
                        cell.style.textAlign = 'center';
                        document.getElementById('paginationControls').style.display = 'none';
                        return;
                    }

                    const vendas = dados.vendas;
                    const totalVendas = dados.totalVendas;
                    const paginaAtualRecebida = dados.paginaAtual;
                    const totalPaginas = Math.ceil(totalVendas / limitePorPagina);

                    if (vendas.length === 0 && paginaAtualRecebida === 1) {
                        const row = tbody.insertRow();
                        const cell = row.insertCell();
                        cell.colSpan = 4;
                        cell.textContent = 'Nenhuma venda finalizada encontrada.';
                        cell.style.textAlign = 'center';
                        document.getElementById('paginationControls').style.display = 'none';
                        return;
                    } else if (vendas.length === 0 && paginaAtualRecebida > 1) {
                        // Se não há vendas para a página atual mas não é a primeira página, volta para a anterior
                        paginaAtual--;
                        carregarVendas(paginaAtual);
                        return;
                    }

                    vendas.forEach(venda => {
                        const row = tbody.insertRow();

                        // Formatar Data (dd/mm/aa) e Hora (HH:MM)
                        let dataHora = new Date(venda.data_venda);
                        let dataFormatada = `${String(dataHora.getDate()).padStart(2, '0')}/${String(dataHora.getMonth() + 1).padStart(2, '0')}/${String(dataHora.getFullYear()).slice(2)}`;
                        let horaFormatada = `${String(dataHora.getHours()).padStart(2, '0')}:${String(dataHora.getMinutes()).padStart(2, '0')}`;

                        // Se a hora for 24:00, ajusta para 00:00 do dia seguinte.
                        // Se for necessário exibir 24:00, remova ou ajuste esta lógica.
                        // Geralmente, 24:00 é representado como 00:00 do dia seguinte.
                        if (dataHora.getHours() === 0 && dataHora.getMinutes() === 0 && dataHora.getSeconds() === 0 && horaFormatada === '00:00') {
                             // Isso evita que 00:00 seja confundido com "24:00" do dia anterior,
                             // se a data_venda for a meia-noite exata.
                        } else if (dataHora.getHours() === 24) { // Isso é improvável, mas para garantir
                            horaFormatada = '00:00';
                            // dataHora.setDate(dataHora.getDate() + 1); // Descomente se 24:00 significa próximo dia
                            // dataFormatada = `${String(dataHora.getDate()).padStart(2, '0')}/${String(dataHora.getMonth() + 1).padStart(2, '0')}/${String(dataHora.getFullYear()).slice(2)}`;
                        }


                        row.insertCell().textContent = dataFormatada;
                        row.insertCell().textContent = horaFormatada;

                        const valorTotal = parseFloat(venda.total_venda);
                        row.insertCell().textContent = isNaN(valorTotal) ? '0.00' : valorTotal.toFixed(2);
                        row.insertCell().textContent = venda.forma_pagamento;
                    });

                    // Atualizar controles de paginação
                    document.getElementById('pageInfo').textContent = `Página ${paginaAtualRecebida} de ${totalPaginas}`;
                    document.getElementById('prevPageBtn').style.display = (paginaAtualRecebida > 1) ? 'inline-block' : 'none';
                    document.getElementById('nextPageBtn').style.display = (paginaAtualRecebida < totalPaginas) ? 'inline-block' : 'none';
                    document.getElementById('paginationControls').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erro ao carregar vendas:', error);
                    const tbody = document.getElementById('vendasTableBody');
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">Erro ao carregar vendas: ${error.message}</td></tr>`;
                    document.getElementById('paginationControls').style.display = 'none';
                });
        }
    </script>
</body>
</html>