/**
 * 🛠️ UTILITÁRIOS COMUNS
 * Funções compartilhadas entre login e cadastro
 */

/**
 * 🚨 Exibe alerta de sucesso ou erro
 */
function showAlert(type, message, container) {
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const icon = type === 'success' ? '✅' : '❌';

  container.innerHTML = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
      ${icon} ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
}

/**
 * ⏳ Mostra estado de carregamento no botão
 */
function showLoading(button) {
  button.originalText = button.textContent;
  button.textContent = 'Processando...';
  button.disabled = true;
}

/**
 * 🔄 Restaura estado original do botão
 */
function resetButton(button) {
  button.textContent = button.originalText || 'Enviar';
  button.disabled = false;
}

/**
 * 🧹 Limpa alertas anteriores
 */
function clearAlerts(alertContainer) {
  alertContainer.innerHTML = '';
}

/**
 * 📊 Processa resposta HTTP padrão
 */
function processHttpResponse(response) {
  if (!response.ok) {
    throw new Error(`Erro HTTP: ${response.status}`);
  }
  return response.json();
}

/**
 * 🎨 Atualiza estilos de cartões de seleção
 */
function updateCardStyles(clienteCard, prestadorCard, isCustomer) {
  clienteCard.className = isCustomer ? 'profile-card active' : 'profile-card inactive';
  prestadorCard.className = isCustomer ? 'profile-card inactive' : 'profile-card active';
}

/**
 * 🏷️ Define se campo é obrigatório
 */
function setFieldRequired(fieldId, required) {
  const field = document.getElementById(fieldId);
  if (field) {
    if (required) {
      field.setAttribute('required', '');
    } else {
      field.removeAttribute('required');
    }
  }
}

/**
 * 🧹 Limpa valor do campo
 */
function clearField(fieldId) {
  const field = document.getElementById(fieldId);
  if (field) field.value = '';
}