document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const alertContainer = document.getElementById('alertContainer');
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const btnText = document.getElementById('btnText');
  const btnLoading = document.getElementById('btnLoading');

  // Toggle password visibility
  togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);

    const icon = this.querySelector('i');
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
  });

  // Show alert function
  function showAlert(message, type = 'danger') {
    alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
  }

  // Handle form submission
  loginForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    // Show loading state
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');

    // Clear previous alerts
    alertContainer.innerHTML = '';

    try {
      const formData = new FormData(this);

      // Log para debug
      console.log('Email:', formData.get('email'));
      console.log('Password:', formData.get('password'));
      console.log('Enviando para:', '../../php/admin/administrador-signin.php');

      const response = await fetch('../../php/admin/administrador-signin.php', {
        method: 'POST',
        body: formData
      });

      console.log('Response status:', response.status);

      const text = await response.text();
      console.log('Response text:', text);

      let data;
      try {
        data = JSON.parse(text);
      } catch (parseError) {
        console.error('Erro ao parsear JSON:', parseError);
        console.log('Texto recebido:', text);
        throw new Error('Resposta inválida do servidor');
      }

      if (data.success) {
        showAlert('Login realizado com sucesso! Redirecionando...', 'success');

        // Aguardar um momento e redirecionar
        setTimeout(() => {
          // Caminho correto para o dashboard
          window.location.href = '../administrador-dashboard.html';
        }, 1500);
      } else {
        showAlert(data.message || 'Erro ao fazer login');
      }
    } catch (error) {
      console.error('Erro no login:', error);
      showAlert('Erro de conexão. Verifique sua internet e tente novamente.');
    } finally {
      // Reset button state
      btnText.classList.remove('d-none');
      btnLoading.classList.add('d-none');
    }
  });
});

