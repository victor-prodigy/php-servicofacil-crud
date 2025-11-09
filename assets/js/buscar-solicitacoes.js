// Buscar Solicitações de Serviços - JavaScript

document.addEventListener('DOMContentLoaded', function () {
  checkAuthentication();
  setupEventListeners();

  // Verificar se há parâmetros na URL (vindo do formulário do dashboard)
  const urlParams = new URLSearchParams(window.location.search);
  const category = urlParams.get('category');
  const location = urlParams.get('location');
  const search = urlParams.get('search');

  // Se houver parâmetros na URL, preencher os campos e executar a busca
  if (category || location || search) {
    // Preencher campos se existirem
    if (search && document.getElementById('searchInput')) {
      document.getElementById('searchInput').value = search;
    }

    if (location && document.getElementById('locationInput')) {
      document.getElementById('locationInput').value = location;
    }

    if (category) {
      // Ativar botão de categoria correspondente
      const categoryBtns = document.querySelectorAll('.filter-category');
      categoryBtns.forEach(btn => {
        if (btn.getAttribute('data-category') === category) {
          btn.classList.add('active');
        }
      });
    }

    // Executar busca com os parâmetros da URL
    carregarSolicitacoes(search || '', category || '', location || '', '', '');
  } else {
    // Caso contrário, carregar todas as solicitações
    carregarTodasSolicitacoes();
  }
});

async function checkAuthentication() {
  try {
    const response = await fetch('../../php/prestador/prestador-dashboard.php');
    const data = await response.json();

    if (!data.authenticated) {
      alert(data.msg || 'Acesso negado. Faça login como prestador.');
      window.location.href = '../login/index.html';
      return;
    }

    document.getElementById('userName').textContent = data.nome;

  } catch (error) {
    console.error('Erro ao verificar autenticação:', error);
    alert('Erro ao carregar a página. Por favor, tente novamente.');
    window.location.href = '../login/index.html';
  }
}

function setupEventListeners() {
  // Form submit
  const searchForm = document.getElementById('searchForm');
  if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
      e.preventDefault();
      realizarBusca();
    });
  }

  // Enter key on inputs
  const searchInput = document.getElementById('searchInput');
  const locationInput = document.getElementById('locationInput');
  const precoMinInput = document.getElementById('precoMinInput');
  const precoMaxInput = document.getElementById('precoMaxInput');

  if (searchInput) {
    searchInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        realizarBusca();
      }
    });
  }

  if (locationInput) {
    locationInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        realizarBusca();
      }
    });
  }

  if (precoMinInput) {
    precoMinInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        realizarBusca();
      }
    });
  }

  if (precoMaxInput) {
    precoMaxInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        realizarBusca();
      }
    });
  }

  // Filter buttons
  const filterBtns = document.querySelectorAll('.filter-category');

  // Verificar se há categoria na URL e destacar o botão correspondente
  const urlParams = new URLSearchParams(window.location.search);
  const categoryFromUrl = urlParams.get('category');
  if (categoryFromUrl) {
    filterBtns.forEach(btn => {
      if (btn.getAttribute('data-category') === categoryFromUrl) {
        btn.classList.add('active');
      }
    });
  } else {
    // Ativar botão "Todas" por padrão
    filterBtns.forEach(btn => {
      if (btn.getAttribute('data-category') === '') {
        btn.classList.add('active');
      }
    });
  }

  filterBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      // Remove active class from all buttons
      filterBtns.forEach(b => b.classList.remove('active'));
      // Add active class to clicked button
      this.classList.add('active');

      // Get category from data attribute
      const category = this.getAttribute('data-category');
      aplicarFiltroCategoria(category);
    });
  });
}

async function realizarBusca() {
  const search = document.getElementById('searchInput').value.trim();
  const location = document.getElementById('locationInput').value.trim();
  const precoMin = document.getElementById('precoMinInput').value.trim();
  const precoMax = document.getElementById('precoMaxInput').value.trim();
  const activeCategoryBtn = document.querySelector('.filter-category.active');
  const category = activeCategoryBtn ? activeCategoryBtn.getAttribute('data-category') : '';

  await carregarSolicitacoes(search, category, location, precoMin, precoMax);
}

function aplicarFiltroCategoria(category) {
  const search = document.getElementById('searchInput').value.trim();
  const location = document.getElementById('locationInput').value.trim();
  const precoMin = document.getElementById('precoMinInput').value.trim();
  const precoMax = document.getElementById('precoMaxInput').value.trim();
  carregarSolicitacoes(search, category, location, precoMin, precoMax);
}

async function carregarTodasSolicitacoes() {
  await carregarSolicitacoes('', '', '', '', '');
}

async function carregarSolicitacoes(search, category, location, precoMin, precoMax) {
  const loadingState = document.getElementById('loadingState');
  const resultsGrid = document.getElementById('resultsGrid');
  const noResultsState = document.getElementById('noResultsState');
  const totalResults = document.getElementById('totalResults');

  // Show loading
  loadingState.style.display = 'block';
  resultsGrid.innerHTML = '';
  noResultsState.style.display = 'none';

  try {
    // Build query params
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (category) params.append('categoria', category);
    if (location) params.append('cidade', location);
    if (precoMin) params.append('preco_min', precoMin);
    if (precoMax) params.append('preco_max', precoMax);

    const url = `../../php/servico/buscar-solicitacoes.php?${params.toString()}`;
    const response = await fetch(url);
    const data = await response.json();

    if (!data.success) {
      throw new Error(data.message);
    }

    // Hide loading
    loadingState.style.display = 'none';

    // Update total results
    totalResults.textContent = data.total;

    // Display results message
    const resultsMessage = document.getElementById('resultsMessage');
    if (data.filtros.search || data.filtros.categoria || data.filtros.cidade || data.filtros.preco_min || data.filtros.preco_max) {
      const filters = [];
      if (data.filtros.search) filters.push(`"${data.filtros.search}"`);
      if (data.filtros.categoria) filters.push(`Categoria: ${data.filtros.categoria}`);
      if (data.filtros.cidade) filters.push(`Cidade: ${data.filtros.cidade}`);
      if (data.filtros.preco_min || data.filtros.preco_max) {
        const precoFilter = [];
        if (data.filtros.preco_min) precoFilter.push(`R$ ${parseFloat(data.filtros.preco_min).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`);
        if (data.filtros.preco_max) precoFilter.push(`R$ ${parseFloat(data.filtros.preco_max).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`);
        filters.push(`Preço: ${precoFilter.join(' - ')}`);
      }
      resultsMessage.textContent = `Resultados para: ${filters.join(', ')}`;
    } else {
      resultsMessage.textContent = 'Todas as solicitações disponíveis';
    }

    // Display solicitacoes
    if (data.solicitacoes.length === 0) {
      noResultsState.style.display = 'block';
      return;
    }

    data.solicitacoes.forEach(solicitacao => {
      const card = criarCardSolicitacao(solicitacao);
      resultsGrid.appendChild(card);
    });

  } catch (error) {
    console.error('Erro ao buscar solicitações:', error);
    loadingState.style.display = 'none';
    resultsGrid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Erro ao carregar solicitações:</strong> ${error.message}
                </div>
            </div>
        `;
  }
}

function criarCardSolicitacao(solicitacao) {
  const col = document.createElement('div');
  col.className = 'col-md-6 col-lg-4';

  // Format category badge
  const categoryBadge = solicitacao.categoria ?
    `<span class="badge bg-primary category-badge">${escapeHtml(solicitacao.categoria)}</span>` :
    `<span class="badge bg-secondary category-badge">Sem categoria</span>`;

  // Format location
  const location = solicitacao.cidade || 'Não informado';

  // Format description (truncate)
  const description = solicitacao.descricao && solicitacao.descricao.length > 120
    ? solicitacao.descricao.substring(0, 120) + '...'
    : (solicitacao.descricao || 'Sem descrição');

  // Format budget
  const budget = solicitacao.orcamento_maximo
    ? `R$ ${parseFloat(solicitacao.orcamento_maximo).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
    : 'A combinar';

  // Format deadline
  let deadline = 'Não especificado';
  if (solicitacao.prazo_desejado) {
    const deadlineDate = new Date(solicitacao.prazo_desejado);
    deadline = deadlineDate.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    });
  }

  // Format creation date
  const createdDate = new Date(solicitacao.data_criacao);
  const createdDateFormatted = createdDate.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });

  // Format client name
  const clientName = solicitacao.cliente_nome || 'Cliente';

  col.innerHTML = `
        <div class="card solicitacao-card h-100">
            <div class="card-body">
                <div class="solicitacao-header">
                    <div>
                        <h5 class="card-title mb-1">${escapeHtml(solicitacao.titulo)}</h5>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> ${escapeHtml(location)}
                        </small>
                    </div>
                </div>
                
                <div class="mb-3">
                    ${categoryBadge}
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <p class="card-text text-muted" style="font-size: 0.9rem;">
                        ${escapeHtml(description)}
                    </p>
                </div>
                
                <!-- Budget and Deadline -->
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-currency-dollar"></i> <strong>Orçamento:</strong> ${budget}
                    </small>
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-calendar-event"></i> <strong>Prazo:</strong> ${deadline}
                    </small>
                    <small class="text-muted d-block">
                        <i class="bi bi-person"></i> <strong>Cliente:</strong> ${escapeHtml(clientName)}
                    </small>
                </div>
                
                <!-- Creation Date -->
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Publicado em: ${createdDateFormatted}
                    </small>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2 mt-3">
                    <a href="detalhe-oportunidade.html?id=${solicitacao.id}" class="btn btn-primary btn-sm">
                        <i class="bi bi-eye"></i> Ver Detalhes
                    </a>
                </div>
            </div>
        </div>
    `;

  return col;
}

function escapeHtml(text) {
  if (!text) return '';
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

