document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/prestador/prestador-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para a página de login
            alert(data.message);
            window.location.href = './login/index.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza o nome do usuário
        document.getElementById('userName').textContent = data.nome;
        document.getElementById('dashboardContent').style.display = 'block';

        // Carregar a lista de propostas (próprias) e serviços dos clientes (visualização)
        carregarPropostas();
        carregarServicosClientes();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarPropostas() {
    try {
        const response = await fetch('../php/prestador/listar-propostas.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Erro ao carregar propostas');
        }

        const tableBody = document.getElementById('propostasTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (data.propostas.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma proposta enviada ainda.
                        <br>
                        <small>Navegue pelas solicitações de clientes abaixo e envie suas propostas!</small>
                    </td>
                </tr>
            `;
            return;
        }

        data.propostas.forEach(proposta => {
            const row = document.createElement('tr');

            // Definir classe do status baseado no status da solicitação
            const statusClass = getStatusClassSolicitacao(proposta.status_solicitacao);

            row.innerHTML = `
                <td>
                    <strong>${proposta.solicitacao_titulo}</strong>
                    <br>
                    <small class="text-muted">${proposta.categoria} • ${proposta.cidade}</small>
                </td>
                <td>
                    <strong>${proposta.cliente_nome}</strong>
                    <br>
                    <small class="text-muted">${proposta.cliente_email}</small>
                </td>
                <td>
                    <strong class="text-success">R$ ${proposta.valor_proposto}</strong>
                    <br>
                    <small class="text-muted">Orçamento máximo: ${proposta.orcamento_maximo}</small>
                </td>
                <td>
                    <span class="badge bg-info">${proposta.prazo_estimado}</span>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${getStatusTextSolicitacao(proposta.status_solicitacao)}
                    </span>
                </td>
                <td>
                    <small>${proposta.data_proposta}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhesProposta(${proposta.proposal_id})" 
                                title="Ver detalhes da proposta">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editarProposta(${proposta.proposal_id})" 
                                title="Editar proposta">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar propostas:', error);
        const tableBody = document.getElementById('propostasTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar propostas: ${error.message}
                </td>
            </tr>
        `;
    }
}

async function carregarServicosClientes() {
    try {
        const response = await fetch('../php/servico/listar-servico.php');
        const data = await response.json();

        const tableBody = document.getElementById('servicosClientesTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum serviço postado pelos clientes encontrado.
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada serviço à tabela
        data.servicos.forEach(servico => {
            const row = document.createElement('tr');

            const dataFormatada = new Date(servico.data_postagem).toLocaleDateString('pt-BR');
            const orcamento = servico.orcamento ?
                `R$ ${parseFloat(servico.orcamento).toFixed(2)}` :
                'A combinar';

            // Formatar prazo
            const prazo = servico.prazo ?
                new Date(servico.prazo).toLocaleDateString('pt-BR') :
                'Flexível';

            // Create cells programmatically to prevent XSS
            const cells = [];

            // Title and description cell
            const titleCell = document.createElement('td');
            const titleStrong = document.createElement('strong');
            titleStrong.textContent = servico.titulo;
            titleCell.appendChild(titleStrong);

            const br = document.createElement('br');
            titleCell.appendChild(br);

            const descSmall = document.createElement('small');
            descSmall.className = 'text-muted';
            const descText = servico.descricao.length > 50 ?
                servico.descricao.substring(0, 50) + '...' :
                servico.descricao;
            descSmall.textContent = descText;
            titleCell.appendChild(descSmall);
            cells.push(titleCell);

            // Category cell
            const categoryCell = document.createElement('td');
            const categorySpan = document.createElement('span');
            categorySpan.className = 'badge bg-success';
            categorySpan.textContent = servico.categoria;
            categoryCell.appendChild(categorySpan);
            cells.push(categoryCell);

            // Client name cell
            const clientCell = document.createElement('td');
            const clientStrong = document.createElement('strong');
            clientStrong.textContent = servico.cliente_nome;
            clientCell.appendChild(clientStrong);
            cells.push(clientCell);

            // Budget cell
            const budgetCell = document.createElement('td');
            const budgetStrong = document.createElement('strong');
            budgetStrong.className = 'text-success';
            budgetStrong.textContent = orcamento;
            budgetCell.appendChild(budgetStrong);
            cells.push(budgetCell);

            // Deadline cell
            const deadlineCell = document.createElement('td');
            const deadlineSmall = document.createElement('small');
            deadlineSmall.textContent = prazo;
            deadlineCell.appendChild(deadlineSmall);
            cells.push(deadlineCell);

            // Location cell
            const locationCell = document.createElement('td');
            const locationSmall = document.createElement('small');
            locationSmall.textContent = servico.localizacao;
            locationCell.appendChild(locationSmall);
            cells.push(locationCell);

            // Status cell
            const statusCell = document.createElement('td');
            const statusSpan = document.createElement('span');
            const statusClass = getStatusClassServico(servico.status);
            // Validate status class to prevent XSS
            const validStatusClasses = ['bg-success', 'bg-secondary', 'bg-primary', 'bg-info', 'bg-danger'];
            const safeStatusClass = validStatusClasses.includes(statusClass) ? statusClass : 'bg-secondary';
            statusSpan.className = `badge ${safeStatusClass}`;
            statusSpan.textContent = getStatusTextServico(servico.status);
            statusCell.appendChild(statusSpan);
            cells.push(statusCell);

            // Date cell
            const dateCell = document.createElement('td');
            const dateSmall = document.createElement('small');
            dateSmall.textContent = dataFormatada;
            dateCell.appendChild(dateSmall);
            cells.push(dateCell);

            // Actions cell
            const actionsCell = document.createElement('td');
            const btnGroup = document.createElement('div');
            btnGroup.className = 'btn-group';
            btnGroup.setAttribute('role', 'group');

            // View details button
            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'btn btn-sm btn-outline-primary';
            viewBtn.title = 'Ver detalhes';
            viewBtn.addEventListener('click', () => verDetalhesServico(servico.id));

            const viewIcon = document.createElement('i');
            viewIcon.className = 'bi bi-eye';
            viewBtn.appendChild(viewIcon);
            btnGroup.appendChild(viewBtn);

            // Send proposal button
            const proposalBtn = document.createElement('button');
            proposalBtn.type = 'button';
            proposalBtn.className = 'btn btn-sm btn-outline-success';
            proposalBtn.title = 'Enviar Proposta';
            proposalBtn.addEventListener('click', () => enviarProposta(servico.id));

            const proposalIcon = document.createElement('i');
            proposalIcon.className = 'bi bi-send';
            proposalBtn.appendChild(proposalIcon);
            btnGroup.appendChild(proposalBtn);

            actionsCell.appendChild(btnGroup);
            cells.push(actionsCell);

            // Append all cells to the row
            cells.forEach(cell => row.appendChild(cell));

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços dos clientes:', error);
        const tableBody = document.getElementById('servicosClientesTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar serviços: ${error.message}
                </td>
            </tr>
        `;
    }
}

// Funções auxiliares para status dos serviços
function getStatusClass(status) {
    const statusClasses = {
        'ativo': 'bg-success',
        'inativo': 'bg-secondary',
        'pausado': 'bg-warning text-dark'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusText(status) {
    const statusTexts = {
        'ativo': 'Ativo',
        'inativo': 'Inativo',
        'pausado': 'Pausado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções auxiliares para status dos serviços postados pelos clientes
function getStatusClassServico(status) {
    const statusClasses = {
        'aberto': 'bg-success',
        'fechado': 'bg-secondary',
        'em_andamento': 'bg-primary',
        'concluido': 'bg-info',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusTextServico(status) {
    const statusTexts = {
        'aberto': 'Aberto',
        'fechado': 'Fechado',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções auxiliares para status das solicitações
function getStatusClassSolicitacao(status) {
    const statusClasses = {
        'pendente': 'bg-warning text-dark',
        'em_andamento': 'bg-primary',
        'concluido': 'bg-success',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusTextSolicitacao(status) {
    const statusTexts = {
        'pendente': 'Pendente',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações dos serviços
function verDetalhesServico(id) {
    alert(`Ver detalhes do serviço ${id} - Funcionalidade em desenvolvimento`);
}

function editarServico(id) {
    alert(`Editar serviço ${id} - Funcionalidade em desenvolvimento`);
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('servico_id', id);

        const response = await fetch('../php/servico/apaga-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Serviço excluído com sucesso!');
            carregarServicos(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        alert('Erro ao excluir serviço: ' + error.message);
    }
}

function enviarProposta(servicoId) {
    // Criar modal para enviar proposta para service_request
    const modalHtml = `
        <div class="modal fade" id="propostaModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-send-fill"></i> Enviar Proposta para Solicitação
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="propostaForm">
                            <input type="hidden" id="requestId" value="${servicoId}">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="valorProposta" class="form-label">Valor da Proposta (R$) *</label>
                                        <input type="number" class="form-control" id="valorProposta" 
                                               step="0.01" min="0.01" required 
                                               placeholder="Ex: 150.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prazoEstimado" class="form-label">Prazo Estimado *</label>
                                        <input type="text" class="form-control" id="prazoEstimado" 
                                               placeholder="Ex: 3 dias, 1 semana, 15 dias úteis" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensagemProposta" class="form-label">Descrição do Trabalho *</label>
                                <textarea class="form-control" id="mensagemProposta" rows="4" required
                                          placeholder="Descreva detalhadamente como você realizará o serviço, materiais que utilizará, metodologia, etc..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="processarEnvioProposta()">
                            <i class="bi bi-send"></i> Enviar Proposta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior se existir
    const existingModal = document.getElementById('propostaModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Adicionar modal ao body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('propostaModal'));
    modal.show();
}

// Funções para gerenciar propostas
async function processarEnvioProposta() {
    try {
        const requestId = document.getElementById('requestId').value;
        const valorProposta = document.getElementById('valorProposta').value;
        const prazoEstimado = document.getElementById('prazoEstimado').value;
        const mensagemProposta = document.getElementById('mensagemProposta').value;

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

        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('amount', valorProposta);
        formData.append('estimate', prazoEstimado);
        formData.append('message', mensagemProposta);

        const response = await fetch('../php/prestador/criar-proposta.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('propostaModal'));
            modal.hide();

            // Mostrar mensagem de sucesso
            alert(`✅ Proposta enviada com sucesso para "${data.solicitacao_titulo}"!\n\nO cliente receberá sua proposta e poderá entrar em contato.`);

            // Recarregar a lista de propostas
            carregarPropostas();

        } else {
            alert('❌ Erro ao enviar proposta: ' + data.error);
        }

    } catch (error) {
        console.error('Erro ao enviar proposta:', error);
        alert('❌ Erro ao enviar proposta. Verifique sua conexão e tente novamente.');
    }
}


function verDetalhesProposta(proposalId) {
    // Implementar modal ou página para ver detalhes da proposta
    alert(`Ver detalhes da proposta ${proposalId} - Funcionalidade em desenvolvimento`);
}

function editarProposta(proposalId) {
    // Implementar funcionalidade para editar proposta
    alert(`Editar proposta ${proposalId} - Funcionalidade em desenvolvimento`);
}

function logout() {
    // Limpar dados da sessão no localStorage
    localStorage.clear();

    // Fazer requisição para destruir a sessão no servidor
    fetch('../php/logout.php')
        .then(() => {
            // Redirecionar para a página de login
            window.location.href = './login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            // Mesmo com erro, redireciona para garantir que o usuário saia
            window.location.href = './login/index.html';
        });
}