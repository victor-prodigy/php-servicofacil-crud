document.addEventListener('DOMContentLoaded', function () {
  // Elementos DOM
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const navLinks = document.querySelectorAll('.nav-link[data-section]');
  const contentSections = document.querySelectorAll('.content-section');
  const pageTitle = document.getElementById('pageTitle');
  const logoutBtn = document.getElementById('logoutBtn');
  const refreshBtn = document.getElementById('refreshBtn');
  const currentDate = document.getElementById('currentDate');

  // Verificar autenticação
  checkAuth();

  // Inicializar
  init();

  function init() {
    updateCurrentDate();
    loadDashboardData();
    setupEventListeners();
    showSection('overview'); // Mostrar seção de visão geral por padrão
  }

  function setupEventListeners() {
    // Toggle sidebar
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }

    // Navigation
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const section = link.dataset.section;
        showSection(section);

        // Update active nav
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Close sidebar on mobile
        if (window.innerWidth < 992) {
          sidebar.classList.remove('show');
        }
      });
    });

    // Logout
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      logout();
    });

    // Refresh
    refreshBtn.addEventListener('click', () => {
      loadDashboardData();
      const activeSection = document.querySelector('.content-section.active');
      if (activeSection) {
        if (activeSection.id === 'usersSection') {
          loadUsersData();
        } else if (activeSection.id === 'servicesSection') {
          loadServicesData();
        }
      }
    });

    // Search and filter for services
    const searchServices = document.getElementById('searchServices');
    const statusFilter = document.getElementById('statusFilter');

    if (searchServices) {
      searchServices.addEventListener('input', debounce(() => {
        loadServicesData();
      }, 300));
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', () => {
        loadServicesData();
      });
    }

    // Search and filter for users
    const searchUsers = document.getElementById('searchUsers');
    const tipoFilter = document.getElementById('tipoFilter');
    const statusFilterUsers = document.getElementById('statusFilterUsers');

    if (searchUsers) {
      searchUsers.addEventListener('input', debounce(() => {
        loadUsersData();
      }, 300));
    }

    if (tipoFilter) {
      tipoFilter.addEventListener('change', () => {
        loadUsersData();
      });
    }

    if (statusFilterUsers) {
      statusFilterUsers.addEventListener('change', () => {
        loadUsersData();
      });
    }

    // Search and filter for postagens
    const searchPostagens = document.getElementById('searchPostagens');
    const statusFilterPostagens = document.getElementById('statusFilterPostagens');
    const disponibilidadeFilterPostagens = document.getElementById('disponibilidadeFilterPostagens');

    if (searchPostagens) {
      searchPostagens.addEventListener('input', debounce(() => {
        loadPostagensData();
      }, 300));
    }

    if (statusFilterPostagens) {
      statusFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    if (disponibilidadeFilterPostagens) {
      disponibilidadeFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    // Confirm remove postagem button
    const confirmRemovePostagemBtn = document.getElementById('confirmRemovePostagemBtn');
    if (confirmRemovePostagemBtn) {
      confirmRemovePostagemBtn.addEventListener('click', () => {
        const serviceId = confirmRemovePostagemBtn.dataset.serviceId;
        const motivo = document.getElementById('motivoRemocao').value || 'Conteúdo inapropriado';
        removePostagem(serviceId, motivo);
      });
    }
  }

  async function checkAuth() {
    try {
      // Mostrar loading enquanto verifica autenticação
      const loadingDiv = document.createElement('div');
      loadingDiv.id = 'loadingAuth';
      loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
      loadingDiv.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
      loadingDiv.style.zIndex = '9999';
      loadingDiv.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Verificando acesso...</span>
          </div>
          <h5>Verificando permissões de administrador...</h5>
        </div>
      `;
      document.body.appendChild(loadingDiv);

      const response = await fetch('../php/admin/verificar-auth.php');
      const data = await response.json();

        // Remover loading
      document.getElementById('loadingAuth')?.remove();

      if (!data.authenticated) {
        // Mostrar alerta antes de redirecionar
        alert(data.usuario.message || 'Acesso negado. Você não tem permissões de administrador.');
        window.location.href = 'login/administrador-signin.html';
        return;
      }

      // Update user info
      document.getElementById('userName').textContent = data.usuario.nome;
      document.getElementById('userEmail').textContent = data.usuario.email;

      // Log de sucesso
      console.log('✅ Acesso autorizado para administrador:', data.usuario.nome);

    } catch (error) {
      console.error('❌ Erro na verificação de autenticação:', error);

      // Remover loading se houver erro
      document.getElementById('loadingAuth')?.remove();

      alert('Erro ao verificar autenticação. Redirecionando para login...');
      window.location.href = 'login/administrador-signin.html';
    }
  }

  async function loadDashboardData() {
    try {
      const response = await fetch('../php/admin/administrador-dashboard.php?action=stats');
      const data = await response.json();

      if (data.success) {
        const stats = data.data;
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('totalServices').textContent = stats.total_services || 0;
        document.getElementById('totalProposals').textContent = stats.total_proposals || 0;
        document.getElementById('totalContracts').textContent = stats.total_contracts || 0;
      }
    } catch (error) {
      console.error('Erro ao carregar estatísticas:', error);
    }
  }

  // ========== USER MANAGEMENT FUNCTIONS ==========
  let allUsers = [];

  async function loadUsersData() {
    try {
      const response = await fetch('../php/admin/listar-usuarios.php');
      const data = await response.json();

      if (data.success) {
        allUsers = data.usuarios;
        updateUsersStatistics(data.estatisticas);
        filterAndRenderUsers();
      } else {
        console.error('Erro ao carregar usuários:', data.error);
        alert('Erro ao carregar usuários: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
      alert('Erro de conexão ao carregar usuários');
    }
  }

  function updateUsersStatistics(stats) {
    document.getElementById('statTotalUsuarios').textContent = stats.total_usuarios || 0;
    document.getElementById('statTotalClientes').textContent = stats.total_clientes || 0;
    document.getElementById('statTotalPrestadores').textContent = stats.total_prestadores || 0;
    document.getElementById('statUsuariosAtivos').textContent = stats.usuarios_ativos || 0;
  }

  function filterAndRenderUsers() {
    const searchTerm = document.getElementById('searchUsers')?.value.toLowerCase() || '';
    const tipoFilter = document.getElementById('tipoFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilterUsers')?.value || '';

    let filteredUsers = allUsers.filter(user => {
      const matchesSearch = !searchTerm || 
        user.nome.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        (user.instagram && user.instagram.toLowerCase().includes(searchTerm));
      
      const matchesTipo = !tipoFilter || user.tipo_usuario === tipoFilter;
      const matchesStatus = !statusFilter || user.status === statusFilter;

      return matchesSearch && matchesTipo && matchesStatus;
    });

    renderUsersTable(filteredUsers);
  }

  function renderUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="8" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhum usuário encontrado</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = users.map(user => `
      <tr>
        <td>#${user.user_id}</td>
        <td>
          <div>
            <strong>${user.nome || ''}</strong>
            ${user.verificado ? '<br><small class="text-success"><i class="fas fa-check-circle"></i> Verificado</small>' : ''}
          </div>
        </td>
        <td>${user.email || ''}</td>
        <td>${user.instagram || 'Não informado'}</td>
        <td>
          <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">
            ${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}
          </span>
        </td>
        <td>
          <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">
            ${user.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <small>${user.data_cadastro || ''}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewUserDetails(${user.user_id})" title="Ver detalhes">
              <i class="fas fa-eye"></i>
            </button>
            ${user.status === 'ativo' 
              ? `<button class="btn btn-outline-warning" onclick="manageUser(${user.user_id}, 'desativar')" title="Desativar">
                   <i class="fas fa-ban"></i>
                 </button>`
              : `<button class="btn btn-outline-success" onclick="manageUser(${user.user_id}, 'ativar')" title="Ativar">
                   <i class="fas fa-check"></i>
                 </button>`
            }
            <button class="btn btn-outline-danger" onclick="manageUser(${user.user_id}, 'excluir')" title="Excluir">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Global functions for user management
  window.viewUserDetails = async function (userId) {
    try {
      const response = await fetch(`../php/admin/detalhes-usuario.php?user_id=${userId}`);
      const data = await response.json();

      if (data.success) {
        const user = data.usuario;
        const stats = data.estatisticas || {};
        
        let statsHtml = '';
        if (user.tipo_usuario === 'cliente') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Cliente</h6>
                <p><strong>Total de Solicitações:</strong> ${stats.total_solicitacoes || 0}</p>
                <p><strong>Solicitações Pendentes:</strong> ${stats.solicitacoes_pendentes || 0}</p>
                <p><strong>Solicitações Concluídas:</strong> ${stats.solicitacoes_concluidas || 0}</p>
                <p><strong>Propostas Recebidas:</strong> ${stats.propostas_recebidas || 0}</p>
              </div>
            </div>
          `;
        } else if (user.tipo_usuario === 'prestador') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Prestador</h6>
                <p><strong>Total de Propostas:</strong> ${stats.total_propostas || 0}</p>
                <p><strong>Propostas Pendentes:</strong> ${stats.propostas_pendentes || 0}</p>
                <p><strong>Propostas Aceitas:</strong> ${stats.propostas_aceitas || 0}</p>
                <p><strong>Valor Médio:</strong> R$ ${parseFloat(stats.valor_medio_propostas || 0).toFixed(2)}</p>
              </div>
            </div>
          `;
        }

        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
              <p><strong>Nome:</strong> ${user.nome}</p>
              <p><strong>Email:</strong> ${user.email}</p>
              <p><strong>Telefone:</strong> ${user.telefone}</p>
              <p><strong>Instagram:</strong> ${user.instagram || 'Não informado'}</p>
              <p><strong>Tipo:</strong> <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}</span></p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-info-circle me-2"></i>Informações da Conta</h6>
              <p><strong>Status:</strong> <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">${user.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></p>
              <p><strong>Verificado:</strong> ${user.verificado ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-warning">Não</span>'}</p>
              <p><strong>Data de Cadastro:</strong> ${user.data_cadastro}</p>
              <p><strong>Última Atualização:</strong> ${user.ultima_atualizacao}</p>
            </div>
          </div>
          ${user.especialidade ? `<p><strong>Especialidade:</strong> ${user.especialidade}</p>` : ''}
          ${user.localizacao ? `<p><strong>Localização:</strong> ${user.localizacao}</p>` : ''}
          <hr>
          ${statsHtml}
        `;

        document.getElementById('userDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do usuário: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do usuário');
    }
  };

  window.manageUser = async function (userId, acao) {
    const acoes = {
      'ativar': 'ativar',
      'desativar': 'desativar',
      'excluir': 'excluir permanentemente'
    };

    const confirmMessage = acao === 'excluir' 
      ? 'Tem certeza que deseja EXCLUIR PERMANENTEMENTE este usuário? Esta ação não pode ser desfeita!'
      : `Tem certeza que deseja ${acoes[acao]} este usuário?`;

    if (!confirm(confirmMessage)) {
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
        alert(data.message);
        loadUsersData(); // Recarregar lista de usuários
      } else {
        alert('Erro: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao processar ação');
    }
  };

  // ========== SERVICES MANAGEMENT FUNCTIONS ==========
  async function loadServicesData() {
    try {
      const search = document.getElementById('searchServices')?.value || '';
      const status = document.getElementById('statusFilter')?.value || '';

      const params = new URLSearchParams({
        action: 'services',
        search: search,
        status: status
      });

      const response = await fetch(`../php/admin/administrador-dashboard.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderServicesTable(data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
    }
  }

  function renderServicesTable(services) {
    const tbody = document.querySelector('#servicesTable tbody');
    if (!tbody) {
      // Create tbody if it doesn't exist
      const table = document.getElementById('servicesTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderServicesTable(services);
    }

    if (services.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Nenhum serviço encontrado</p>
                    </td>
                </tr>
            `;
      return;
    }

    tbody.innerHTML = services.map(service => `
            <tr>
                <td>#${service.request_id}</td>
                <td>
                    <div>
                        <strong>${service.cliente_nome}</strong>
                        <br>
                        <small class="text-muted">${service.cliente_email}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${service.titulo}</strong>
                        <br>
                        <small class="text-muted">${truncateText(service.descricao, 50)}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-secondary">${service.categoria}</span>
                </td>
                <td>
                    <small>${service.cidade}</small>
                </td>
                <td>
                    <span class="badge ${getStatusClass(service.status)}">${service.status}</span>
                </td>
                <td>
                    <small>${formatDate(service.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewServiceDetails(${service.request_id})" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
  }

  // Global functions for service management
  window.viewServiceDetails = async function (serviceId) {
    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?action=details&id=${serviceId}`);
      const data = await response.json();

      if (data.success) {
        const service = data.data;
        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações do Cliente</h6>
              <p><strong>Nome:</strong> ${service.cliente_nome}</p>
              <p><strong>Email:</strong> ${service.cliente_email}</p>
              <p><strong>Telefone:</strong> ${service.cliente_telefone || 'Não informado'}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-briefcase me-2"></i>Informações do Serviço</h6>
              <p><strong>Status:</strong> <span class="badge ${getStatusClass(service.status)}">${service.status}</span></p>
              <p><strong>Categoria:</strong> ${service.categoria}</p>
              <p><strong>Data de Criação:</strong> ${formatDate(service.created_at)}</p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-info-circle me-2"></i>Detalhes do Serviço</h6>
              <p><strong>Título:</strong> ${service.titulo}</p>
              <p><strong>Descrição:</strong></p>
              <p class="bg-light p-3 rounded">${service.descricao}</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-map-marker-alt me-2"></i>Localização</h6>
              <p><strong>Endereço:</strong> ${service.endereco}</p>
              <p><strong>Cidade:</strong> ${service.cidade}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-dollar-sign me-2"></i>Orçamento e Prazo</h6>
              <p><strong>Orçamento Máximo:</strong> R$ ${parseFloat(service.orcamento_maximo || 0).toFixed(2)}</p>
              <p><strong>Prazo Desejado:</strong> ${service.prazo_desejado ? formatDate(service.prazo_desejado) : 'Não definido'}</p>
            </div>
          </div>
          ${service.observacoes ? `
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-sticky-note me-2"></i>Observações</h6>
              <p class="bg-light p-3 rounded">${service.observacoes}</p>
            </div>
          </div>
          ` : ''}
        `;

        document.getElementById('serviceDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do serviço');
    }
  };

  function showSection(sectionName) {
    // Hide all sections
    contentSections.forEach(section => {
      section.classList.remove('active');
    });

    // Show target section
    const targetSection = document.getElementById(sectionName + 'Section');
    if (targetSection) {
      targetSection.classList.add('active');
    }

    // Update page title
    const titles = {
      overview: 'Visão Geral',
      services: 'Gerenciar Serviços',
      users: 'Gerenciar Usuários',
      proposals: 'Propostas',
      contracts: 'Contratos',
      reviews: 'Avaliações',
      postagens: 'Gerenciar Postagens'
    };

    pageTitle.textContent = titles[sectionName] || 'Dashboard';

    // Load section specific data
    if (sectionName === 'services') {
      loadServicesData();
    } else if (sectionName === 'users') {
      loadUsersData();
    } else if (sectionName === 'postagens') {
      loadPostagensData();
    }
  }

  function getStatusClass(status) {
    const classes = {
      'Pendente': 'bg-warning',
      'Em Andamento': 'bg-info',
      'Concluído': 'bg-success',
      'Cancelado': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
  }

  function truncateText(text, length) {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  }

  function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR');
  }

  function updateCurrentDate() {
    const now = new Date();
    currentDate.textContent = now.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // ========== POSTAGENS MANAGEMENT FUNCTIONS ==========
  async function loadPostagensData() {
    try {
      const search = document.getElementById('searchPostagens')?.value || '';
      const status = document.getElementById('statusFilterPostagens')?.value || '';
      const disponibilidade = document.getElementById('disponibilidadeFilterPostagens')?.value || '';

      const params = new URLSearchParams({
        search: search,
        status: status,
        disponibilidade: disponibilidade
      });

      const response = await fetch(`../php/admin/listar-postagens.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderPostagensTable(data.postagens);
      } else {
        console.error('Erro ao carregar postagens:', data.message);
      }
    } catch (error) {
      console.error('Erro ao carregar postagens:', error);
    }
  }

  function renderPostagensTable(postagens) {
    const tbody = document.querySelector('#postagensTable tbody');
    if (!tbody) {
      const table = document.getElementById('postagensTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderPostagensTable(postagens);
    }

    if (postagens.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhuma postagem encontrada</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = postagens.map(postagem => `
      <tr>
        <td>#${postagem.service_id}</td>
        <td>
          <div>
            <strong>${postagem.prestador_nome}</strong>
            <br>
            <small class="text-muted">${postagem.prestador_email}</small>
          </div>
        </td>
        <td>
          <div>
            <strong>${postagem.titulo}</strong>
            <br>
            <small class="text-muted">${truncateText(postagem.descricao, 50)}</small>
          </div>
        </td>
        <td>
          <span class="badge bg-secondary">${postagem.categoria}</span>
        </td>
        <td>
          <strong>R$ ${postagem.preco}</strong>
        </td>
        <td>
          <span class="badge ${postagem.status === 'ativo' ? 'bg-success' : 'bg-secondary'}">
            ${postagem.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <span class="badge ${postagem.disponibilidade === 'disponivel' ? 'bg-info' : 'bg-warning'}">
            ${postagem.disponibilidade === 'disponivel' ? 'Disponível' : 'Indisponível'}
          </span>
        </td>
        <td>
          <small>${postagem.created_at}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-danger" onclick="showRemovePostagemModal(${postagem.service_id}, '${escapeHtml(postagem.titulo)}')" title="Remover">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  window.showRemovePostagemModal = function (serviceId, titulo) {
    const modal = document.getElementById('removePostagemModal');
    const confirmBtn = document.getElementById('confirmRemovePostagemBtn');
    confirmBtn.dataset.serviceId = serviceId;
    
    // Atualizar texto do modal se necessário
    const modalBody = modal.querySelector('.modal-body p');
    if (modalBody) {
      modalBody.textContent = `Tem certeza que deseja remover a postagem "${titulo}"? Esta ação não pode ser desfeita.`;
    }
    
    new bootstrap.Modal(modal).show();
  };

  async function removePostagem(serviceId, motivo) {
    try {
      const formData = new FormData();
      formData.append('service_id', serviceId);
      formData.append('motivo', motivo);

      const response = await fetch('../php/admin/remover-postagem.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Postagem removida com sucesso! A ação foi registrada no histórico administrativo.');
        bootstrap.Modal.getInstance(document.getElementById('removePostagemModal')).hide();
        loadPostagensData();
      } else {
        alert('Erro ao remover postagem: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao remover postagem');
    }
  }

  async function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
      try {
        // Clear session
        await fetch('../php/logout.php', { method: 'POST' });
        window.location.href = 'login/administrador-signin.html';
      } catch (error) {
        // Force redirect even if logout fails
        window.location.href = 'login/administrador-signin.html';
      }
    }
  }
});

