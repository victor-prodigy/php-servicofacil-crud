document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

function setupTabNavigation() {
    const tabItems = document.querySelectorAll('.tab-nav-item');
    tabItems.forEach(item => {
        item.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
}

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

        // Inicializar formulário de busca após exibir o conteúdo
        setTimeout(function () {
            if (typeof initializeSearch === 'function') {
                initializeSearch();
            }
        }, 150);

        // Setup tab navigation after content is shown
        setupTabNavigation();

        // Carregar a lista de serviços, solicitações e contratos
        carregarServicos();
        carregarSolicitacoes();
        carregarContratos();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarServicos() {
    try {
        const response = await fetch('../php/servico/listar-servico.php');
        
        // Verificar se a resposta é OK
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
        }
        
        // Verificar se o conteúdo é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido');
        }
        
        const data = await response.json();

        const tableBody = document.getElementById('servicosTable');
        if (!tableBody) return;
        
        tableBody.innerHTML = ''; // Limpar tabela

        // Verificar se há erro na resposta
        if (!data.success) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        ${data.message || 'Erro ao carregar serviços'}
                    </td>
                </tr>
            `;
            return;
        }

        // Verificar se não há serviços
        if (!data.servicos || data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum serviço disponível no momento.
                    </td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');

            // Formatar data
            const data_formatada = servico.data_postagem ? 
                new Date(servico.data_postagem).toLocaleDateString('pt-BR') : 
                'Não informado';

            // Definir classe do status
            const statusClass = getStatusClass(servico.status);

            // Formatar orçamento
            const orcamento = servico.orcamento ?
                new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(servico.orcamento) : 'A combinar';

            row.innerHTML = `
                <td>
                    <strong>${servico.titulo || 'Sem título'}</strong>
                    <br>
                    <small class="text-muted">Por: ${servico.prestador_nome || 'Prestador'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${servico.categoria || 'Sem categoria'}</span>
                </td>
                <td>${orcamento}</td>
                <td><span class="badge ${statusClass}">${getStatusText(servico.status)}</span></td>
                <td>
                    <small>${data_formatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhesServicoPublicado(${servico.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="contratarServico(${servico.id})" 
                                title="Contratar">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        const tableBody = document.getElementById('servicosTable');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Erro ao carregar serviços: ${error.message}
                        <br>
                        <small class="text-muted">Verifique sua conexão e tente novamente.</small>
                    </td>
                </tr>
            `;
        }
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
        
        // Verificar se a resposta é OK
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
        }
        
        // Verificar se o conteúdo é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido');
        }
        
        const data = await response.json();

        const tableBody = document.getElementById('solicitacoesTable');
        if (!tableBody) return;
        
        tableBody.innerHTML = ''; // Limpar tabela

        // Verificar se há erro na resposta
        if (!data.success) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        ${data.message || 'Erro ao carregar solicitações'}
                        <br>
                        <a href="./servico/nova-solicitacao.html" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Nova Solicitação
                        </a>
                    </td>
                </tr>
            `;
            return;
        }

        // Verificar se não há solicitações
        if (!data.solicitacoes || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de serviço encontrada.
                        <br>
                        <a href="./servico/nova-solicitacao.html" class="btn btn-success btn-sm mt-2">
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

            // Formatar data de criação
            const dataFormatada = solicitacao.data_criacao ? 
                new Date(solicitacao.data_criacao).toLocaleDateString('pt-BR') : 
                'Não informado';
            
            // Formatar prazo desejado
            let prazoFormatado = 'Não definido';
            if (solicitacao.prazo_desejado) {
                try {
                    prazoFormatado = new Date(solicitacao.prazo_desejado).toLocaleDateString('pt-BR');
                } catch (e) {
                    prazoFormatado = solicitacao.prazo_desejado;
                }
            }
            
            // Formatar orçamento
            const orcamento = solicitacao.orcamento_maximo ?
                new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(solicitacao.orcamento_maximo) :
                'Flexível';

            row.innerHTML = `
                <td>
                    <strong>${solicitacao.titulo || 'Sem título'}</strong>
                    <br>
                    <small class="text-muted">${solicitacao.cidade || 'Não informado'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${solicitacao.categoria || 'Sem categoria'}</span>
                </td>
                <td>
                    <small>${prazoFormatado}</small>
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
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Erro ao carregar solicitações: ${error.message}
                        <br>
                        <small class="text-muted">Verifique sua conexão e tente novamente.</small>
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

// Funções para ações das solicitações
function verDetalhes(id) {
    // Implementar modal ou página de detalhes
    alert(`Ver detalhes da solicitação ${id} - Funcionalidade em desenvolvimento`);
}

function editarSolicitacao(id) {
    // Redirecionar para página de edição
    window.location.href = `./servico/altera-solicitacao.html?id=${id}`;
}

async function excluirSolicitacao(id) {
    if (!confirm('Tem certeza que deseja excluir esta solicitação de serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('solicitacao_id', id);

        const response = await fetch('../php/servico/apaga-solicitacao.php', {
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

// Função para carregar contratos do cliente
async function carregarContratos() {
    try {
        const response = await fetch('../php/servico/listar-contratos-cliente.php');
        
        // Verificar se a resposta é OK
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
        }
        
        // Verificar se o conteúdo é JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text);
            throw new Error('Resposta do servidor não é JSON válido');
        }
        
        const data = await response.json();

        const tableBody = document.getElementById('contratosTable');
        if (!tableBody) return;
        
        tableBody.innerHTML = ''; // Limpar tabela

        // Verificar se há erro na resposta
        if (!data.success) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        ${data.message || 'Erro ao carregar contratos'}
                    </td>
                </tr>
            `;
            return;
        }

        // Verificar se não há contratos
        if (!data.contratos || data.contratos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum contrato encontrado.
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada contrato à tabela
        data.contratos.forEach(contrato => {
            const row = document.createElement('tr');

            // Formatar data
            const dataFormatada = contrato.created_at ? 
                new Date(contrato.created_at).toLocaleDateString('pt-BR') : 
                'Não informado';

            // Definir badge de status
            let statusBadge = '';
            if (contrato.status === 'completed') {
                statusBadge = '<span class="badge bg-success">Concluído</span>';
            } else if (contrato.status === 'active') {
                statusBadge = '<span class="badge bg-primary">Ativo</span>';
            } else if (contrato.status === 'cancelled') {
                statusBadge = '<span class="badge bg-danger">Cancelado</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">Pendente</span>';
            }

            // Gerar coluna de avaliação baseada no status
            let avaliacaoHtml = '';
            if (contrato.status === 'completed' && contrato.ja_avaliado == 0) {
                avaliacaoHtml = `
                    <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
                       class="btn btn-sm btn-warning">
                        <i class="bi bi-star-fill"></i> Avaliar
                    </a>
                `;
            } else if (contrato.ja_avaliado == 1) {
                avaliacaoHtml = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Já avaliado</span>';
            } else {
                avaliacaoHtml = '<span class="badge bg-secondary">Aguardando conclusão</span>';
            }

            row.innerHTML = `
                <td>
                    <strong>${contrato.titulo || 'Sem título'}</strong>
                </td>
                <td>
                    ${contrato.prestador_nome || 'Prestador não informado'}
                    <br>
                    <small class="text-muted">${contrato.specialty || 'Sem especialidade'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${contrato.categoria || 'Sem categoria'}</span>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <small>${dataFormatada}</small>
                </td>
                <td>${avaliacaoHtml}</td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar contratos:', error);
        const tableBody = document.getElementById('contratosTable');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Erro ao carregar contratos: ${error.message}
                        <br>
                        <small class="text-muted">Verifique sua conexão e tente novamente.</small>
                    </td>
                </tr>
            `;
        }
    }
}

// Funções auxiliares para status dos serviços publicados
function getStatusText(status) {
    const statusTexts = {
        'ativo': 'Ativo',
        'inativo': 'Inativo',
        'pausado': 'Pausado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações dos serviços publicados (visualização pelos clientes)
function verDetalhesServicoPublicado(id) {
    alert(`Ver detalhes do serviço ${id} - Funcionalidade em desenvolvimento`);
}

function contratarServico(id) {
    alert(`Contratar serviço ${id} - Funcionalidade em desenvolvimento`);
}

// Tab switching function
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
    });

    // Remove active class from all tabs
    const tabItems = document.querySelectorAll('.tab-nav-item');
    tabItems.forEach(item => {
        item.classList.remove('active');
    });

    // Show selected tab content
    const selectedTab = document.getElementById(`${tabName}-tab`);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    }

    // Add active class to the clicked tab button
    tabItems.forEach(item => {
        if (item.getAttribute('data-tab') === tabName) {
            item.classList.add('active');
        }
    });
}

// Função para alternar filtros avançados
function toggleFiltrosAvancados() {
    const filtrosAvancados = document.getElementById('filtrosAvancados');
    if (filtrosAvancados) {
        if (filtrosAvancados.style.display === 'none' || filtrosAvancados.style.display === '') {
            filtrosAvancados.style.display = 'block';
        } else {
            filtrosAvancados.style.display = 'none';
        }
    }
}

