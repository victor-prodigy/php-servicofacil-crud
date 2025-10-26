// Buscar Prestadores - JavaScript

document.addEventListener('DOMContentLoaded', function () {
  checkAuthentication();
  setupEventListeners();
  carregarTodosPrestadores();
});

async function checkAuthentication() {
  try {
    const response = await fetch('../../php/cliente/cliente-dashboard.php');
    const data = await response.json();

    if (!data.authenticated) {
      alert(data.message);
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

  // Filter buttons
  const filterBtns = document.querySelectorAll('.filter-specialty');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      // Remove active class from all buttons
      filterBtns.forEach(b => b.classList.remove('active'));
      // Add active class to clicked button
      this.classList.add('active');

      // Get specialty from data attribute
      const specialty = this.getAttribute('data-specialty');
      aplicarFiltroEspecialidade(specialty);
    });
  });
}

async function realizarBusca() {
  const search = document.getElementById('searchInput').value.trim();
  const location = document.getElementById('locationInput').value.trim();
  const activeSpecialtyBtn = document.querySelector('.filter-specialty.active');
  const specialty = activeSpecialtyBtn ? activeSpecialtyBtn.getAttribute('data-specialty') : '';

  await carregarPrestadores(search, specialty, location);
}

function aplicarFiltroEspecialidade(specialty) {
  const search = document.getElementById('searchInput').value.trim();
  const location = document.getElementById('locationInput').value.trim();
  carregarPrestadores(search, specialty, location);
}

async function carregarTodosPrestadores() {
  await carregarPrestadores('', '', '');
}

async function carregarPrestadores(search, specialty, location) {
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
    if (specialty) params.append('specialty', specialty);
    if (location) params.append('location', location);

    const url = `../../php/servico/buscar-prestadores.php?${params.toString()}`;
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
    if (data.filtros.search || data.filtros.specialty || data.filtros.location) {
      const filters = [];
      if (data.filtros.search) filters.push(`"${data.filtros.search}"`);
      if (data.filtros.specialty) filters.push(`Especialidade: ${data.filtros.specialty}`);
      if (data.filtros.location) filters.push(`Localização: ${data.filtros.location}`);
      resultsMessage.textContent = `Resultados para: ${filters.join(', ')}`;
    } else {
      resultsMessage.textContent = 'Todos os prestadores disponíveis';
    }

    // Display prestadores
    if (data.prestadores.length === 0) {
      noResultsState.style.display = 'block';
      return;
    }

    data.prestadores.forEach(prestador => {
      const card = criarCardPrestador(prestador);
      resultsGrid.appendChild(card);
    });

  } catch (error) {
    console.error('Erro ao buscar prestadores:', error);
    loadingState.style.display = 'none';
    resultsGrid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Erro ao carregar prestadores:</strong> ${error.message}
                </div>
            </div>
        `;
  }
}

function criarCardPrestador(prestador) {
  const col = document.createElement('div');
  col.className = 'col-md-6 col-lg-4';

  // Calculate star rating
  const rating = prestador.avaliacao_media || 0;
  const starRating = gerarEstrelas(rating);

  // Format specialty badge
  const specialtyBadge = prestador.especialidade ?
    `<span class="badge bg-primary specialty-badge">${prestador.especialidade}</span>` :
    `<span class="badge bg-secondary specialty-badge">Sem especialidade</span>`;

  // Format location
  const location = prestador.localizacao || 'Não informado';

  col.innerHTML = `
        <div class="card prestador-card h-100">
            <div class="card-body">
                <div class="prestador-header">
                    <div>
                        <h5 class="card-title mb-1">${prestador.nome}</h5>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt"></i> ${location}
                        </small>
                    </div>
                </div>
                
                <div class="mb-3">
                    ${specialtyBadge}
                </div>
                
                <!-- Rating -->
                <div class="mb-3">
                    <div class="star-rating">${starRating}</div>
                    <small class="text-muted ms-2">
                        ${prestador.avaliacao_media > 0 ? prestador.avaliacao_media.toFixed(1) : 'N/A'} 
                        (${prestador.total_avaliacoes} avaliações)
                    </small>
                </div>
                
                <!-- Contact Info -->
                <div class="mb-3">
                    <small class="text-muted d-block">
                        <i class="bi bi-envelope"></i> ${prestador.email}
                    </small>
                    ${prestador.telefone ? `
                        <small class="text-muted d-block">
                            <i class="bi bi-telephone"></i> ${prestador.telefone}
                        </small>
                    ` : ''}
                </div>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value">${prestador.servicos_concluidos}</div>
                        <div class="stat-label">Serviços</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">${prestador.avaliacao_media}</div>
                        <div class="stat-label">Avaliação</div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary btn-sm" onclick="verPerfilPrestador(${prestador.prestador_id})">
                        <i class="bi bi-person"></i> Ver Perfil
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="contratarPrestador(${prestador.prestador_id})">
                        <i class="bi bi-check-circle"></i> Contratar
                    </button>
                </div>
            </div>
        </div>
    `;

  return col;
}

function gerarEstrelas(rating) {
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);

  let html = '';

  // Full stars
  for (let i = 0; i < fullStars; i++) {
    html += '<i class="bi bi-star-fill"></i>';
  }

  // Half star
  if (hasHalfStar) {
    html += '<i class="bi bi-star-half"></i>';
  }

  // Empty stars
  for (let i = 0; i < emptyStars; i++) {
    html += '<i class="bi bi-star"></i>';
  }

  return html;
}

function verPerfilPrestador(prestadorId) {
  alert(`Ver perfil do prestador ${prestadorId} - Funcionalidade em desenvolvimento`);
  // TODO: Implementar modal ou página de perfil
}

function contratarPrestador(prestadorId) {
  if (confirm('Deseja contratar este prestador? Você será redirecionado para criar uma solicitação.')) {
    // Redirect to new service request page with provider pre-selected
    window.location.href = `nova-solicitacao.html?prestador_id=${prestadorId}`;
  }
}

