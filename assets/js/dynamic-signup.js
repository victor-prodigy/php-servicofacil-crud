/**
 * Dynamic Signup JavaScript
 * Gerencia o formulário dinâmico de cadastro único
 */

let currentUserType = 'customer';

document.addEventListener('DOMContentLoaded', function() {
    initializeDynamicForm();
    setupProfileSelection();
    setupFormSubmission();
});

/**
 * Inicializa o formulário dinâmico
 */
function initializeDynamicForm() {
    // Definir cliente como padrão
    switchUserType('customer');
}

/**
 * Configura a seleção de perfil
 */
function setupProfileSelection() {
    const clienteCard = document.getElementById('cliente-card');
    const prestadorCard = document.getElementById('prestador-card');

    clienteCard.addEventListener('click', () => {
        switchUserType('customer');
    });

    prestadorCard.addEventListener('click', () => {
        switchUserType('service_provider');
    });
}

/**
 * Alterna entre os tipos de usuário
 */
function switchUserType(userType) {
    currentUserType = userType;
    
    const clienteCard = document.getElementById('cliente-card');
    const prestadorCard = document.getElementById('prestador-card');
    const clienteFields = document.getElementById('cliente-fields');
    const prestadorFields = document.getElementById('prestador-fields');
    const userTypeInput = document.getElementById('user_type');
    const submitBtn = document.getElementById('submit-btn');
    const signinLink = document.getElementById('signin-link');
    
    // Remover classes ativas
    clienteCard.classList.remove('active', 'inactive');
    prestadorCard.classList.remove('active', 'inactive');
    
    if (userType === 'customer') {
        // Ativar cliente
        clienteCard.classList.add('active');
        prestadorCard.classList.add('inactive');
        
        // Mostrar campos do cliente
        clienteFields.style.display = 'block';
        prestadorFields.style.display = 'none';
        
        // Atualizar campos obrigatórios
        setFieldsRequired(true, ['phone_number']);
        setFieldsRequired(false, ['specialty', 'location']);
        
        // Atualizar form action e textos
        userTypeInput.value = 'customer';
        submitBtn.textContent = 'Cadastrar como Cliente';
        signinLink.href = 'signin.html';
        
        // Limpar campos do prestador
        document.getElementById('specialty').value = '';
        document.getElementById('location').value = '';
        
    } else if (userType === 'service_provider') {
        // Ativar prestador
        prestadorCard.classList.add('active');
        clienteCard.classList.add('inactive');
        
        // Mostrar campos do prestador
        clienteFields.style.display = 'none';
        prestadorFields.style.display = 'block';
        
        // Atualizar campos obrigatórios
        setFieldsRequired(false, ['phone_number']);
        setFieldsRequired(true, ['specialty', 'location']);
        
        // Atualizar form action e textos
        userTypeInput.value = 'service_provider';
        submitBtn.textContent = 'Cadastrar como Prestador';
        signinLink.href = 'signin.html';
        
        // Limpar campo do cliente
        document.getElementById('phone_number').value = '';
    }
    
    // Adicionar animação suave
    setTimeout(() => {
        const visibleFields = userType === 'customer' ? clienteFields : prestadorFields;
        visibleFields.style.opacity = '0';
        setTimeout(() => {
            visibleFields.style.opacity = '1';
        }, 150);
    }, 100);
}

/**
 * Define campos como obrigatórios ou opcionais
 */
function setFieldsRequired(required, fieldNames) {
    fieldNames.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            if (required) {
                field.setAttribute('required', '');
            } else {
                field.removeAttribute('required');
            }
        }
    });
}

/**
 * Configura o envio do formulário
 */
function setupFormSubmission() {
    const form = document.getElementById('dynamicSignupForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmission();
    });
}

/**
 * Processa o envio do formulário
 */
function handleFormSubmission() {
    const form = document.getElementById('dynamicSignupForm');
    const formData = new FormData(form);
    const submitButton = document.getElementById('submit-btn');
    const alertArea = document.getElementById('alert-area');
    
    // Validar senhas
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        showAlert('danger', 'As senhas não coincidem', alertArea);
        return;
    }
    
    // Determinar endpoint baseado no tipo de usuário
    const endpoint = '../app/universal-signup.php';
    
    // Show loading state
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Cadastrando...';
    submitButton.disabled = true;
    
    // Clear previous alerts
    alertArea.innerHTML = '';
    
    fetch(endpoint, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message, alertArea);
            
            // Reset form after success
            setTimeout(() => {
                form.reset();
                // Redirect to unified signin page
                window.location.href = 'signin.html';
            }, 2000);
        } else {
            // Show error message
            showAlert('danger', data.error, alertArea);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Erro de conexão. Tente novamente.', alertArea);
    })
    .finally(() => {
        // Reset button state
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    });
}

/**
 * Show alert message
 */
function showAlert(type, message, container) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill';
    
    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="${type}:">
                <use xlink:href="#${iconClass}"/>
            </svg>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    container.innerHTML = alertHTML;
    
    // Add Bootstrap icons if not already present
    if (!document.getElementById('bootstrap-icons')) {
        const iconsHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </symbol>
                <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </symbol>
            </svg>
        `;
        
        const div = document.createElement('div');
        div.id = 'bootstrap-icons';
        div.innerHTML = iconsHTML;
        document.body.appendChild(div);
    }
}