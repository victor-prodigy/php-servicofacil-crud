/**
 * 📝 ALTERAR SOLICITAÇÃO DE SERVIÇO - JavaScript
 * Funcionalidades para carregar e atualizar dados da solicitação
 */

// 🎯 Variáveis globais
let solicitacaoId = null;

// 🚀 Inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', function () {
  // Obter ID da solicitação da URL
  const urlParams = new URLSearchParams(window.location.search);
  solicitacaoId = urlParams.get('id');

  if (!solicitacaoId) {
    showAlert('error', 'ID da solicitação não foi fornecido.');
    setTimeout(() => {
      window.location.href = '../cliente-dashboard.html';
    }, 3000);
    return;
  }

  // Carregar dados da solicitação
  carregarDadosSolicitacao();

  // Configurar form submit
  const form = document.getElementById('alterarSolicitacaoForm');
  form.addEventListener('submit', handleFormSubmit);

  // Configurar contadores de caracteres
  setupCharacterCounters();
});

/**
 * 📥 Carregar dados da solicitação existente
 */
async function carregarDadosSolicitacao() {
  try {
    showLoading(true);

    console.log('Carregando solicitação ID:', solicitacaoId);

    const response = await fetch(`../../php/servico/obter-solicitacao.php?id=${solicitacaoId}`);

    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
    }

    const data = await response.json();

    console.log('Dados recebidos:', data);

    if (!data.success) {
      throw new Error(data.message);
    }

    // Preencher formulário com os dados
    preencherFormulario(data.solicitacao);

    showLoading(false);
    showForm(true);

  } catch (error) {
    console.error('Erro ao carregar solicitação:', error);
    showAlert('error', 'Erro ao carregar dados da solicitação: ' + error.message);
    showLoading(false);

    setTimeout(() => {
      window.location.href = '../cliente-dashboard.html';
    }, 3000);
  }
}

/**
 * 📝 Preencher formulário com dados da solicitação
 */
function preencherFormulario(solicitacao) {
  document.getElementById('solicitacao_id').value = solicitacao.request_id;
  document.getElementById('titulo').value = solicitacao.titulo || '';
  document.getElementById('categoria').value = solicitacao.categoria || '';
  document.getElementById('descricao').value = solicitacao.descricao || '';
  document.getElementById('endereco').value = solicitacao.endereco || '';
  document.getElementById('cidade').value = solicitacao.cidade || '';
  document.getElementById('prazo_desejado').value = solicitacao.prazo_desejado || '';
  document.getElementById('orcamento_maximo').value = solicitacao.orcamento_maximo || '';
  document.getElementById('observacoes').value = solicitacao.observacoes || '';

  // Formatar status para exibição
  const statusFormatado = formatarStatus(solicitacao.status);
  document.getElementById('status').value = statusFormatado;

  // Atualizar contadores de caracteres
  updateCharacterCount(document.getElementById('descricao'), 500);
  updateCharacterCount(document.getElementById('observacoes'), 300);
}

/**
 * 🏷️ Formatar status para exibição
 */
function formatarStatus(status) {
  const statusMap = {
    'pendente': 'Pendente',
    'em_andamento': 'Em Andamento',
    'concluido': 'Concluído',
    'cancelado': 'Cancelado'
  };
  return statusMap[status] || status;
}

/**
 * 📤 Processar envio do formulário
 */
async function handleFormSubmit(event) {
  event.preventDefault();

  try {
    // Desabilitar botão durante envio
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

    // Preparar dados do formulário
    const formData = new FormData();
    formData.append('solicitacao_id', document.getElementById('solicitacao_id').value);
    formData.append('titulo', document.getElementById('titulo').value.trim());
    formData.append('categoria', document.getElementById('categoria').value);
    formData.append('descricao', document.getElementById('descricao').value.trim());
    formData.append('endereco', document.getElementById('endereco').value.trim());
    formData.append('cidade', document.getElementById('cidade').value.trim());
    formData.append('prazo_desejado', document.getElementById('prazo_desejado').value);
    formData.append('observacoes', document.getElementById('observacoes').value.trim());

    // Orçamento máximo (opcional)
    const orcamento = document.getElementById('orcamento_maximo').value;
    if (orcamento && orcamento.trim() !== '') {
      formData.append('orcamento_maximo', orcamento);
    }

    // Enviar para o servidor
    const response = await fetch('../../php/servico/altera-solicitacao.php', {
      method: 'POST',
      body: formData
    });

    // Verificar se a resposta HTTP foi bem-sucedida
    if (!response.ok) {
      throw new Error(`Erro HTTP: ${response.status} - ${response.statusText}`);
    }

    const data = await response.json();

    if (data.success) {
      showAlert('success', data.message);

      // Redirecionar após sucesso
      setTimeout(() => {
        window.location.href = '../cliente-dashboard.html';
      }, 2000);
    } else {
      throw new Error(data.message);
    }

  } catch (error) {
    console.error('Erro detalhado:', error);

    // Mostrar erro mais detalhado para debug
    let errorMessage = 'Erro ao atualizar solicitação: ' + error.message;

    // Se for erro de rede ou parsing
    if (error.name === 'TypeError' || error.name === 'SyntaxError') {
      errorMessage += '\n\nVerifique:\n- Se o servidor está funcionando\n- Se os arquivos PHP estão corretos\n- Se há erros de sintaxe';
    }

    showAlert('error', errorMessage);
  } finally {
    // Reabilitar botão
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
  }
}

/**
 * 💬 Exibir alertas
 */
function showAlert(type, message) {
  const alertArea = document.getElementById('alert-area');
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

  alertArea.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

  // Auto-hide após 5 segundos se for sucesso
  if (type === 'success') {
    setTimeout(() => {
      const alert = alertArea.querySelector('.alert');
      if (alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    }, 5000);
  }

  // Scroll para o topo
  alertArea.scrollIntoView({ behavior: 'smooth' });
}

/**
 * ⏳ Controlar exibição do loading
 */
function showLoading(show) {
  const loadingIndicator = document.getElementById('loadingIndicator');
  loadingIndicator.style.display = show ? 'block' : 'none';
}

/**
 * 📋 Controlar exibição do formulário
 */
function showForm(show) {
  const formCard = document.getElementById('formCard');
  formCard.style.display = show ? 'block' : 'none';
}

/**
 * 🎛️ Configurar contadores de caracteres
 */
function setupCharacterCounters() {
  const descricaoField = document.getElementById('descricao');
  const observacoesField = document.getElementById('observacoes');

  if (descricaoField) {
    descricaoField.addEventListener('input', function () {
      updateCharacterCount(this, 500);
    });
  }

  if (observacoesField) {
    observacoesField.addEventListener('input', function () {
      updateCharacterCount(this, 300);
    });
  }
}

/**
 * 🔢 Atualizar contador de caracteres
 */
function updateCharacterCount(element, maxLength) {
  const currentLength = element.value.length;
  const formText = element.parentNode.querySelector('.form-text');

  if (formText) {
    const remaining = maxLength - currentLength;
    const originalText = formText.textContent;

    // Manter texto original se não for contador
    if (!originalText.includes('caracteres')) {
      formText.textContent = `${originalText} (${currentLength}/${maxLength} caracteres)`;
    } else {
      formText.textContent = `${currentLength}/${maxLength} caracteres`;
    }

    if (remaining < 50) {
      formText.style.color = remaining < 0 ? '#dc3545' : '#fd7e14';
    } else {
      formText.style.color = '#6c757d';
    }
  }
}

/**
 * 🔄 Função para recarregar dados (caso necessário)
 */
function recarregarDados() {
  showForm(false);
  carregarDadosSolicitacao();
}

/**
 * 🧹 Função de validação adicional do formulário
 */
function validarFormulario() {
  const titulo = document.getElementById('titulo').value.trim();
  const categoria = document.getElementById('categoria').value;
  const descricao = document.getElementById('descricao').value.trim();
  const endereco = document.getElementById('endereco').value.trim();
  const cidade = document.getElementById('cidade').value.trim();
  const prazoDesejado = document.getElementById('prazo_desejado').value;

  if (!titulo) {
    showAlert('error', 'Título é obrigatório.');
    return false;
  }

  if (!categoria) {
    showAlert('error', 'Categoria é obrigatória.');
    return false;
  }

  if (!descricao) {
    showAlert('error', 'Descrição é obrigatória.');
    return false;
  }

  if (!endereco) {
    showAlert('error', 'Endereço é obrigatório.');
    return false;
  }

  if (!cidade) {
    showAlert('error', 'Cidade é obrigatória.');
    return false;
  }

  if (!prazoDesejado) {
    showAlert('error', 'Prazo desejado é obrigatório.');
    return false;
  }

  return true;
}