document.addEventListener('DOMContentLoaded', function() {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/cliente/cliente-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para a página de login
            alert(data.message);
            window.location.href = './login/index.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza o nome do usuário
        document.getElementById('userName').textContent = data.nome;
        document.getElementById('dashboardContent').style.display = 'block';
        
        // Carregar a lista de serviços
        carregarServicos();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarServicos() {
    try {
        const response = await fetch('../php/servico/servico-listar.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">Nenhum serviço publicado ainda.</td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');
            
            // Formatar data
            const data = new Date(servico.data_postagem).toLocaleDateString('pt-BR');
            
            // Definir classe do status
            const statusClass = getStatusClass(servico.status);
            
            // Formatar orçamento
            const orcamento = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(servico.orcamento);

            row.innerHTML = `
                <td>
                    <a href="./servico/servico-detalhe.html?id=${servico.id}" class="text-decoration-none">
                        ${servico.titulo}
                    </a>
                </td>
                <td>${servico.categoria}</td>
                <td>${orcamento}</td>
                <td><span class="badge ${statusClass}">${servico.status}</span></td>
                <td>${data}</td>
                <td>
                    <a href="./servico/servico-editar.html?id=${servico.id}" class="btn btn-sm btn-primary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <button onclick="excluirServico(${servico.id})" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        alert('Erro ao carregar a lista de serviços. Por favor, tente novamente.');
    }
}

function getStatusClass(status) {
    const statusClasses = {
        'aberto': 'bg-success',
        'em_andamento': 'bg-warning',
        'concluido': 'bg-info',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

async function excluirServico(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('servico_id', id);

        const response = await fetch('../php/servico/servico-excluir.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Serviço excluído com sucesso!');
            carregarServicos(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        alert('Erro ao excluir serviço: ' + error.message);
    }
}

function logout() {
    // Limpar dados da sessão no localStorage
    localStorage.clear();
    
    // Fazer requisição para destruir a sessão no servidor
    fetch('../php/logout.php')
        .then(() => {
            // Redirecionar para a página de login
            window.location.href = './login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            // Mesmo com erro, redireciona para garantir que o usuário saia
            window.location.href = './login/index.html';
        });
}