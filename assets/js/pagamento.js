document.addEventListener('DOMContentLoaded', function() {
    carregarDetalhesServico();
    configurarFormasPagamento();
    configurarMascarasInput();
});

// Carregar detalhes do contrato/serviço
async function carregarDetalhesServico() {
    const urlParams = new URLSearchParams(window.location.search);
    const contractId = urlParams.get('id');

    if (!contractId) {
        alert('Contrato não especificado');
        window.location.href = 'cliente-dashboard.html';
        return;
    }

    try {
        const response = await fetch(`../php/servico/detalhe-contrato-pagamento.php?id=${contractId}`);
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

        // Atualizar status
        const statusElement = document.getElementById('servicoStatus');
        if (servico.contract_status === 'active') {
            statusElement.textContent = 'Ativo';
            statusElement.className = 'badge bg-success';
        } else if (servico.contract_status === 'completed') {
            statusElement.textContent = 'Concluído';
            statusElement.className = 'badge bg-info';
        }

        // Se for pagamento PIX, gerar QR Code e preencher chave
        if (servico.chave_pix) {
            document.getElementById('chavePix').value = servico.chave_pix;
            gerarQRCodePix(servico.chave_pix);
        }

    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        alert('Erro ao carregar detalhes do contrato: ' + error.message);
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
    try {
        const qr = qrcode(0, 'L');
        qr.addData(chave);
        qr.make();
        const qrContainer = document.getElementById('qrCodeContainer');
        if (qrContainer) {
            qrContainer.innerHTML = qr.createImgTag(6);
        }
    } catch (error) {
        console.error('Erro ao gerar QR Code:', error);
    }
}

// Copiar chave PIX
function copiarChavePix() {
    const chavePix = document.getElementById('chavePix');
    if (!chavePix) return;
    
    chavePix.select();
    chavePix.setSelectionRange(0, 99999); // Para mobile
    
    try {
        document.execCommand('copy');
        
        // Feedback visual
        const btnCopiar = document.querySelector('#chavePix + button');
        if (btnCopiar) {
            const textoOriginal = btnCopiar.textContent;
            btnCopiar.textContent = 'Copiado!';
            setTimeout(() => btnCopiar.textContent = textoOriginal, 2000);
        }
        
        alert('Chave PIX copiada para a área de transferência!');
    } catch (err) {
        console.error('Erro ao copiar:', err);
        alert('Erro ao copiar chave PIX. Por favor, copie manualmente.');
    }
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

    const formaPagamento = document.querySelector('input[name="formaPagamento"]:checked');
    if (!formaPagamento) {
        alert('Por favor, selecione uma forma de pagamento.');
        return;
    }

    const contractId = new URLSearchParams(window.location.search).get('id');
    if (!contractId) {
        alert('ID do contrato não encontrado.');
        return;
    }

    const btnConfirmar = document.getElementById('btnConfirmarPagamento');

    // Validações específicas por forma de pagamento
    if (formaPagamento.value === 'cartao' && !validarFormularioCartao()) {
        return;
    }

    if (formaPagamento.value === 'transferencia') {
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
        formData.append('contract_id', contractId);
        formData.append('forma_pagamento', formaPagamento.value);

        // Adicionar dados específicos por forma de pagamento
        switch (formaPagamento.value) {
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
        const modalElement = document.getElementById('confirmacaoModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            alert('Pagamento processado com sucesso!');
            window.location.href = 'cliente-dashboard.html';
        }

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

