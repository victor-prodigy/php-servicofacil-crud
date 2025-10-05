// Dashboard Administrativo - JavaScript
document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard administrativo carregado');
    checkAuthentication();
});

// Verificar autenticação administrativa
async function checkAuthentication() {
    try {
        const response = await fetch('../php/admin/admin-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para login
            alert(data.message);
            window.location.href = './login/admin-login.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza informações
        document.getElementById('adminName').textContent = data.usuario.nome;
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('dashboardContent').style.display = 'block';

        // Carregar dados do dashboard
        carregarDashboard();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/admin-login.html';
    }
}

// Carregar dados do dashboard
async function carregarDashboard() {
    await carregarUsuarios();
    carregarAtividadesRecentes();
    carregarEstatisticasSistema();
}

// Carregar lista de usuários
async function carregarUsuarios() {
    try {
        const response = await fetch('../php/admin/listar-usuarios.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Erro ao carregar usuários');
        }

        // Atualizar estatísticas
        document.getElementById('totalUsuarios').textContent = data.estatisticas.total_usuarios;
        document.getElementById('totalClientes').textContent = data.estatisticas.total_clientes;
        document.getElementById('totalPrestadores').textContent = data.estatisticas.total_prestadores;
        document.getElementById('usuariosAtivos').textContent = data.estatisticas.usuarios_ativos;

        // Armazenar dados globalmente para filtros
        window.todosUsuarios = data.usuarios;
        
        // Renderizar tabela
        renderizarTabelaUsuarios(data.usuarios);

    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        mostrarErroTabela('Erro ao carregar usuários: ' + error.message);
    }
}

// Renderizar tabela de usuários
function renderizarTabelaUsuarios(usuarios) {
    const tableBody = document.getElementById('usuariosTable');
    tableBody.innerHTML = '';

    if (usuarios.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="bi bi-inbox"></i> 
                    Nenhum usuário encontrado.
                </td>
            </tr>
        `;
        return;
    }

    usuarios.forEach(usuario => {
        const row = document.createElement('tr');
        
        // Definir classes CSS baseadas no tipo e status
        const tipoClass = usuario.tipo_usuario === 'cliente' ? 'bg-success' : 'bg-info';
        const statusClass = usuario.status === 'ativo' ? 'bg-success' : 'bg-warning text-dark';
        
        // Avatar com iniciais
        const iniciais = usuario.nome.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div class="user-avatar me-3">
                        ${iniciais}
                    </div>
                    <div>
                        <strong>${usuario.nome}</strong>
                        <br>
                        <small class="text-muted">${usuario.email}</small>
                        ${usuario.telefone !== 'Não informado' ? `<br><small class="text-muted"><i class="bi bi-telephone"></i> ${usuario.telefone}</small>` : ''}
                    </div>
                </div>
            </td>
            <td>
                <span class="badge ${tipoClass}">
                    <i class="bi bi-${usuario.tipo_usuario === 'cliente' ? 'person' : 'tools'}"></i>
                    ${usuario.tipo_usuario.charAt(0).toUpperCase() + usuario.tipo_usuario.slice(1)}
                </span>
                ${usuario.especialidade ? `<br><small class="text-muted">${usuario.especialidade}</small>` : ''}
            </td>
            <td>
                <span class="badge ${statusClass}">
                    ${usuario.status === 'ativo' ? 'Ativo' : 'Inativo'}
                </span>
                ${usuario.verificado ? '<br><i class="bi bi-shield-check text-success" title="Verificado"></i>' : '<br><i class="bi bi-shield-x text-warning" title="Não verificado"></i>'}
            </td>
            <td>
                <small>${usuario.data_cadastro}</small>
            </td>
            <td>
                <small>${usuario.ultima_atualizacao}</small>
                ${usuario.tipo_usuario === 'cliente' ? 
                    `<br><small class="text-muted">${usuario.total_solicitacoes} solicitações</small>` : 
                    `<br><small class="text-muted">${usuario.total_propostas} propostas</small>`
                }
            </td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            onclick="verDetalhesUsuario(${usuario.user_id})" 
                            title="Ver detalhes">
                        <i class="bi bi-eye"></i>
                    </button>
                    ${usuario.status === 'ativo' ? 
                        `<button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="gerenciarUsuario(${usuario.user_id}, 'desativar')" 
                                title="Desativar">
                            <i class="bi bi-pause-circle"></i>
                        </button>` :
                        `<button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="gerenciarUsuario(${usuario.user_id}, 'ativar')" 
                                title="Ativar">
                            <i class="bi bi-play-circle"></i>
                        </button>`
                    }
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="confirmarExclusao(${usuario.user_id}, '${usuario.nome}')" 
                            title="Excluir">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tableBody.appendChild(row);
    });
}

// Filtrar usuários
function filtrarUsuarios() {
    if (!window.todosUsuarios) return;
    
    const filtroTipo = document.getElementById('filtroTipo').value;
    const filtroStatus = document.getElementById('filtroStatus').value;
    const busca = document.getElementById('buscarUsuario').value.toLowerCase();
    
    let usuariosFiltrados = window.todosUsuarios.filter(usuario => {
        // Filtro por tipo
        if (filtroTipo && usuario.tipo_usuario !== filtroTipo) return false;
        
        // Filtro por status
        if (filtroStatus && usuario.status !== filtroStatus) return false;
        
        // Filtro por busca
        if (busca && 
            !usuario.nome.toLowerCase().includes(busca) && 
            !usuario.email.toLowerCase().includes(busca)) return false;
        
        return true;
    });
    
    renderizarTabelaUsuarios(usuariosFiltrados);
}

// Ver detalhes do usuário
async function verDetalhesUsuario(userId) {
    try {
        const response = await fetch(`../php/admin/detalhes-usuario.php?user_id=${userId}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error);
        }
        
        const usuario = data.usuario;
        const stats = data.estatisticas;
        
        let conteudoModal = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Informações Pessoais</h6>
                    <p><strong>Nome:</strong> ${usuario.nome}</p>
                    <p><strong>Email:</strong> ${usuario.email}</p>
                    <p><strong>Telefone:</strong> ${usuario.telefone}</p>
                    <p><strong>Tipo:</strong> 
                        <span class="badge ${usuario.tipo_usuario === 'cliente' ? 'bg-success' : 'bg-info'}">
                            ${usuario.tipo_usuario.charAt(0).toUpperCase() + usuario.tipo_usuario.slice(1)}
                        </span>
                    </p>
                    <p><strong>Status:</strong> 
                        <span class="badge ${usuario.status === 'ativo' ? 'bg-success' : 'bg-warning text-dark'}">
                            ${usuario.status === 'ativo' ? 'Ativo' : 'Inativo'}
                        </span>
                    </p>
                    <p><strong>Verificado:</strong> ${usuario.verificado ? '✅ Sim' : '❌ Não'}</p>
                    <p><strong>Cadastro:</strong> ${usuario.data_cadastro}</p>
                    <p><strong>Última atualização:</strong> ${usuario.ultima_atualizacao}</p>
                    ${usuario.especialidade ? `<p><strong>Especialidade:</strong> ${usuario.especialidade}</p>` : ''}
                    ${usuario.localizacao ? `<p><strong>Localização:</strong> ${usuario.localizacao}</p>` : ''}
                </div>
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2">Estatísticas de Atividade</h6>
        `;
        
        if (usuario.tipo_usuario === 'cliente') {
            conteudoModal += `
                <p><strong>Total de solicitações:</strong> ${stats.total_solicitacoes || 0}</p>
                <p><strong>Pendentes:</strong> ${stats.solicitacoes_pendentes || 0}</p>
                <p><strong>Concluídas:</strong> ${stats.solicitacoes_concluidas || 0}</p>
                <p><strong>Propostas recebidas:</strong> ${stats.propostas_recebidas || 0}</p>
            `;
            
            if (stats.ultimas_solicitacoes && stats.ultimas_solicitacoes.length > 0) {
                conteudoModal += `
                    <h6 class="mt-3">Últimas Solicitações</h6>
                    <div class="list-group">
                `;
                stats.ultimas_solicitacoes.forEach(sol => {
                    conteudoModal += `
                        <div class="list-group-item">
                            <strong>${sol.titulo}</strong> - ${sol.categoria}
                            <br><small class="text-muted">${new Date(sol.created_at).toLocaleDateString('pt-BR')}</small>
                        </div>
                    `;
                });
                conteudoModal += `</div>`;
            }
            
        } else if (usuario.tipo_usuario === 'prestador') {
            conteudoModal += `
                <p><strong>Total de propostas:</strong> ${stats.total_propostas || 0}</p>
                <p><strong>Pendentes:</strong> ${stats.propostas_pendentes || 0}</p>
                <p><strong>Aceitas:</strong> ${stats.propostas_aceitas || 0}</p>
                <p><strong>Valor médio:</strong> R$ ${stats.valor_medio_propostas ? parseFloat(stats.valor_medio_propostas).toFixed(2) : '0,00'}</p>
            `;
            
            if (stats.ultimas_propostas && stats.ultimas_propostas.length > 0) {
                conteudoModal += `
                    <h6 class="mt-3">Últimas Propostas</h6>
                    <div class="list-group">
                `;
                stats.ultimas_propostas.forEach(prop => {
                    conteudoModal += `
                        <div class="list-group-item">
                            <strong>${prop.titulo}</strong> - R$ ${parseFloat(prop.amount).toFixed(2)}
                            <br><small class="text-muted">${new Date(prop.submitted_at).toLocaleDateString('pt-BR')}</small>
                        </div>
                    `;
                });
                conteudoModal += `</div>`;
            }
        }
        
        conteudoModal += `
                </div>
            </div>
        `;
        
        document.getElementById('detalhesUsuarioContent').innerHTML = conteudoModal;
        
        const modal = new bootstrap.Modal(document.getElementById('detalhesUsuarioModal'));
        modal.show();
        
    } catch (error) {
        console.error('Erro ao carregar detalhes:', error);
        alert('Erro ao carregar detalhes do usuário: ' + error.message);
    }
}

// Gerenciar usuário (ativar/desativar)
async function gerenciarUsuario(userId, acao) {
    if (!confirm(`Tem certeza que deseja ${acao} este usuário?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('acao', acao);
        
        const response = await fetch('../php/admin/gerenciar-usuario.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ ${data.message}`);
            carregarUsuarios(); // Recarregar lista
        } else {
            alert(`❌ ${data.error}`);
        }
        
    } catch (error) {
        console.error('Erro ao gerenciar usuário:', error);
        alert('❌ Erro ao processar ação. Tente novamente.');
    }
}

// Confirmar exclusão
function confirmarExclusao(userId, nomeUsuario) {
    if (confirm(`⚠️ ATENÇÃO: Tem certeza que deseja EXCLUIR PERMANENTEMENTE o usuário "${nomeUsuario}"?\n\nEsta ação não pode ser desfeita!`)) {
        gerenciarUsuario(userId, 'excluir');
    }
}

// Carregar atividades recentes (placeholder)
function carregarAtividadesRecentes() {
    const container = document.getElementById('atividadesRecentes');
    container.innerHTML = `
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-person-plus text-success me-2"></i>
            <small>Novo cliente cadastrado - João Silva - 2 horas atrás</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-send text-info me-2"></i>
            <small>Nova proposta enviada - Maria Santos - 4 horas atrás</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-check-circle text-success me-2"></i>
            <small>Solicitação concluída - Pedro Lima - 6 horas atrás</small>
        </div>
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-person-plus text-success me-2"></i>
            <small>Novo prestador cadastrado - Ana Costa - 8 horas atrás</small>
        </div>
    `;
}

// Carregar estatísticas do sistema (placeholder)
function carregarEstatisticasSistema() {
    // Simular dados de progresso
    document.getElementById('progressSolicitacoes').style.width = '85%';
    document.getElementById('progressSolicitacoes').textContent = '85%';
    
    document.getElementById('progressPropostas').style.width = '72%';
    document.getElementById('progressPropostas').textContent = '72%';
    
    document.getElementById('progressContratos').style.width = '58%';
    document.getElementById('progressContratos').textContent = '58%';
}

// Mostrar erro na tabela
function mostrarErroTabela(mensagem) {
    const tableBody = document.getElementById('usuariosTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-danger">
                <i class="bi bi-exclamation-triangle"></i> 
                ${mensagem}
            </td>
        </tr>
    `;
}

// Logout
function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        fetch('../php/logout.php')
            .then(() => {
                window.location.href = './login/admin-login.html';
            })
            .catch(error => {
                console.error('Erro ao fazer logout:', error);
                window.location.href = './login/admin-login.html';
            });
    }
}