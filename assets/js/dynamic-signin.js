/**
 * ✅ SISTEMA DE LOGIN UNIFICADO
 * Gerencia autenticação de Cliente e Prestador
 */

// 🎯 Variável global: tipo de usuário ativo
let userType = 'cliente';

// 🚀 Inicialização automática quando página carrega
document.addEventListener('DOMContentLoaded', initializeLoginForm);

/**
 * 🔧 Configura o formulário de login
 */
function initializeLoginForm() {
  setUserType('cliente');     // Cliente como padrão
  setupUserSelection();       // Configurar seleção de perfil
  setupFormHandler();         // Configurar envio do formulário
}

/**
 * 👥 Configura seleção entre Cliente e Prestador
 */
function setupUserSelection() {
  document.getElementById('cliente-card').onclick = () => setUserType('cliente');
  document.getElementById('prestador-card').onclick = () => setUserType('service_provider');
}

/**
 * 🔄 Define o tipo de usuário ativo
 */
function setUserType(type) {
  userType = type;

  const isClient = (type === 'cliente');

  // Atualizar interface visual
  updateCardStyles(isClient);
  updateFormElements(isClient);
}

/**
 * 🎨 Atualiza estilos dos cartões de seleção
 */
function updateCardStyles(isClient) {
  const clienteCard = document.getElementById('cliente-card');
  const prestadorCard = document.getElementById('prestador-card');

  clienteCard.className = isClient ? 'profile-card active' : 'profile-card inactive';
  prestadorCard.className = isClient ? 'profile-card inactive' : 'profile-card active';
}

/**
 * 📝 Atualiza elementos do formulário
 */
function updateFormElements(isClient) {
  document.getElementById('user_type').value = isClient ? 'cliente' : 'service_provider';
  document.getElementById('submit-btn').textContent = isClient ? 'Entrar como Cliente' : 'Entrar como Prestador';
}

/**
 * 📋 Configura envio do formulário
 */
function setupFormHandler() {
  document.getElementById('dynamicSigninForm').onsubmit = function (e) {
    e.preventDefault();
    handleLogin();
  };
}

/**
 * 🔐 Processa o login do usuário
 */
function handleLogin() {
  const form = document.getElementById('dynamicSigninForm');
  const button = document.getElementById('submit-btn');
  const alerts = document.getElementById('alert-area');

  // Preparar dados do formulário
  const formData = new FormData(form);
  const endpoint = getLoginEndpoint();

  // Mostrar estado de carregamento
  showLoading(button);
  clearAlerts(alerts);

  // Fazer requisição de login
  sendLoginRequest(endpoint, formData, button, alerts);
}

/**
 * 🎯 Determina endpoint baseado no tipo de usuário
 */
function getLoginEndpoint() {
  return userType === 'cliente'
    ? '../../php/cliente/cliente-signin.php'
    : '../../php/prestador/prestador-signin.php';
}

/**
 * ⏳ Mostra estado de carregamento no botão
 */
function showLoading(button) {
  button.originalText = button.textContent;
  button.textContent = 'Entrando...';
  button.disabled = true;
}

/**
 * 🧹 Limpa alertas anteriores
 */
function clearAlerts(alertContainer) {
  alertContainer.innerHTML = '';
}

/**
 * 🌐 Envia requisição de login para o servidor
 */
function sendLoginRequest(endpoint, formData, button, alerts) {
  fetch(endpoint, {
    method: 'POST',
    body: formData
  })
    .then(response => processResponse(response))
    .then(data => handleLoginResponse(data, button, alerts))
    .catch(error => handleLoginError(error, button, alerts))
    .finally(() => resetButton(button));
}

/**
 * 📊 Processa resposta HTTP
 */
function processResponse(response) {
  if (!response.ok) {
    throw new Error(`Erro HTTP: ${response.status}`);
  }
  return response.json();
}

/**
 * ✅ Trata resposta de login bem-sucedida
 */
function handleLoginResponse(data, button, alerts) {
  if (data.success) {
    showAlert('success', data.message, alerts);
    redirectToUserDashboard();
  } else {
    showAlert('danger', data.message || 'Erro no login', alerts);
  }
}

/**
 * ❌ Trata erros de login
 */
function handleLoginError(error, button, alerts) {
  console.error('Erro de login:', error);
  showAlert('danger', 'Erro de conexão. Tente novamente.', alerts);
}

/**
 * 🔄 Restaura estado original do botão
 */
function resetButton(button) {
  button.textContent = button.originalText;
  button.disabled = false;
}

/**
 * 🏠 Redireciona para dashboard do usuário
 */
function redirectToUserDashboard() {
  setTimeout(() => {
    const dashboardUrl = userType === 'cliente'
      ? '../cliente-dashboard.html'
      : '../prestador-dashboard.html';
    window.location.href = dashboardUrl;
  }, 1500);
}

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

