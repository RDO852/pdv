// --- Lógica de Login ---
function fazerLogin() {
    const user = document.getElementById('usuario').value.trim();
    const pass = document.getElementById('senha').value.trim();
    if (user === 'admin' && pass === '123') { // Credenciais fixas para login
        document.getElementById('telaLogin').style.display = 'none';
        document.getElementById('containerPDV').style.display = 'flex'; // Torna o PDV visível
        document.getElementById('codigoProdutoInput').focus();
    } else {
        alert('Usuário ou senha incorretos.');
    }
}

let carrinho = []; // Array para armazenar os produtos da venda atual
let emModoDeCancelamento = false; // Flag para controlar o modo de cancelamento
let enterPressCount = 0; // Para controlar as etapas de Enter no pagamento (Dinheiro)

// Referências aos elementos do DOM
const codigoProdutoInput = document.getElementById('codigoProdutoInput');
const descricaoProdutoDiv = document.getElementById('descricaoProdutoDiv');
const valorUnitarioProdutoDiv = document.getElementById('valorUnitarioProdutoDiv');
const valorTotalItemDiv = document.getElementById('valorTotalItemDiv');
const caixaProdutosDiv = document.getElementById('caixaProdutos');
const totalVendaSpan = document.getElementById('totalVendaSpan');
const totalItensSpan = document.getElementById('totalItensSpan');

// Referências para o popup de dinheiro
const valorDinheiroInput = document.getElementById('valorDinheiroInput');
const valorTotalPopup = document.getElementById('valorTotalPopup');
const valorTrocoPopup = document.getElementById('valorTrocoPopup');

// Constantes para o Pix
const chavePix = '+5586999416417';        // sua chave real (telefone, e-mail ou aleatória)
const nomeRecebedor = 'Maria Eva Leal';   // até 25 caracteres
const cidade = 'Teresina';                // até 15 caracteres


// Garante que o input de login ou o PDV seja focado e os listeners de login sejam configurados
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded disparado.');

    const telaLogin = document.getElementById('telaLogin');
    const usuarioInput = document.getElementById('usuario');
    const senhaInput = document.getElementById('senha');

    // Mova os event listeners de login para dentro do DOMContentLoaded
    if (usuarioInput) {
        usuarioInput.addEventListener('keypress', function(event) {
            console.log('Evento keypress detectado no usuarioInput. Tecla:', event.key);
            if (event.key === 'Enter') {
                event.preventDefault();
                if (senhaInput) {
                    senhaInput.style.display = 'block'; // Garante que o campo de senha esteja visível
                    senhaInput.focus();
                }
            }
        });
    }

    if (senhaInput) {
        senhaInput.addEventListener('keypress', function(event) {
            console.log('Evento keypress detectado no senhaInput. Tecla:', event.key);
            if (event.key === 'Enter') {
                event.preventDefault();
                fazerLogin();
            }
        });
    }

    if (telaLogin && telaLogin.style.display !== 'none') {
        if (usuarioInput) {
            usuarioInput.focus();
            console.log('Foco no campo de usuário (tela de login visível).');
        }
    } else if (codigoProdutoInput) { // Caso contrário, foca o input do PDV (se o PDV já estiver visível por algum motivo)
        codigoProdutoInput.focus();
        console.log('Foco no campo de código do produto (PDV visível).');
    } else {
        console.warn('Nem tela de login nem input de produto encontrados ou visíveis para focar.');
    }

    // Event listener para o input de dinheiro do popup
    if (valorDinheiroInput) {
        // Correção 1: Formatação automática da vírgula/dinheiro
        valorDinheiroInput.addEventListener('input', function(event) {
    let value = event.target.value;

    // Remove tudo que não for dígito
    value = value.replace(/[^0-9]/g, '');

    // Formata o valor como moeda (BRL) sem o símbolo "R$"
    let formattedValue = (parseInt(value) / 100).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        currencyDisplay: 'code' // Exibe o código da moeda
    });

    // Remove o código da moeda (BRL)
    formattedValue = formattedValue.replace('BRL', '').trim();

    event.target.value = formattedValue;
});

valorDinheiroInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        if (enterPressCount === 0) {
            calcularTroco();
            enterPressCount++;
        } else if (enterPressCount === 1) {
            confirmarPagamentoDinheiro();
            enterPressCount = 0; // Reseta para a próxima venda
        }
    }
});

valorDinheiroInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        if (enterPressCount === 0) {
            calcularTroco();
            enterPressCount++;
        } else if (enterPressCount === 1) {
            confirmarPagamentoDinheiro();
            enterPressCount = 0; // Reseta para a próxima venda
        }
    }
});

        valorDinheiroInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                console.log('Enter pressionado no valorDinheiroInput. enterPressCount:', enterPressCount); // Depuração
                enterPressCount++;
                if (enterPressCount === 1) { // Primeiro Enter: calcula troco
                    console.log('Primeiro Enter: Chamando calcularTroco().'); // Depuração
                    calcularTroco();
                } else if (enterPressCount === 2) { // Segundo Enter: confirma pagamento
                    console.log('Segundo Enter: Chamando confirmarPagamentoDinheiro().'); // Depuração
                    confirmarPagamentoDinheiro();
                }
            }
        });
    }

}); // Fim de DOMContentLoaded

// Event listener principal para o campo de código do produto (Enter para processar)
if (codigoProdutoInput) {
    codigoProdutoInput.addEventListener('keypress', function(event) {
        console.log('Evento keypress detectado no codigoProdutoInput. Tecla:', event.key);
        if (event.key === 'Enter') {
            event.preventDefault(); // Impede o envio padrão do formulário
            console.log('Tecla Enter pressionada. Chamando processarEntradaPrincipal().');
            processarEntradaPrincipal();
        }
    });
}

// Event listener global para teclas de função (F1, F2, F3)
document.addEventListener('keydown', function(event) {
    // console.log('Tecla pressionada globalmente:', event.key, 'Code:', event.code); // Para depuração

    // Certifica-se de que o PDV está visível e ativo (não na tela de login)
    const containerPDV = document.getElementById('containerPDV');
    if (!containerPDV || containerPDV.style.display === 'none') {
        return; // Não executa se o PDV não estiver visível
    }

    if (event.key === 'F1') {
        event.preventDefault(); // Impede o comportamento padrão da tecla F1 (ajuda do navegador)
        console.log('F1 pressionado. Abrindo popup Dinheiro.');
        abrirPopupDinheiro();
    } else if (event.key === 'F2') {
        event.preventDefault(); // Impede o comportamento padrão da tecla F2 (renomear, etc.)
        console.log('F2 pressionado. Abrindo popup Pix.');
        abrirPopupPix();
    }
    // Adicione mais `else if` para outras teclas de função (F3, F4, etc.) se tiver
    // else if (event.key === 'F3') {
    //     event.preventDefault();
    //     abrirPopupCartao(); // Se você tiver uma função para cartão
    // }
});


function processarEntradaPrincipal() {
    console.log('Função processarEntradaPrincipal() iniciada.');
    const entrada = codigoProdutoInput.value.trim().toLowerCase();
    console.log('Entrada do usuário:', entrada);

    if (entrada === 'r') {
        console.log('Modo de cancelamento solicitado.');
        iniciarProcessoDeCancelamento();
        codigoProdutoInput.value = '';
        return;
    }

    if (emModoDeCancelamento) {
        console.log('Em modo de cancelamento, processando:', entrada);
        cancelarProduto(entrada);
        return;
    }

    let codigoProd;
    let quantidadeAdicionada = 1;

    if (entrada.includes('x')) {
        const [qtdStr, cod] = entrada.split('x');
        const qtdNum = parseInt(qtdStr.trim());
        if (!isNaN(qtdNum) && qtdNum > 0) {
            quantidadeAdicionada = qtdNum;
            codigoProd = cod.trim();
        } else {
            alert('Quantidade inválida para adição de produto. Use formato "quantidadeXcodigo" (ex: 2x001).');
            codigoProdutoInput.value = '';
            console.warn('Quantidade inválida detectada.');
            return;
        }
    } else {
        codigoProd = entrada;
    }

    console.log(`Buscando produto: Código=${codigoProd}, Quantidade=${quantidadeAdicionada}`);
    fetch(`buscar_produto.php?codigo=${codigoProd}`)
    .then(res => {
        console.log('Resposta do fetch recebida. Status:', res.status);
        if (!res.ok) {
            return res.json().then(err => {
                console.error('Erro na resposta HTTP:', err);
                throw new Error(err.erro || `Erro HTTP: ${res.status} - ${res.statusText}`);
            });
        }
        return res.json();
    })
    .then(produto => {
        console.log('JSON do produto recebido:', produto);
        if (produto.erro) {
            alert(produto.erro);
            console.warn('Erro do backend:', produto.erro);
            descricaoProdutoDiv.textContent = '';
            valorUnitarioProdutoDiv.textContent = '';
            valorTotalItemDiv.textContent = '';
            return;
        }

        descricaoProdutoDiv.textContent = produto.descricao;
        valorUnitarioProdutoDiv.textContent = parseFloat(produto.valor_unitario).toFixed(2).replace('.', ',');
        const totalItem = parseFloat(produto.valor_unitario) * quantidadeAdicionada;
        valorTotalItemDiv.textContent = totalItem.toFixed(2).replace('.', ',');

        adicionarOuAtualizarProdutoNoCarrinho(produto, quantidadeAdicionada);
        renderizarCarrinho();
        codigoProdutoInput.value = '';
        codigoProdutoInput.focus();
        console.log('Produto adicionado/atualizado e renderizado.');
    })
    .catch(err => {
        console.error('Erro ao buscar produto no frontend:', err);
        alert('Erro na comunicação com o servidor ao buscar produto. Detalhes: ' + err.message);
        descricaoProdutoDiv.textContent = '';
        valorUnitarioProdutoDiv.textContent = '';
        valorTotalItemDiv.textContent = '';
        codigoProdutoInput.value = '';
    });
}

function adicionarOuAtualizarProdutoNoCarrinho(produto, quantidadeAdicionada) {
    const produtoExistente = carrinho.find(item => item.codigo === produto.codigo);

    if (produtoExistente) {
        produtoExistente.quantidade += quantidadeAdicionada;
        if (produtoExistente.quantidadeCancelada > produtoExistente.quantidade) {
            produtoExistente.quantidadeCancelada = produtoExistente.quantidade;
        }
    } else {
        carrinho.push({
            codigo: produto.codigo,
            descricao: produto.descricao,
            valorUnitario: parseFloat(produto.valor_unitario),
            quantidade: quantidadeAdicionada,
            quantidadeCancelada: 0
        });
    }
}

function iniciarProcessoDeCancelamento() {
    emModoDeCancelamento = true;
    codigoProdutoInput.placeholder = 'Modo CANCELAMENTO: Digite o código (Ex: 1x001 ou 001)';
    descricaoProdutoDiv.textContent = '';
    valorUnitarioProdutoDiv.textContent = '';
    valorTotalItemDiv.textContent = '';
    alert('Modo de cancelamento ativado. Digite o código do produto (e opcionalmente a quantidade com "x") para cancelar.');
}

function cancelarProduto(entrada) {
    const entradaNormalizada = entrada.trim().toLowerCase();
    let quantidadeCancelar = 1;
    let codigoProduto;

    if (entradaNormalizada.includes('x')) {
        const [quantStr, cod] = entradaNormalizada.split('x');
        quantidadeCancelar = parseInt(quantStr.trim());
        codigoProduto = cod.trim();
        if (isNaN(quantidadeCancelar) || quantidadeCancelar <= 0) {
            alert('Quantidade inválida para cancelamento. Use formato "quantidadeXcodigo" (ex: 1x001).');
            emModoDeCancelamento = false;
            codigoProdutoInput.placeholder = 'Ex: 2x001 ou R + Enter';
            codigoProdutoInput.value = '';
            codigoProdutoInput.focus();
            return;
        }
    } else {
        codigoProduto = entradaNormalizada;
    }

    const produtoIndex = carrinho.findIndex(p => p.codigo === codigoProduto);

    if (produtoIndex !== -1) {
        const produto = carrinho[produtoIndex];
        const unidadesAtivas = produto.quantidade - produto.quantidadeCancelada;
        if (unidadesAtivas > 0) {
            const podeCancelar = Math.min(quantidadeCancelar, unidadesAtivas);
            produto.quantidadeCancelada += podeCancelar;
            renderizarCarrinho();
            alert(`Foram canceladas ${podeCancelar} unidade(s) do produto ${produto.descricao}.`);
        } else {
            alert(`O item "${produto.descricao}" já foi totalmente cancelado.`);
        }
    } else {
        alert('Produto não encontrado no carrinho para cancelamento.');
    }
    emModoDeCancelamento = false;
    codigoProdutoInput.placeholder = 'Ex: 2x001 ou R + Enter';
    codigoProdutoInput.value = '';
    codigoProdutoInput.focus();
}

function renderizarCarrinho() {
    caixaProdutosDiv.innerHTML = '';
    let subtotal = 0;
    let totalItens = 0;
    carrinho.forEach(item => {
        const ativas = item.quantidade - item.quantidadeCancelada;
        const total = item.valorUnitario * ativas;
        totalItens += ativas;
        let infoQtd = `${item.quantidade}x`;
        if (item.quantidadeCancelada > 0 && ativas > 0) {
            infoQtd += ` <span class="qtd-info-parcial-cancelada">(${item.quantidadeCancelada} cancelada(s))</span>`;
        } else if (ativas <= 0) {
            infoQtd += ` <span class="qtd-info">(Todas canceladas)</span>`;
        }
        const div = document.createElement('div');
        div.classList.add('produto-item-caixa');
        if (ativas <= 0) {
            div.classList.add('item-cancelado-vermelho');
        }
        div.innerHTML = `
            <span class="info-produto">${infoQtd} ${item.descricao} (R$ ${item.valorUnitario.toFixed(2).replace('.', ',')})</span>
            <span class="preco-total-item">R$ ${total.toFixed(2).replace('.', ',')}</span>
        `;
        caixaProdutosDiv.appendChild(div);
        subtotal += total;
    });
    totalVendaSpan.textContent = subtotal.toFixed(2).replace('.', ',');
    totalItensSpan.textContent = totalItens;
}

// --- Funções de Pagamento (Dinheiro) ---
function abrirPopupDinheiro() {
    const total = parseFloat(totalVendaSpan.textContent.replace(',', '.')) || 0;
    if (total <= 0) {
        alert('O valor da venda deve ser maior que zero para pagamento.');
        return;
    }
    valorTotalPopup.value = total.toFixed(2).replace('.', ',');
    valorDinheiroInput.value = ''; // Limpa o campo de dinheiro
    valorTrocoPopup.value = '0,00'; // Reseta o troco
    document.getElementById('popupDinheiro').classList.remove('hidden');
    valorDinheiroInput.focus(); // Foca o campo de dinheiro no popup
    enterPressCount = 0; // Reseta o contador de Enter para o pagamento
}

function fecharPopupDinheiro() {
    document.getElementById('popupDinheiro').classList.add('hidden');
    codigoProdutoInput.focus(); // Retorna o foco para o campo de código principal
}

function calcularTroco() {
    const totalVenda = parseFloat(valorTotalPopup.value.replace(',', '.'));
    // Use replace(',', '.') para converter o valor do input para um número float
    const valorPago = parseFloat(valorDinheiroInput.value.replace(',', '.'));

    console.log('Calculando Troco: Total Venda =', totalVenda, 'Valor Pago =', valorPago); // Depuração

    if (isNaN(valorPago) || valorPago < 0) {
        alert('Por favor, insira um valor de dinheiro válido.');
        valorDinheiroInput.value = '';
        valorDinheiroInput.focus();
        valorTrocoPopup.value = '0,00';
        enterPressCount = 0; // Reseta o contador se a entrada for inválida
        return;
    }

    const troco = valorPago - totalVenda;
    valorTrocoPopup.value = troco.toFixed(2).replace('.', ',');

    if (troco < 0) {
        alert('Valor insuficiente! Faltam R$ ' + (-troco).toFixed(2).replace('.', ','));
        // Não resetamos enterPressCount aqui para permitir que o usuário adicione mais dinheiro
        // e pressione Enter novamente para recalcular ou confirmar.
        // A próxima vez que ele pressionar Enter, ele chamará calcularTroco novamente (se enterPressCount for 1)
        // ou confirmarPagamentoDinheiro (se enterPressCount for 2 e o troco for > 0).
        // Se o usuário digitar um valor válido que ainda seja insuficiente, ele permanecerá em enterPressCount = 1
        // para tentar novamente calcular o troco.
        enterPressCount = 0; // Reset para permitir um novo cálculo ao próximo ENTER
        valorDinheiroInput.focus(); // Mantém o foco para que o usuário insira mais dinheiro
    } else {
        // Se o troco é positivo ou zero, o pagamento está ok.
        // O enterPressCount já está em 1, então o próximo ENTER irá para a confirmação (enterPressCount = 2).
        console.log('Troco calculado com sucesso ou valor exato. Pronto para confirmar a venda.'); // Depuração
    }
}

function confirmarPagamentoDinheiro() {
    const totalVenda = parseFloat(valorTotalPopup.value.replace(',', '.'));
    const valorPago = parseFloat(valorDinheiroInput.value.replace(',', '.'));
    const troco = valorPago - totalVenda;

    console.log('Confirmando Pagamento: Total Venda =', totalVenda, 'Valor Pago =', valorPago, 'Troco =', troco); // Depuração

    if (troco >= 0) {
        finalizarVenda('Dinheiro', { valorPago: valorPago, troco: troco });
    } else {
        alert('Não é possível finalizar a venda: valor pago é insuficiente. Por favor, insira um valor maior ou igual ao total da venda.');
        valorDinheiroInput.focus(); // Mantém o foco para que o usuário adicione mais dinheiro
        enterPressCount = 0; // Reset para que ele tente calcular o troco novamente ou digite novo valor.
    }
}


// --- Funções de Pagamento (Pix) ---
// Função para gerar o payload do Pix Copia e Cola (BR Code)
function gerarPayloadPix(chave, valor, nome, cidade) {
    const merchantCategoryCode = '0000'; // Categoria padrão (Retail Trade)
    const transactionCurrency = '986'; // BRL (Real Brasileiro)
    const countryCode = 'BR';
    const merchantName = nome.substring(0, 25);
    const merchantCity = cidade.substring(0, 15);

    let payload = `00140112BR.GOV.BR.BCB.PIX01${chave.length.toString().padStart(2, '0')}${chave}`;
    payload += `5204${merchantCategoryCode}`;
    payload += `5303${transactionCurrency}`;
    payload += `54${valor.toFixed(2).length.toString().padStart(2, '0')}${valor.toFixed(2)}`;
    payload += `5802${countryCode}`;
    payload += `59${merchantName.length.toString().padStart(2, '0')}${merchantName}`;
    payload += `60${merchantCity.length.toString().padStart(2, '0')}${merchantCity}`;
    payload += `62070503***`; // Identificador de transação (opcional, pode ser fixo ou gerado)
    payload += `6304`; // CRC16 - placeholder para o cálculo

    const crc = crc16(payload + '0000'); // Calcula o CRC com placeholder 0000
    return payload + crc.toString(16).toUpperCase().padStart(4, '0');
}

// Função CRC16 (retirada de exemplos de Pix)
function crc16(data) {
    let crc = 0xFFFF;
    for (let i = 0; i < data.length; i++) {
        crc ^= data.charCodeAt(i) << 8;
        for (let j = 0; j < 8; j++) {
            if ((crc & 0x8000) !== 0) {
                crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
            } else {
                crc = (crc << 1) & 0xFFFF;
            }
        }
    }
    return crc;
}

function fecharPopupPix() {
    document.getElementById('popupPix').classList.add('hidden');
    codigoProdutoInput.focus();
}

function abrirPopupPix() {
    const total = parseFloat(document.getElementById('totalVendaSpan').textContent.replace(',', '.')) || 0;

    if (total <= 0) {
        alert('O valor da venda deve ser maior que zero para Pix.');
        return;
    }

    const payloadPix = gerarPayloadPix(chavePix, total, nomeRecebedor, cidade);
    const qrCodeURL = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(payloadPix)}&size=250x250`;

    document.getElementById('valorPixPopup').value = total.toFixed(2).replace('.', ',');
    document.getElementById('qrcodePixImg').src = qrCodeURL;
    document.getElementById('popupPix').classList.remove('hidden');

    document.getElementById('qrcodePixImg').onclick = function() {
        if (confirm('Pagamento Pix recebido e confirmado?')) {
            finalizarVenda('Pix');
            this.onclick = null; // Remove o evento após a confirmação para evitar múltiplos cliques
        }
    };
}


// --- Funções de Sair ---
function fazerLogout() {
    // Redireciona para um script PHP que destrói a sessão e redireciona para a página de login
    window.location.href = 'logout.php';
}

// TODO: Adicionar lógica para F3 (Cartão) se necessário

// Função genérica para finalizar a venda (chamada por dinheiro, pix, etc.)
function finalizarVenda(formaPagamento, detalhesPagamento = {}) {
    if (carrinho.length === 0 || parseFloat(totalVendaSpan.textContent.replace(',', '.')) <= 0) {
        alert('Não há produtos no carrinho ou o total da venda é zero para finalizar.');
        return;
    }

    const totalVenda = parseFloat(totalVendaSpan.textContent.replace(',', '.'));
    const itensVendidos = carrinho.map(item => ({
        codigo: item.codigo,
        descricao: item.descricao,
        quantidade: item.quantidade - item.quantidadeCancelada, // Vende apenas a quantidade não cancelada
        valorUnitario: item.valorUnitario,
        totalItem: (item.quantidade - item.quantidadeCancelada) * item.valorUnitario
    })).filter(item => item.quantidade > 0); // Filtra itens com quantidade > 0

    // Cria um objeto com os dados da venda para enviar ao backend
    const dadosVenda = {
        dataHora: new Date().toISOString(),
        totalVenda: totalVenda,
        formaPagamento: formaPagamento,
        detalhesPagamento: detalhesPagamento,
        itens: itensVendidos
    };

    fetch('finalizar_venda.php', { // Você precisará criar este arquivo PHP
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dadosVenda)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.erro || `Erro HTTP: ${response.status} - ${response.statusText}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.sucesso) {
            let mensagemSucesso = `Venda Finalizada com Sucesso! [Venda ID: ${data.vendaId}]\n\n`;
            if (formaPagamento === 'Dinheiro' && detalhesPagamento.troco !== undefined) {
                mensagemSucesso += `Valor Pago: R$ ${detalhesPagamento.valorPago.toFixed(2).replace('.', ',')}\n`;
                mensagemSucesso += `Troco: R$ ${detalhesPagamento.troco.toFixed(2).replace('.', ',')}\n`;
            }
            mensagemSucesso += `Valor Total: R$ ${totalVenda.toFixed(2).replace('.', ',')}\n`;
            mensagemSucesso += `Forma de Pagamento: ${formaPagamento}`;

            alert(mensagemSucesso);

            fecharPopupDinheiro(); // Fecha o popup de dinheiro, se aberto
            fecharPopupPix();      // Fecha o popup de Pix, se aberto

            carrinho = []; // Limpa o carrinho
            renderizarCarrinho(); // Atualiza a exibição do carrinho para vazio

            // Limpa os campos de informação do último produto
            descricaoProdutoDiv.textContent = '';
            valorUnitarioProdutoDiv.textContent = '';
            valorTotalItemDiv.textContent = '';

            codigoProdutoInput.value = ''; // Limpa o campo de código principal
            codigoProdutoInput.focus(); // Retorna o foco ao campo de código

            enterPressCount = 0; // Reseta o contador para o pagamento
        } else {
            alert('Erro ao finalizar venda: ' + (data.erro || 'Erro desconhecido.'));
        }
    })
    // Dentro da sua função finalizarVenda ou onde você faz o fetch:
    .catch(error => {
        console.error('Erro na comunicação ao finalizar venda:', error);
        // O erro pode ser de parsing JSON se o servidor ainda retornar HTML.
        // Ou pode ser um erro de rede.
        if (error instanceof SyntaxError && error.message.includes('JSON')) {
             alert('Resposta inesperada do servidor. Possível problema de autenticação ou configuração.');
        } else {
             alert('Erro na comunicação com o servidor ao finalizar venda. Verifique sua conexão ou tente novamente.');
        }
    })};