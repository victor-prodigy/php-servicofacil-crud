document.addEventListener('DOMContentLoaded', function() {
    carregarDetalhesServico();
    configurarFormasPagamento();
    configurarMascarasInput();
});

// Carregar detalhes do serviço
async function carregarDetalhesServico() {
    const urlParams = new URLSearchParams(window.location.search);
    const servicoId = urlParams.get('id');

    if (!servicoId) {
        alert('Serviço não especificado');
        window.location.href = 'cliente-dashboard.html';
        return;
    }

    try {
        const response = await fetch(`../php/servico/detalhe.php?id=${servicoId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const servico = data.servico;

        // Atualizar informações na página
        document.getElementById('servicoTitulo').textContent = servico.titulo;
        document.getElementById('servicoDescricao').textContent = servico.descricao;
        document.getElementById('prestadorNome').textContent = servico.prestador_nome;
        document.getElementById('valorTotal').textContent = formatarMoeda(servico.valor_total);

        // Verificar se o serviço está realmente concluído
        if (servico.status !== 'concluido') {
            alert('Este serviço ainda não está concluído e não pode ser pago.');
            window.location.href = 'cliente-dashboard.html';
        }

        // Se for pagamento PIX, gerar QR Code
        if (servico.chave_pix) {
            gerarQRCodePix(servico.chave_pix);
        }

    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        alert('Erro ao carregar detalhes do serviço. Por favor, tente novamente.');
        window.location.href = 'cliente-dashboard.html';
    }
}

// Configurar formas de pagamento
function configurarFormasPagamento() {
    const radios = document.querySelectorAll('input[name="formaPagamento"]');
    const forms = {
        cartao: document.getElementById('formCartao'),
        pix: document.getElementById('formPix'),
        transferencia: document.getElementById('formTransferencia')
    };

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            // Esconder todos os formulários
            Object.values(forms).forEach(form => form.style.display = 'none');
            
            // Mostrar o formulário selecionado
            forms[radio.value].style.display = 'block';
        });
    });
}

// Configurar máscaras de input
function configurarMascarasInput() {
    const numeroCartao = document.getElementById('numeroCartao');
    const validadeCartao = document.getElementById('validadeCartao');
    const cvvCartao = document.getElementById('cvvCartao');

    // Máscara para número do cartão
    numeroCartao?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        e.target.value = value.substr(0, 19); // 16 dígitos + 3 espaços
    });

    // Máscara para validade MM/AA
    validadeCartao?.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substr(0, 2) + '/' + value.substr(2);
        }
        e.target.value = value.substr(0, 5);
    });

    // Máscara para CVV (3-4 dígitos)
    cvvCartao?.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substr(0, 4);
    });
}

// Gerar QR Code PIX
function gerarQRCodePix(chave) {
    const qr = qrcode(0, 'L');
    qr.addData(chave);
    qr.make();
    document.getElementById('qrCodeContainer').innerHTML = qr.createImgTag(6);
}

// Copiar chave PIX
function copiarChavePix() {
    const chavePix = document.getElementById('chavePix');
    chavePix.select();
    document.execCommand('copy');
    
    // Feedback visual
    const btnCopiar = document.querySelector('#chavePix + button');
    const textoOriginal = btnCopiar.textContent;
    btnCopiar.textContent = 'Copiado!';
    setTimeout(() => btnCopiar.textContent = textoOriginal, 2000);
}

// Validar formulário de cartão
function validarFormularioCartao() {
    const numeroCartao = document.getElementById('numeroCartao').value.replace(/\s/g, '');
    const nomeCartao = document.getElementById('nomeCartao').value;
    const validadeCartao = document.getElementById('validadeCartao').value;
    const cvvCartao = document.getElementById('cvvCartao').value;

    if (numeroCartao.length !== 16) {
        alert('Número do cartão inválido');
        return false;
    }

    if (nomeCartao.length < 3) {
        alert('Nome no cartão inválido');
        return false;
    }

    if (!validadeCartao.match(/^\d{2}\/\d{2}$/)) {
        alert('Validade do cartão inválida');
        return false;
    }

    if (cvvCartao.length < 3) {
        alert('CVV inválido');
        return false;
    }

    return true;
}

// Confirmar pagamento
async function confirmarPagamento() {
    if (!document.getElementById('aceitarTermos').checked) {
        alert('Por favor, aceite os termos e condições para continuar.');
        return;
    }

    const formaPagamento = document.querySelector('input[name="formaPagamento"]:checked').value;
    const servicoId = new URLSearchParams(window.location.search).get('id');
    const btnConfirmar = document.getElementById('btnConfirmarPagamento');

    // Validações específicas por forma de pagamento
    if (formaPagamento === 'cartao' && !validarFormularioCartao()) {
        return;
    }

    if (formaPagamento === 'transferencia') {
        const comprovante = document.getElementById('comprovante').files[0];
        if (!comprovante) {
            alert('Por favor, anexe o comprovante de transferência.');
            return;
        }
    }

    try {
        btnConfirmar.disabled = true;
        btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';

        const formData = new FormData();
        formData.append('servico_id', servicoId);
        formData.append('forma_pagamento', formaPagamento);

        // Adicionar dados específicos por forma de pagamento
        switch (formaPagamento) {
            case 'cartao':
                formData.append('numero_cartao', document.getElementById('numeroCartao').value.replace(/\s/g, ''));
                formData.append('nome_cartao', document.getElementById('nomeCartao').value);
                formData.append('validade_cartao', document.getElementById('validadeCartao').value);
                formData.append('cvv_cartao', document.getElementById('cvvCartao').value);
                formData.append('parcelas', document.getElementById('parcelas').value);
                break;

            case 'transferencia':
                formData.append('comprovante', document.getElementById('comprovante').files[0]);
                break;
        }

        const response = await fetch('../php/pagamento/processar.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        // Mostrar modal de sucesso
        const modal = new bootstrap.Modal(document.getElementById('confirmacaoModal'));
        modal.show();

    } catch (error) {
        console.error('Erro no pagamento:', error);
        alert('Erro ao processar pagamento: ' + error.message);
    } finally {
        btnConfirmar.disabled = false;
        btnConfirmar.textContent = 'Confirmar Pagamento';
    }
}

// Formatar moeda
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}