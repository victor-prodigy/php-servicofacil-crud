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

        // Carregar a lista de serviços (próprios) e solicitações (visualização)
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
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum serviço publicado ainda.
                        <br>
                        <a href="./servico/servico-novo.html" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Publicar primeiro serviço
                        </a>
                    </td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');

            // Formatar data
            const data_formatada = new Date(servico.data_postagem).toLocaleDateString('pt-BR');

            // Definir classe do status
            const statusClass = getStatusClass(servico.status);

            // Formatar preço
            const preco = servico.orcamento ? 
                new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(servico.orcamento) : 'A combinar';

            row.innerHTML = `
                <td>
                    <strong>${servico.titulo}</strong>
                    <br>
                    <small class="text-muted">${servico.localizacao}</small>
                </td>
                <td>
                    <span class="badge bg-info">${servico.categoria}</span>
                </td>
                <td>${preco}</td>
                <td><span class="badge ${statusClass}">${getStatusText(servico.status)}</span></td>
                <td>
                    <small>${data_formatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhesServico(${servico.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editarServico(${servico.id})" 
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="excluirServico(${servico.id})" 
                                title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar serviços: ${error.message}
                </td>
            </tr>
        `;
    }
}

async function carregarSolicitacoes() {
    try {
        const response = await fetch('../php/servico/listar-solicitacoes-prestador.php');
        const data = await response.json();

        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de cliente encontrada.
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
                    <small class="text-muted">${solicitacao.endereco}</small>
                </td>
                <td>
                    <span class="badge bg-success">${solicitacao.categoria}</span>
                </td>
                <td>${orcamento}</td>
                <td>
                    <small>${solicitacao.prazo_desejado}</small>
                </td>
                <td>
                    <small>${solicitacao.cidade}</small>
                </td>
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
                                onclick="verDetalhesSolicitacao(${solicitacao.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="oferecerOrcamento(${solicitacao.id})" 
                                title="Ofertar serviço">
                            <i class="bi bi-hand-thumbs-up"></i>
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
                <td colspan="8" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar solicitações: ${error.message}
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

// Funções para ações das solicitações
function verDetalhesSolicitacao(id) {
    alert(`Ver detalhes da solicitação ${id} - Funcionalidade em desenvolvimento`);
}

function oferecerOrcamento(id) {
    alert(`Ofertar orçamento para solicitação ${id} - Funcionalidade em desenvolvimento`);
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