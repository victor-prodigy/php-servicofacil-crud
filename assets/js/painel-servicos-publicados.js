// Função para fazer logout
function logout() {
    localStorage.clear();
    fetch('../../php/logout.php')
        .then(() => {
            window.location.href = '../login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            // Mesmo com erro, redireciona para garantir que o usuário saia
            window.location.href = '../login/index.html';
        });
}

// Função para carregar postagens
async function carregarPostagens() {
    const container = document.getElementById('servicesContainer');

    // Mostrar loading
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-3 text-muted">Carregando serviços...</p>
        </div>
    `;

    try {
        const response = await fetch('../../php/prestador/listar-postagens-prestador.php');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('Erro ao parsear JSON:', parseError);
            console.error('Resposta do servidor:', text);
            throw new Error('Resposta inválida do servidor');
        }

        if (data.success) {
            if (data.postagens && data.postagens.length > 0) {
                renderizarPostagens(data.postagens);
            } else {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">Nenhuma postagem encontrada</h4>
                        <p class="text-muted">Comece criando sua primeira postagem de serviço!</p>
                        <a href="postar-servico.html" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Criar Primeira Postagem
                        </a>
                    </div>
                `;
            }
        } else {
            container.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> ${data.message || 'Erro ao carregar postagens'}
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        container.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Erro ao carregar postagens: ${error.message}. Por favor, verifique o console para mais detalhes.
            </div>
        `;
    }
}

// Função para renderizar postagens
function renderizarPostagens(postagens) {
    const container = document.getElementById('servicesContainer');

    let html = '<div class="row">';

    postagens.forEach(postagem => {
        const disponibilidadeBadge = postagem.disponibilidade === 'disponivel'
            ? '<span class="badge badge-disponivel">Disponível</span>'
            : '<span class="badge badge-indisponivel">Indisponível</span>';

        const statusBadge = postagem.status === 'ativo'
            ? '<span class="badge badge-ativo">Ativo</span>'
            : '<span class="badge badge-inativo">Inativo</span>';

        html += `
            <div class="col-md-6 mb-4">
                <div class="card service-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${escapeHtml(postagem.titulo)}</h5>
                            <div>
                                ${disponibilidadeBadge}
                                ${statusBadge}
                            </div>
                        </div>
                        <p class="card-text text-muted mb-2">
                            <small><i class="bi bi-tag"></i> ${escapeHtml(postagem.categoria)}</small>
                        </p>
                        <p class="card-text">${escapeHtml(postagem.descricao.substring(0, 150))}${postagem.descricao.length > 150 ? '...' : ''}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <h4 class="text-primary mb-0">R$ ${postagem.preco}</h4>
                                <small class="text-muted">Criado em: ${postagem.created_at}</small>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary btn-action" onclick="editarPostagem(${postagem.service_id})">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger btn-action" onclick="excluirPostagem(${postagem.service_id}, '${escapeHtml(postagem.titulo)}')">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}

// Função para editar postagem
async function editarPostagem(serviceId) {
    try {
        const response = await fetch(`../../php/prestador/obter-postagem-servico.php?service_id=${serviceId}`);
        const data = await response.json();

        if (data.success && data.postagem) {
            const postagem = data.postagem;

            // Preencher formulário do modal
            document.getElementById('editServiceId').value = postagem.service_id;
            document.getElementById('editTitulo').value = postagem.titulo;
            document.getElementById('editDescricao').value = postagem.descricao;
            document.getElementById('editCategoria').value = postagem.categoria;
            document.getElementById('editPreco').value = postagem.preco;
            document.getElementById('editDisponibilidade').value = postagem.disponibilidade;

            // Abrir modal
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        } else {
            alert('Erro ao carregar postagem: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar postagem. Por favor, tente novamente.');
    }
}

// Função para salvar edição
async function salvarEdicao() {
    const form = document.getElementById('editForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('../../php/prestador/editar-postagem-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Postagem atualizada com sucesso!');
            const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
            modal.hide();
            carregarPostagens();
        } else {
            alert('Erro ao atualizar postagem: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao atualizar postagem. Por favor, tente novamente.');
    }
}

// Função para excluir postagem
async function excluirPostagem(serviceId, titulo) {
    if (!confirm(`Tem certeza que deseja excluir a postagem "${titulo}"? Esta ação não pode ser desfeita.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('service_id', serviceId);

    try {
        const response = await fetch('../../php/prestador/apagar-postagem-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Postagem excluída com sucesso!');
            carregarPostagens();
        } else {
            alert('Erro ao excluir postagem: ' + data.message);
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao excluir postagem. Por favor, tente novamente.');
    }
}

// Função para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Carregar postagens ao carregar a página
document.addEventListener('DOMContentLoaded', function () {
    // Verificar autenticação do prestador
    checkAuthentication();
});

// Função para verificar autenticação
async function checkAuthentication() {
    try {
        const response = await fetch('../../php/prestador/prestador-dashboard.php');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.authenticated) {
            alert(data.message || data.msg || 'Acesso negado. Faça login como prestador.');
            window.location.href = '../../client/login/index.html';
            return;
        }

        // Se estiver autenticado, carregar postagens
        carregarPostagens();
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = '../../client/login/index.html';
    }
}

