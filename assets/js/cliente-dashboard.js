document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/cliente/cliente-dashboard.php');
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
        
        // Carregar a lista de serviços e solicitações
        carregarServicos();
        carregarSolicitacoes();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarServicos() {
    try {
        const response = await fetch('../php/servico/servico-listar.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Nenhum serviço publicado ainda.</td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');
            
            // Formatar data
            const data = new Date(servico.data_postagem).toLocaleDateString('pt-BR');
            
            // Definir classe do status
            const statusClass = getStatusClass(servico.status);
            
            // Formatar orçamento
            const orcamento = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(servico.orcamento);

            row.innerHTML = `
                <td>
                    <a href="./servico/servico-detalhe.html?id=${servico.id}" class="text-decoration-none">
                        ${servico.titulo}
                    </a>
                </td>
                <td>${servico.categoria}</td>
                <td>${orcamento}</td>
                <td><span class="badge ${statusClass}">${servico.status}</span></td>
                <td>${data}</td>
                <td>
                    <a href="./servico/servico-editar.html?id=${servico.id}" class="btn btn-sm btn-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="excluirServico(${servico.id})" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        alert('Erro ao carregar a lista de serviços. Por favor, tente novamente.');
    }
}

function getStatusClass(status) {
    const statusClasses = {
        'aberto': 'bg-success',
        'em_andamento': 'bg-warning',
        'concluido': 'bg-info',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('servico_id', id);

        const response = await fetch('../php/servico/servico-excluir.php', {
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

// Função para carregar solicitações de serviço
async function carregarSolicitacoes() {
    try {
        const response = await fetch('../php/servico/listar-solicitacoes.php');
        const data = await response.json();

        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de serviço encontrada.
                        <br>
                        <a href="./servico/solicitar-servico.html" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Fazer primeira solicitação
                        </a>
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada solicitação à tabela
        data.solicitacoes.forEach(solicitacao => {
            const row = document.createElement('tr');
            
            const dataFormatada = new Date(solicitacao.data_criacao).toLocaleDateString('pt-BR');
            const orcamento = solicitacao.orcamento_maximo ? 
                `R$ ${parseFloat(solicitacao.orcamento_maximo).toFixed(2)}` : 
                'Flexível';
            
            row.innerHTML = `
                <td>
                    <strong>${solicitacao.titulo}</strong>
                    <br>
                    <small class="text-muted">${solicitacao.cidade}</small>
                </td>
                <td>
                    <span class="badge bg-info">${solicitacao.categoria}</span>
                </td>
                <td>
                    <small>${solicitacao.prazo_desejado}</small>
                </td>
                <td>${orcamento}</td>
                <td>
                    <span class="badge ${getStatusClassSolicitacao(solicitacao.status)}">
                        ${getStatusTextSolicitacao(solicitacao.status)}
                    </span>
                </td>
                <td>
                    <small>${dataFormatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhes(${solicitacao.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editarSolicitacao(${solicitacao.id})" 
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="excluirSolicitacao(${solicitacao.id})" 
                                title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar solicitações:', error);
        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar solicitações: ${error.message}
                </td>
            </tr>
        `;
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

// Funções para ações das solicitações
function verDetalhes(id) {
    // Implementar modal ou página de detalhes
    alert(`Ver detalhes da solicitação ${id} - Funcionalidade em desenvolvimento`);
}

function editarSolicitacao(id) {
    // Redirecionar para página de edição (a ser implementada)
    alert(`Editar solicitação ${id} - Funcionalidade em desenvolvimento`);
}

async function excluirSolicitacao(id) {
    if (!confirm('Tem certeza que deseja excluir esta solicitação de serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('solicitacao_id', id);

        const response = await fetch('../php/servico/excluir-solicitacao.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Solicitação excluída com sucesso!');
            carregarSolicitacoes(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir solicitação:', error);
        alert('Erro ao excluir solicitação: ' + error.message);
    }
}
}