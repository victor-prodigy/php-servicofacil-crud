document.addEventListener('DOMContentLoaded', function() {
    // Verificar autenticação do prestador
    checkAuthentication();
});

// Função para verificar autenticação
async function checkAuthentication() {
    try {
        const response = await fetch('../../php/prestador/prestador-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            alert(data.message || data.msg || 'Acesso negado. Faça login como prestador.');
            window.location.href = '../login/index.html';
            return;
        }

        // Se estiver autenticado, inicializar o formulário
        initializeForm();
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = '../login/index.html';
    }
}

// Função para inicializar o formulário
function initializeForm() {
    const form = document.getElementById('postagemForm');
    
    // Formatar preço enquanto digita
    const precoInput = document.getElementById('preco');
    precoInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d.,]/g, '');
        value = value.replace(',', '.');
        if (value && !isNaN(value)) {
            e.target.value = value;
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validar preço
        const preco = parseFloat(document.getElementById('preco').value);
        if (!preco || preco <= 0) {
            alert('O preço deve ser maior que zero.');
            return;
        }

        // Criar objeto com os dados do formulário
        const formData = new FormData(form);
        
        try {
            const response = await fetch('../../php/prestador/criar-postagem-servico.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                alert('Serviço publicado com sucesso!');
                window.location.href = 'painel-servicos-publicados.html';
            } else {
                alert('Erro ao publicar serviço: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar o formulário. Por favor, tente novamente.');
        }
    });
}

