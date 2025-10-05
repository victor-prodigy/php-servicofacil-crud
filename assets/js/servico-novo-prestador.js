document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('servicoForm');
    const alertDiv = document.getElementById('alertMessage');
    
    // Função para mostrar alertas
    function showAlert(message, type = 'danger') {
        if (alertDiv) {
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.classList.remove('d-none');
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            alert(message);
        }
    }

    // Verificar parâmetros de erro na URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error')) {
        const errorType = urlParams.get('error');
        let errorMessage = 'Erro ao publicar serviço. Tente novamente.';
        
        switch(errorType) {
            case 'unauthorized':
                errorMessage = 'Acesso não autorizado. Faça login como prestador.';
                setTimeout(() => window.location.href = '../../index.html', 2000);
                break;
            case 'no_provider':
                errorMessage = 'Perfil de prestador não encontrado. Entre em contato com o suporte.';
                break;
            case 'empty_fields':
                errorMessage = 'Por favor, preencha todos os campos obrigatórios.';
                break;
            case 'exception':
                errorMessage = 'Erro no servidor. Tente novamente mais tarde.';
                break;
            case 'invalid_method':
                errorMessage = 'Método de requisição inválido.';
                break;
        }
        
        showAlert(errorMessage, 'danger');
    }

    // Verificar mensagem de sucesso
    if (urlParams.has('success')) {
        showAlert('Serviço publicado com sucesso!', 'success');
        setTimeout(() => {
            window.location.href = '../prestador-dashboard.html';
        }, 2000);
    }

    // Formatação de valor monetário
    const orcamentoInput = document.getElementById('orcamento');
    if (orcamentoInput) {
        orcamentoInput.addEventListener('blur', function() {
            if (this.value) {
                const valor = parseFloat(this.value);
                if (!isNaN(valor)) {
                    this.value = valor.toFixed(2);
                }
            }
        });
    }

    // Validação e envio do formulário
    form.addEventListener('submit', function(e) {
        // Limpar mensagens anteriores
        if (alertDiv) {
            alertDiv.classList.add('d-none');
        }

        // Validar título
        const titulo = document.getElementById('titulo').value.trim();
        if (titulo.length < 5) {
            e.preventDefault();
            showAlert('O título deve ter pelo menos 5 caracteres.', 'warning');
            return false;
        }

        // Validar descrição
        const descricao = document.getElementById('descricao').value.trim();
        if (descricao.length < 20) {
            e.preventDefault();
            showAlert('A descrição deve ter pelo menos 20 caracteres para ser mais informativa.', 'warning');
            return false;
        }

        // Validar categoria
        const categoria = document.getElementById('categoria').value;
        if (!categoria) {
            e.preventDefault();
            showAlert('Por favor, selecione uma categoria.', 'warning');
            return false;
        }

        // Validar orçamento
        const orcamento = parseFloat(document.getElementById('orcamento').value);
        if (isNaN(orcamento) || orcamento <= 0) {
            e.preventDefault();
            showAlert('O preço do serviço deve ser maior que zero.', 'warning');
            return false;
        }

        // Validar prazo
        const prazo = document.getElementById('prazo').value.trim();
        if (prazo.length < 3) {
            e.preventDefault();
            showAlert('Por favor, informe um prazo estimado válido.', 'warning');
            return false;
        }

        // Validar localização
        const localizacao = document.getElementById('localizacao').value.trim();
        if (localizacao.length < 5) {
            e.preventDefault();
            showAlert('Por favor, informe a área de atendimento.', 'warning');
            return false;
        }

        // Mostrar loading no botão
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
        }

        // Permitir envio do formulário
        return true;
    });

    // Auto-resize do textarea de descrição
    const descricaoTextarea = document.getElementById('descricao');
    if (descricaoTextarea) {
        descricaoTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Capitalizar primeira letra do título
    const tituloInput = document.getElementById('titulo');
    if (tituloInput) {
        tituloInput.addEventListener('blur', function() {
            if (this.value) {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            }
        });
    }

    // Contador de caracteres para descrição
    if (descricaoTextarea) {
        const charCountDiv = document.createElement('div');
        charCountDiv.className = 'form-text text-end';
        charCountDiv.id = 'charCount';
        descricaoTextarea.parentNode.appendChild(charCountDiv);

        descricaoTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCountDiv.textContent = `${length} caracteres`;
            
            if (length < 20) {
                charCountDiv.className = 'form-text text-end text-danger';
            } else if (length < 50) {
                charCountDiv.className = 'form-text text-end text-warning';
            } else {
                charCountDiv.className = 'form-text text-end text-success';
            }
        });
        
        // Trigger inicial
        descricaoTextarea.dispatchEvent(new Event('input'));
    }
});