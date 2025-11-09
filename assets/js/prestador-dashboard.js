document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/prestador/prestador-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para a página de login
            alert(data.message || 'Acesso negado');
            window.location.href = './login/index.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza o nome do usuário
        const userNameElements = document.querySelectorAll('#userName');
        userNameElements.forEach(el => {
            el.textContent = data.nome || 'Usuário';
        });
        
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
        if (!tableBody) {
            console.error('Elemento propostasTable não encontrado');
            return;
        }
        
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
                    <strong>${escapeHtml(proposta.solicitacao_titulo)}</strong>
                    <br>
                    <small class="text-muted">${escapeHtml(proposta.categoria)} • ${escapeHtml(proposta.cidade)}</small>
                </td>
                <td>
                    <strong>${escapeHtml(proposta.cliente_nome)}</strong>
                    <br>
                    <small class="text-muted">${escapeHtml(proposta.cliente_email)}</small>
                </td>
                <td>
                    <strong class="text-success">R$ ${proposta.valor_proposto}</strong>
                    <br>
                    <small class="text-muted">Orçamento máximo: ${escapeHtml(proposta.orcamento_maximo)}</small>
                </td>
                <td>
                    <span class="badge bg-info">${escapeHtml(proposta.prazo_estimado)}</span>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        ${getStatusTextSolicitacao(proposta.status_solicitacao)}
                    </span>
                </td>
                <td>
                    <small>${escapeHtml(proposta.data_proposta)}</small>
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
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Erro ao carregar propostas: ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }
}

async function carregarServicosClientes() {
    try {
        const response = await fetch('../php/servico/buscar-solicitacoes.php');
        const data = await response.json();

        const tableBody = document.getElementById('servicosClientesTable');
        if (!tableBody) {
            console.error('Elemento servicosClientesTable não encontrado');
            return;
        }
        
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || !data.solicitacoes || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de serviço postada pelos clientes encontrada.
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada serviço à tabela
        data.solicitacoes.forEach(servico => {
            const row = document.createElement('tr');

            const dataFormatada = servico.data_criacao ? 
                new Date(servico.data_criacao).toLocaleDateString('pt-BR') : 
                'Não informado';
            const orcamento = servico.orcamento_maximo ?
                `R$ ${parseFloat(servico.orcamento_maximo).toFixed(2).replace('.', ',')}` :
                'A combinar';

            // Formatar prazo
            const prazo = servico.prazo_desejado ?
                new Date(servico.prazo_desejado).toLocaleDateString('pt-BR') :
                'Flexível';

            // Create cells programmatically to prevent XSS
            const cells = [];

            // Title cell
            const titleCell = document.createElement('td');
            const titleStrong = document.createElement('strong');
            titleStrong.textContent = servico.titulo;
            titleCell.appendChild(titleStrong);

            const br = document.createElement('br');
            titleCell.appendChild(br);

            const descSmall = document.createElement('small');
            descSmall.className = 'text-muted';
            const descText = servico.descricao && servico.descricao.length > 50 ?
                servico.descricao.substring(0, 50) + '...' :
                (servico.descricao || 'Sem descrição');
            descSmall.textContent = descText;
            titleCell.appendChild(descSmall);
            cells.push(titleCell);

            // Category cell
            const categoryCell = document.createElement('td');
            const categorySpan = document.createElement('span');
            categorySpan.className = 'badge bg-success';
            categorySpan.textContent = servico.categoria || 'N/A';
            categoryCell.appendChild(categorySpan);
            cells.push(categoryCell);

            // Client name cell
            const clientCell = document.createElement('td');
            const clientStrong = document.createElement('strong');
            clientStrong.textContent = servico.cliente_nome || 'N/A';
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
            locationSmall.textContent = servico.cidade || 'N/A';
            locationCell.appendChild(locationSmall);
            cells.push(locationCell);

            // Status cell
            const statusCell = document.createElement('td');
            const statusSpan = document.createElement('span');
            const statusClass = getStatusClassSolicitacao(servico.status);
            const validStatusClasses = ['bg-warning', 'bg-primary', 'bg-success', 'bg-danger', 'bg-secondary'];
            const safeStatusClass = validStatusClasses.includes(statusClass) ? statusClass : 'bg-secondary';
            statusSpan.className = `badge ${safeStatusClass} text-dark`;
            statusSpan.textContent = getStatusTextSolicitacao(servico.status);
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

            // Send proposal button - only if status is 'pendente'
            if (servico.status === 'pendente') {
                const proposalBtn = document.createElement('button');
                proposalBtn.type = 'button';
                proposalBtn.className = 'btn btn-sm btn-outline-success';
                proposalBtn.title = 'Enviar Proposta';
                proposalBtn.addEventListener('click', () => enviarProposta(servico.id));

                const proposalIcon = document.createElement('i');
                proposalIcon.className = 'bi bi-send';
                proposalBtn.appendChild(proposalIcon);
                btnGroup.appendChild(proposalBtn);
            }

            actionsCell.appendChild(btnGroup);
            cells.push(actionsCell);

            // Append all cells to the row
            cells.forEach(cell => row.appendChild(cell));

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços dos clientes:', error);
        const tableBody = document.getElementById('servicosClientesTable');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Erro ao carregar serviços: ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
        }
    }
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
    window.location.href = `./prestador/detalhe-oportunidade.html?id=${id}`;
}

function enviarProposta(servicoId) {
    window.location.href = `./prestador/detalhe-oportunidade.html?id=${servicoId}`;
}

// Funções para gerenciar propostas
function verDetalhesProposta(proposalId) {
    // Implementar modal ou página para ver detalhes da proposta
    alert(`Ver detalhes da proposta ${proposalId} - Funcionalidade em desenvolvimento`);
}

function editarProposta(proposalId) {
    // Implementar funcionalidade para editar proposta
    alert(`Editar proposta ${proposalId} - Funcionalidade em desenvolvimento`);
}

// Função auxiliar para escapar HTML e prevenir XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

