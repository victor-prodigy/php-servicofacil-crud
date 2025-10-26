/**
 * ✅ SISTEMA DE CADASTRO UNIFICADO
 * Gerencia registro de Cliente e Prestador
 */

// 🎯 Variável global: tipo de usuário ativo
let userType = 'cliente';

// 🚀 Inicialização automática quando página carrega
document.addEventListener('DOMContentLoaded', initializeSignupForm);

/**
 * 🔧 Configura o formulário de cadastro
 */
function initializeSignupForm() {
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
    toggleUserFields(isClient);
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
    document.getElementById('submit-btn').textContent = isClient ? 'Cadastrar como Cliente' : 'Cadastrar como Prestador';
}

/**
 * 🔀 Alterna campos específicos do usuário
 */
function toggleUserFields(isClient) {
    const clienteFields = document.getElementById('cliente-fields');
    const prestadorFields = document.getElementById('prestador-fields');

    if (isClient) {
        // Mostrar campos do cliente
        clienteFields.style.display = 'block';
        prestadorFields.style.display = 'none';

        // Configurar campos obrigatórios
        setFieldRequired('phone_number', true);
        setFieldRequired('specialty', false);
        setFieldRequired('location', false);

        // Limpar campos do prestador
        clearField('specialty');
        clearField('location');
    } else {
        // Mostrar campos do prestador
        clienteFields.style.display = 'none';
        prestadorFields.style.display = 'block';

        // Configurar campos obrigatórios
        setFieldRequired('phone_number', false);
        setFieldRequired('specialty', true);
        setFieldRequired('location', true);

        // Limpar campo do cliente
        clearField('phone_number');
    }
}

/**
 * 📋 Configura envio do formulário
 */
function setupFormHandler() {
    document.getElementById('dynamicSignupForm').onsubmit = function (e) {
        e.preventDefault();
        handleSignup();
    };
}

/**
 * ✅ Processa o cadastro do usuário
 */
function handleSignup() {
    const form = document.getElementById('dynamicSignupForm');
    const button = document.getElementById('submit-btn');
    const alerts = document.getElementById('alert-area');

    // Validar senhas
    if (!validatePasswords()) {
        showAlert('danger', 'As senhas não coincidem', alerts);
        return;
    }

    // Preparar dados do formulário
    const formData = new FormData(form);

    // Mostrar estado de carregamento
    showLoading(button);
    clearAlerts(alerts);

    // Fazer requisição de cadastro
    sendSignupRequest(formData, form, button, alerts);
}

/**
 * 🔒 Valida se as senhas coincidem
 */
function validatePasswords() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    return password === confirmPassword;
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

/**
 * ⏳ Mostra estado de carregamento no botão
 */
function showLoading(button) {
    button.originalText = button.textContent;
    button.textContent = 'Cadastrando...';
    button.disabled = true;
}

/**
 * 🧹 Limpa alertas anteriores
 */
function clearAlerts(alertContainer) {
    alertContainer.innerHTML = '';
}

/**
 * 🌐 Envia requisição de cadastro para o servidor
 */
function sendSignupRequest(formData, form, button, alerts) {
    // 🎯 Endpoint específico baseado no tipo de usuário
    const endpoint = getSignupEndpoint();

    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => handleSignupResponse(data, form, button, alerts))
        .catch(error => handleSignupError(error, button, alerts))
        .finally(() => resetButton(button));
}

/**
 * 🎯 Retorna endpoint específico para o tipo de usuário
 */
function getSignupEndpoint() {
    return userType === 'cliente'
        ? '../../php/cliente/cliente-signup.php'
        : '../../php/prestador/prestador-signup.php';
}

/**
 * ✅ Trata resposta de cadastro bem-sucedida
 */
function handleSignupResponse(data, form, button, alerts) {
    if (data.success) {
        showAlert('success', data.message, alerts);
        setTimeout(() => {
            form.reset();
            window.location.href = '../login/index.html';
        }, 2000);
    } else {
        showAlert('danger', data.message || 'Erro no cadastro', alerts);
    }
}

/**
 * ❌ Trata erros de cadastro
 */
function handleSignupError(error, button, alerts) {
    console.error('Erro de cadastro:', error);
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