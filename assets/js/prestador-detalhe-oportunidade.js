// ============================================
// 📋 PRESTADOR - DETALHES DA OPORTUNIDADE
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

/**
 * Verificar autenticação do prestador
 */
async function checkAuthentication() {
    try {
        const response = await fetch('../../php/prestador/prestador-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            alert(data.message || 'Acesso negado. Faça login como prestador.');
            window.location.href = '../login/index.html';
            return;
        }

        // Atualizar nome do usuário
        const userNameElement = document.getElementById('userName');
        if (userNameElement) {
            userNameElement.textContent = data.nome || 'Usuário';
        }

        // Carregar detalhes da oportunidade
        carregarDetalhesOportunidade();
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = '../login/index.html';
    }
}

/**
 * Carregar detalhes da oportunidade
 */
async function carregarDetalhesOportunidade() {
    const loadingState = document.getElementById('loadingState');
    const opportunityDetails = document.getElementById('opportunityDetails');
    const errorState = document.getElementById('errorState');

    // Obter ID da URL
    const urlParams = new URLSearchParams(window.location.search);
    const oportunidadeId = urlParams.get('id');

    if (!oportunidadeId) {
        mostrarErro('ID da oportunidade não fornecido.');
        return;
    }

    // Mostrar loading
    loadingState.style.display = 'block';
    opportunityDetails.style.display = 'none';
    errorState.style.display = 'none';

    try {
        const response = await fetch(`../../php/servico/obter-oportunidade-detalhes.php?id=${oportunidadeId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar detalhes da oportunidade');
        }

        // Esconder loading
        loadingState.style.display = 'none';

        // Preencher dados da oportunidade
        preencherDetalhesOportunidade(data.oportunidade);

        // Mostrar seção de detalhes
        opportunityDetails.style.display = 'block';

        // Configurar formulário de proposta
        configurarFormularioProposta(data.oportunidade);

    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        loadingState.style.display = 'none';
        mostrarErro(error.message);
    }
}

/**
 * Preencher detalhes da oportunidade na página
 */
function preencherDetalhesOportunidade(oportunidade) {
    // Título
    document.getElementById('opportunityTitle').textContent = escapeHtml(oportunidade.titulo);
    
    // Categoria e localização
    document.getElementById('opportunityCategory').textContent = escapeHtml(oportunidade.categoria);
    document.getElementById('opportunityLocation').textContent = escapeHtml(oportunidade.cidade);
    
    // Data de criação
    if (oportunidade.data_criacao_formatada) {
        document.getElementById('opportunityDate').textContent = escapeHtml(oportunidade.data_criacao_formatada);
    }
    
    // Descrição
    document.getElementById('opportunityDescription').textContent = escapeHtml(oportunidade.descricao);
    
    // Endereço
    document.getElementById('opportunityAddress').textContent = escapeHtml(oportunidade.endereco);
    
    // Cidade
    document.getElementById('opportunityCity').textContent = escapeHtml(oportunidade.cidade);
    
    // Prazo desejado
    document.getElementById('opportunityDeadline').textContent = 
        oportunidade.prazo_desejado_texto || 'Não especificado';
    
    // Orçamento máximo
    document.getElementById('opportunityBudget').textContent = 
        oportunidade.orcamento_maximo_formatado || 'A combinar';
    
    // Observações
    const observationsItem = document.getElementById('observationsItem');
    const opportunityObservations = document.getElementById('opportunityObservations');
    if (oportunidade.observacoes && oportunidade.observacoes.trim() !== '') {
        opportunityObservations.textContent = escapeHtml(oportunidade.observacoes);
        observationsItem.style.display = 'block';
    } else {
        observationsItem.style.display = 'none';
    }
    
    // Informações do cliente
    document.getElementById('clientName').textContent = escapeHtml(oportunidade.cliente_nome);
    document.getElementById('clientEmail').textContent = escapeHtml(oportunidade.cliente_email);
    
    const clientPhoneItem = document.getElementById('clientPhoneItem');
    const clientPhone = document.getElementById('clientPhone');
    if (oportunidade.cliente_telefone && oportunidade.cliente_telefone.trim() !== '') {
        clientPhone.textContent = escapeHtml(oportunidade.cliente_telefone);
        clientPhoneItem.style.display = 'block';
    } else {
        clientPhoneItem.style.display = 'none';
    }
}

/**
 * Configurar formulário de proposta
 */
function configurarFormularioProposta(oportunidade) {
    const requestIdInput = document.getElementById('requestId');
    if (requestIdInput) {
        requestIdInput.value = oportunidade.id;
    }

    const proposalSection = document.getElementById('proposalSection');
    const existingProposalAlert = document.getElementById('existingProposalAlert');
    const proposalForm = document.getElementById('proposalForm');
    const submitProposalBtn = document.getElementById('submitProposalBtn');

    // Verificar se já enviou proposta
    if (oportunidade.ja_enviou_proposta && oportunidade.proposta_existente) {
        // Mostrar alerta de proposta existente
        existingProposalAlert.style.display = 'block';
        
        const existingProposalDetails = document.getElementById('existingProposalDetails');
        const proposta = oportunidade.proposta_existente;
        
        let detalhesHtml = '<div class="row">';
        
        if (proposta.valor) {
            detalhesHtml += `
                <div class="col-md-4">
                    <strong>Valor:</strong> R$ ${parseFloat(proposta.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                </div>
            `;
        }
        
        if (proposta.prazo) {
            detalhesHtml += `
                <div class="col-md-8">
                    <strong>Prazo:</strong> ${escapeHtml(proposta.prazo)}
                </div>
            `;
        }
        
        detalhesHtml += '</div>';
        existingProposalDetails.innerHTML = detalhesHtml;
        
        // Desabilitar formulário
        if (proposalForm) {
            proposalForm.style.display = 'none';
        }
        if (submitProposalBtn) {
            submitProposalBtn.disabled = true;
        }
    } else {
        // Esconder alerta
        existingProposalAlert.style.display = 'none';
        
        // Habilitar formulário
        if (proposalForm) {
            proposalForm.style.display = 'block';
            proposalForm.addEventListener('submit', handlePropostaSubmit);
        }
        if (submitProposalBtn) {
            submitProposalBtn.disabled = false;
        }
    }
}

/**
 * Manipular envio de proposta
 */
async function handlePropostaSubmit(event) {
    event.preventDefault();

    const requestId = document.getElementById('requestId').value;
    const valorProposta = document.getElementById('valorProposta').value;
    const prazoEstimado = document.getElementById('prazoEstimado').value;
    const mensagemProposta = document.getElementById('mensagemProposta').value;
    const submitBtn = document.getElementById('submitProposalBtn');

    // Validações
    if (!valorProposta || parseFloat(valorProposta) <= 0) {
        alert('Por favor, informe um valor válido para a proposta');
        return;
    }

    if (!prazoEstimado.trim()) {
        alert('Por favor, informe o prazo estimado');
        return;
    }

    if (!mensagemProposta.trim()) {
        alert('Por favor, descreva como você realizará o trabalho');
        return;
    }

    // Desabilitar botão durante o envio
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    try {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('amount', valorProposta);
        formData.append('estimate', prazoEstimado);
        formData.append('message', mensagemProposta);

        const response = await fetch('../../php/prestador/criar-proposta.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert(`✅ Proposta enviada com sucesso!\n\nO cliente receberá sua proposta e poderá entrar em contato.`);
            
            // Recarregar a página para atualizar o estado
            window.location.reload();
        } else {
            alert('❌ Erro ao enviar proposta: ' + (data.error || data.message || 'Erro desconhecido'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Proposta';
        }

    } catch (error) {
        console.error('Erro ao enviar proposta:', error);
        alert('❌ Erro ao enviar proposta. Verifique sua conexão e tente novamente.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Proposta';
    }
}

/**
 * Mostrar erro
 */
function mostrarErro(mensagem) {
    const errorState = document.getElementById('errorState');
    const errorMessage = document.getElementById('errorMessage');
    
    if (errorMessage) {
        errorMessage.textContent = mensagem;
    }
    
    if (errorState) {
        errorState.style.display = 'block';
    }
}

/**
 * Função auxiliar para escapar HTML e prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Logout
 */
function logout() {
    localStorage.clear();
    fetch('../../php/logout.php')
        .then(() => {
            window.location.href = '../login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            window.location.href = '../login/index.html';
        });
}

