// ============================================
// 📋 PRESTADOR - ENTRAR EM CONTATO COM CLIENTE
// ============================================

let currentClienteId = null;
let currentContractId = null;
let chatInterval = null;

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

        // Carregar solicitações aprovadas
        carregarSolicitacoesAprovadas();
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = '../login/index.html';
    }
}

/**
 * Carregar solicitações aprovadas
 */
async function carregarSolicitacoesAprovadas() {
    const loadingState = document.getElementById('loadingState');
    const contractsList = document.getElementById('contractsList');
    const noContractsState = document.getElementById('noContractsState');

    // Mostrar loading
    loadingState.style.display = 'block';
    contractsList.style.display = 'none';
    noContractsState.style.display = 'none';

    try {
        const response = await fetch('../../php/prestador/listar-solicitacoes-aprovadas.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar solicitações');
        }

        // Esconder loading
        loadingState.style.display = 'none';

        if (data.contratos && data.contratos.length > 0) {
            // Mostrar lista de contratos
            exibirContratos(data.contratos);
            contractsList.style.display = 'block';
        } else {
            // Mostrar estado vazio
            noContractsState.style.display = 'block';
        }

    } catch (error) {
        console.error('Erro ao carregar solicitações:', error);
        loadingState.style.display = 'none';
        alert('Erro ao carregar solicitações aprovadas: ' + error.message);
    }
}

/**
 * Exibir contratos na página
 */
function exibirContratos(contratos) {
    const contractsList = document.getElementById('contractsList');
    let html = '';

    contratos.forEach(contrato => {
        html += `
            <div class="card contact-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-contract"></i> ${escapeHtml(contrato.titulo)}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="contact-info-item">
                                <div class="contact-label">
                                    <i class="fas fa-tag"></i> Categoria
                                </div>
                                <div class="contact-value">
                                    <span class="badge bg-secondary">${escapeHtml(contrato.categoria)}</span>
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <div class="contact-label">
                                    <i class="fas fa-user"></i> Cliente
                                </div>
                                <div class="contact-value">
                                    ${escapeHtml(contrato.cliente_nome)}
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <div class="contact-label">
                                    <i class="fas fa-map-marker-alt"></i> Localização
                                </div>
                                <div class="contact-value">
                                    ${escapeHtml(contrato.cidade)} - ${escapeHtml(contrato.endereco)}
                                </div>
                            </div>
                            <div class="contact-info-item">
                                <div class="contact-label">
                                    <i class="fas fa-calendar"></i> Data do Contrato
                                </div>
                                <div class="contact-value">
                                    ${escapeHtml(contrato.contract_date)}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="contact-buttons">
                                <button 
                                    class="btn btn-contact btn-contact-action"
                                    onclick="verDadosContato(${contrato.cliente_id}, ${contrato.contract_id})"
                                >
                                    <i class="fas fa-address-card"></i> Ver Dados de Contato
                                </button>
                                <button 
                                    class="btn btn-contact btn-contact-action"
                                    onclick="abrirChat(${contrato.cliente_id}, ${contrato.contract_id}, '${escapeHtml(contrato.cliente_nome)}')"
                                >
                                    <i class="fas fa-comments"></i> Chat Interno
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    contractsList.innerHTML = html;
}

/**
 * Ver dados de contato do cliente
 */
async function verDadosContato(clienteId, contractId) {
    try {
        const response = await fetch(
            `../../php/prestador/obter-dados-contato.php?cliente_id=${clienteId}&contract_id=${contractId}`
        );
        const data = await response.json();

        if (!data.success) {
            alert('Erro ao carregar dados de contato: ' + data.message);
            return;
        }

        const cliente = data.cliente;
        const contactInfo = document.getElementById('contactInfo');
        
        contactInfo.innerHTML = `
            <div class="contact-info-item">
                <div class="contact-label">
                    <i class="fas fa-user"></i> Nome
                </div>
                <div class="contact-value">
                    ${escapeHtml(cliente.nome)}
                </div>
            </div>
            <div class="contact-info-item">
                <div class="contact-label">
                    <i class="fas fa-envelope"></i> Email
                </div>
                <div class="contact-value">
                    <a href="mailto:${escapeHtml(cliente.email)}">${escapeHtml(cliente.email)}</a>
                </div>
            </div>
            <div class="contact-info-item">
                <div class="contact-label">
                    <i class="fas fa-phone"></i> Telefone
                </div>
                <div class="contact-value">
                    ${cliente.telefone !== 'Não informado' 
                        ? `<a href="tel:${escapeHtml(cliente.telefone)}">${escapeHtml(cliente.telefone)}</a>` 
                        : escapeHtml(cliente.telefone)}
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                <strong>Informação:</strong> Estes dados são fornecidos de forma segura apenas para prestadores com contratos ativos.
            </div>
        `;

        // Abrir modal
        const modal = new bootstrap.Modal(document.getElementById('contactModal'));
        modal.show();

    } catch (error) {
        console.error('Erro ao carregar dados de contato:', error);
        alert('Erro ao carregar dados de contato. Tente novamente.');
    }
}

/**
 * Abrir chat com cliente
 */
async function abrirChat(clienteId, contractId, clienteNome) {
    currentClienteId = clienteId;
    currentContractId = contractId;

    // Atualizar título do modal
    document.getElementById('chatModalLabel').innerHTML = `
        <i class="fas fa-comments"></i> Chat com ${escapeHtml(clienteNome)}
    `;

    // Limpar mensagens anteriores
    document.getElementById('chatMessages').innerHTML = '';
    document.getElementById('messageInput').value = '';

    // Carregar mensagens
    await carregarMensagens();

    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('chatModal'));
    modal.show();

    // Iniciar atualização automática de mensagens
    if (chatInterval) {
        clearInterval(chatInterval);
    }
    chatInterval = setInterval(carregarMensagens, 3000); // Atualizar a cada 3 segundos

    // Parar atualização quando modal fechar
    const chatModal = document.getElementById('chatModal');
    chatModal.addEventListener('hidden.bs.modal', function () {
        if (chatInterval) {
            clearInterval(chatInterval);
            chatInterval = null;
        }
    }, { once: true });
}

/**
 * Carregar mensagens do chat
 */
async function carregarMensagens() {
    if (!currentClienteId || !currentContractId) return;

    try {
        const response = await fetch(
            `../../php/prestador/listar-mensagens.php?cliente_id=${currentClienteId}&contract_id=${currentContractId}`
        );
        const data = await response.json();

        if (!data.success) {
            console.error('Erro ao carregar mensagens:', data.message);
            return;
        }

        const chatMessages = document.getElementById('chatMessages');
        let html = '';

        if (data.mensagens && data.mensagens.length > 0) {
            data.mensagens.forEach(msg => {
                const messageClass = msg.is_sent ? 'sent' : 'received';
                html += `
                    <div class="message ${messageClass}">
                        <div>${escapeHtml(msg.message)}</div>
                        <div class="message-time">${escapeHtml(msg.sent_at)}</div>
                    </div>
                `;
            });
        } else {
            html = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-comments" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mt-2">Nenhuma mensagem ainda. Inicie a conversa!</p>
                </div>
            `;
        }

        chatMessages.innerHTML = html;
        
        // Scroll para o final
        chatMessages.scrollTop = chatMessages.scrollHeight;

    } catch (error) {
        console.error('Erro ao carregar mensagens:', error);
    }
}

/**
 * Enviar mensagem
 */
async function sendMessage() {
    if (!currentClienteId || !currentContractId) {
        alert('Erro: IDs não definidos');
        return;
    }

    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    const sendBtn = document.getElementById('sendMessageBtn');

    if (!message) {
        alert('Por favor, digite uma mensagem');
        return;
    }

    // Desabilitar botão durante envio
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    try {
        const formData = new FormData();
        formData.append('cliente_id', currentClienteId);
        formData.append('contract_id', currentContractId);
        formData.append('message', message);

        const response = await fetch('../../php/prestador/enviar-mensagem.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Limpar campo de input
            messageInput.value = '';
            
            // Recarregar mensagens
            await carregarMensagens();
        } else {
            alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
        }

    } catch (error) {
        console.error('Erro ao enviar mensagem:', error);
        alert('Erro ao enviar mensagem. Verifique sua conexão e tente novamente.');
    } finally {
        // Reabilitar botão
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
    }
}

/**
 * Permitir envio com Enter (Shift+Enter para nova linha)
 */
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }
});

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

