/**
 * Authentication JavaScript
 * Handles login forms with AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle cliente signin form
    const clienteSigninForm = document.getElementById('clienteSigninForm');
    if (clienteSigninForm) {
        clienteSigninForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSignin(this, 'cliente');
        });
    }

    // Handle prestador signin form
    const prestadorSigninForm = document.getElementById('prestadorSigninForm');
    if (prestadorSigninForm) {
        prestadorSigninForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSignin(this, 'prestador');
        });
    }

    // Handle signup forms (if they exist)
    const clienteSignupForm = document.getElementById('clienteSignupForm');
    if (clienteSignupForm) {
        clienteSignupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSignup(this, 'cliente');
        });
    }

    const prestadorSignupForm = document.getElementById('prestadorSignupForm');
    if (prestadorSignupForm) {
        prestadorSignupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleSignup(this, 'prestador');
        });
    }
});

/**
 * Handle signin form submission
 */
function handleSignin(form, userType) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const alertArea = form.querySelector('#alert-area');
    
    // Show loading state
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Entrando...';
    submitButton.disabled = true;
    
    // Clear previous alerts
    alertArea.innerHTML = '';
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message, alertArea);
            
            // Redirect after a short delay
            setTimeout(() => {
                if (data.data && data.data.redirect_url) {
                    window.location.href = data.data.redirect_url;
                } else {
                    // Fallback redirect
                    const redirectUrl = userType === 'cliente' ? 
                        'cliente-dashboard.html' : 
                        'prestador-dashboard.html';
                    window.location.href = redirectUrl;
                }
            }, 1500);
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
 * Handle signup form submission
 */
function handleSignup(form, userType) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const alertArea = form.querySelector('#alert-area');
    
    // Validate password confirmation
    const password = form.querySelector('#password').value;
    const confirmPassword = form.querySelector('#confirm_password').value;
    
    if (password !== confirmPassword) {
        showAlert('danger', 'As senhas não coincidem', alertArea);
        return;
    }
    
    // Show loading state
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Cadastrando...';
    submitButton.disabled = true;
    
    // Clear previous alerts
    alertArea.innerHTML = '';
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showAlert('success', data.message, alertArea);
            
            // Redirect to signin page after success
            setTimeout(() => {
                // Redirecionar para página unificada
                window.location.href = 'login/index.html';
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

