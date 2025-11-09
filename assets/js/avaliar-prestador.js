// Obter ID do contrato da URL
const urlParams = new URLSearchParams(window.location.search);
const contractId = urlParams.get('contract_id');

let selectedRating = 0;

// Inicialização
document.addEventListener('DOMContentLoaded', function () {
  if (!contractId) {
    alert('ID do contrato não fornecido');
    window.location.href = '../cliente-dashboard.html';
    return;
  }

  carregarInfoContrato();
  inicializarEstrelas();
  inicializarFormulario();
});

// Carregar informações do contrato
async function carregarInfoContrato() {
  try {
    const response = await fetch(`../../php/servico/listar-contratos-cliente.php`);
    const data = await response.json();

    if (data.success) {
      const contrato = data.contratos.find(c => c.contract_id == contractId);

      if (!contrato) {
        throw new Error('Contrato não encontrado');
      }

      if (contrato.ja_avaliado == 1) {
        alert('Você já avaliou este serviço');
        window.location.href = '../cliente-dashboard.html';
        return;
      }

      if (contrato.status !== 'completed') {
        alert('Este contrato ainda não foi concluído');
        window.location.href = '../cliente-dashboard.html';
        return;
      }

      // Exibir informações
      document.getElementById('contratoInfo').innerHTML = `
                <div class="alert alert-info">
                    <h5 class="mb-2"><i class="bi bi-info-circle"></i> Informações do Serviço</h5>
                    <p class="mb-1"><strong>Serviço:</strong> ${contrato.titulo}</p>
                    <p class="mb-1"><strong>Categoria:</strong> ${contrato.categoria}</p>
                    <p class="mb-1"><strong>Prestador:</strong> ${contrato.prestador_nome}</p>
                    <p class="mb-0"><strong>Especialidade:</strong> ${contrato.specialty || 'Não informada'}</p>
                </div>
            `;
    }
  } catch (error) {
    console.error('Erro:', error);
    alert('Erro ao carregar informações do contrato');
  }
}

// Sistema de estrelas com acessibilidade
function inicializarEstrelas() {
  const stars = document.querySelectorAll('.star');
  const ratingInput = document.getElementById('rating');
  const ratingError = document.getElementById('rating-error');
  let currentFocusIndex = 0;

  // Função para atualizar visual das estrelas
  function updateStarsVisual(rating) {
    stars.forEach((star, index) => {
      const starRating = parseInt(star.getAttribute('data-rating'));
      const isActive = starRating <= rating;

      if (isActive) {
        star.classList.add('active');
        star.setAttribute('aria-checked', 'true');
      } else {
        star.classList.remove('active');
        star.setAttribute('aria-checked', 'false');
      }
    });
  }

  // Função para atualizar tabindex
  function updateTabIndex(focusIndex) {
    stars.forEach((star, index) => {
      star.setAttribute('tabindex', index === focusIndex ? '0' : '-1');
    });
  }

  // Função para definir rating
  function setRating(rating) {
    selectedRating = rating;
    ratingInput.value = rating;
    updateStarsVisual(rating);

    // Esconder erro se rating foi selecionado
    if (rating > 0) {
      ratingError.style.display = 'none';
    }
  }

  // Função para mover foco
  function moveFocus(direction) {
    if (direction === 'left') {
      currentFocusIndex = Math.max(0, currentFocusIndex - 1);
    } else if (direction === 'right') {
      currentFocusIndex = Math.min(stars.length - 1, currentFocusIndex + 1);
    }
    updateTabIndex(currentFocusIndex);
    stars[currentFocusIndex].focus();
  }

  // Event listeners para cada estrela
  stars.forEach((star, index) => {
    // Click
    star.addEventListener('click', function () {
      const rating = parseInt(this.getAttribute('data-rating'));
      setRating(rating);
      currentFocusIndex = index;
      updateTabIndex(currentFocusIndex);
    });

    // Keyboard events
    star.addEventListener('keydown', function (e) {
      switch (e.key) {
        case 'ArrowLeft':
          e.preventDefault();
          moveFocus('left');
          break;
        case 'ArrowRight':
          e.preventDefault();
          moveFocus('right');
          break;
        case 'Home':
          e.preventDefault();
          currentFocusIndex = 0;
          updateTabIndex(currentFocusIndex);
          stars[currentFocusIndex].focus();
          break;
        case 'End':
          e.preventDefault();
          currentFocusIndex = stars.length - 1;
          updateTabIndex(currentFocusIndex);
          stars[currentFocusIndex].focus();
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          const rating = parseInt(this.getAttribute('data-rating'));
          setRating(rating);
          break;
      }
    });

    // Efeito hover (apenas para mouse)
    star.addEventListener('mouseenter', function () {
      const hoverRating = parseInt(this.getAttribute('data-rating'));
      stars.forEach(s => {
        const starRating = parseInt(s.getAttribute('data-rating'));
        if (starRating <= hoverRating) {
          s.style.color = '#ffc107';
        } else {
          s.style.color = '#ddd';
        }
      });
    });
  });

  // Resetar ao sair do mouse
  document.getElementById('starRating').addEventListener('mouseleave', function () {
    updateStarsVisual(selectedRating);
  });

  // Inicializar foco na primeira estrela
  updateTabIndex(0);
}

// Enviar avaliação
function inicializarFormulario() {
  document.getElementById('formAvaliacao').addEventListener('submit', async function (e) {
    e.preventDefault();

    if (selectedRating === 0) {
      // Mostrar erro de validação
      const ratingError = document.getElementById('rating-error');
      ratingError.style.display = 'block';

      // Focar na primeira estrela para acessibilidade
      const firstStar = document.querySelector('.star[data-rating="1"]');
      if (firstStar) {
        firstStar.focus();
      }
      return;
    }

    const formData = {
      contract_id: contractId,
      rating: selectedRating,
      comment: document.getElementById('comment').value.trim()
    };

    try {
      const response = await fetch('../../php/servico/criar-avaliacao.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (data.success) {
        alert('Avaliação enviada com sucesso!');
        window.location.href = '../cliente-dashboard.html';
      } else {
        alert('Erro: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro ao enviar avaliação');
    }
  });
}

