document.addEventListener('DOMContentLoaded', function() {
    // Verificar se o usuário está logado como cliente
    const userType = localStorage.getItem('userType');
    if (userType !== 'cliente') {
        alert('Acesso negado. Apenas clientes podem criar postagens de serviço.');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    const form = document.getElementById('servicoForm');
    
    // Definir data mínima para o prazo (hoje)
    const prazoInput = document.getElementById('prazo');
    const today = new Date().toISOString().split('T')[0];
    prazoInput.setAttribute('min', today);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validar orçamento
        const orcamento = document.getElementById('orcamento').value;
        if (orcamento <= 0) {
            alert('O orçamento deve ser maior que zero.');
            return;
        }

        // Criar objeto com os dados do formulário
        const formData = new FormData(form);
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Serviço publicado com sucesso!');
                window.location.href = '../cliente-dashboard.html';
            } else {
                alert('Erro ao publicar serviço: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar o formulário. Por favor, tente novamente.');
        }
    });
});