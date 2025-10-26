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
    showSection('services'); // Mostrar seção de serviços por padrão
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
      loadServicesData();
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
                        <button class="btn btn-outline-warning" onclick="editService(${service.request_id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteService(${service.request_id})" title="Remover">
                            <i class="fas fa-trash"></i>
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
        document.getElementById('editServiceBtn').onclick = () => editService(serviceId);
        new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do serviço');
    }
  };

  window.editService = async function (serviceId) {
    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?action=details&id=${serviceId}`);
      const data = await response.json();

      if (data.success) {
        const service = data.data;

        // Preencher formulário
        document.getElementById('editServiceId').value = service.request_id;
        document.getElementById('editTitulo').value = service.titulo;
        document.getElementById('editCategoria').value = service.categoria;
        document.getElementById('editDescricao').value = service.descricao;
        document.getElementById('editEndereco').value = service.endereco;
        document.getElementById('editCidade').value = service.cidade;
        document.getElementById('editOrcamento').value = service.orcamento_maximo || '';
        document.getElementById('editPrazo').value = service.prazo_desejado || '';
        document.getElementById('editStatus').value = service.status;
        document.getElementById('editObservacoes').value = service.observacoes || '';

        new bootstrap.Modal(document.getElementById('editServiceModal')).show();
      } else {
        alert('Erro ao carregar dados do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar dados do serviço');
    }
  };

  window.deleteService = async function (serviceId) {
    if (!confirm('Tem certeza que deseja remover este serviço? Esta ação não pode ser desfeita.')) {
      return;
    }

    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?id=${serviceId}`, {
        method: 'DELETE'
      });

      const data = await response.json();

      if (data.success) {
        alert('Serviço removido com sucesso!');
        loadServicesData();
      } else {
        alert('Erro ao remover serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao remover serviço');
    }
  };

  // Event listeners para os modais
  document.addEventListener('DOMContentLoaded', function () {
    // Salvar alterações do serviço
    document.getElementById('saveServiceBtn')?.addEventListener('click', async function () {
      const serviceId = document.getElementById('editServiceId').value;
      const formData = {
        service_id: serviceId,
        titulo: document.getElementById('editTitulo').value,
        categoria: document.getElementById('editCategoria').value,
        descricao: document.getElementById('editDescricao').value,
        endereco: document.getElementById('editEndereco').value,
        cidade: document.getElementById('editCidade').value,
        orcamento_maximo: document.getElementById('editOrcamento').value,
        prazo_desejado: document.getElementById('editPrazo').value,
        status: document.getElementById('editStatus').value,
        observacoes: document.getElementById('editObservacoes').value
      };

      try {
        const response = await fetch('../php/admin/administrador-gerenciar.php', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
          alert('Serviço atualizado com sucesso!');
          bootstrap.Modal.getInstance(document.getElementById('editServiceModal')).hide();
          loadServicesData();
        } else {
          alert('Erro ao atualizar serviço: ' + data.message);
        }
      } catch (error) {
        console.error('Erro:', error);
        alert('Erro de conexão ao atualizar serviço');
      }
    });
  });

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
      reviews: 'Avaliações'
    };

    pageTitle.textContent = titles[sectionName] || 'Dashboard';

    // Load section specific data
    if (sectionName === 'services') {
      loadServicesData();
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

  async function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
      try {
        // Clear session
        await fetch('../php/admin/logout.php', { method: 'POST' });
        window.location.href = 'login/administrador-signin.html';
      } catch (error) {
        // Force redirect even if logout fails
        window.location.href = 'login/administrador-signin.html';
      }
    }
  }
});