document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const prestadorId = urlParams.get('id');

    if (!prestadorId) {
        alert('ID do prestador não fornecido');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    carregarPerfilPrestador(prestadorId);
    
    // Configurar data mínima para o campo de data
    const dataPreferencial = document.getElementById('dataPreferencial');
    if (dataPreferencial) {
        const hoje = new Date().toISOString().split('T')[0];
        dataPreferencial.min = hoje;
    }
});

async function carregarPerfilPrestador(id) {
    try {
        const response = await fetch(`../../php/prestador/perfil.php?id=${id}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const prestador = data.prestador;

        // Atualizar informações básicas
        document.getElementById('prestadorNome').textContent = prestador.nome;
        document.getElementById('prestadorCategoria').textContent = prestador.categoria;
        if (prestador.foto) {
            document.getElementById('prestadorFoto').src = prestador.foto;
        }

        // Avaliação
        document.getElementById('prestadorAvaliacao').innerHTML = `
            ${formatarEstrelas(prestador.avaliacao_media)}
            <br>
            <small class="text-muted">${prestador.total_avaliacoes} avaliações</small>
        `;

        // Localização
        document.getElementById('prestadorLocalizacao').innerHTML = `
            <i class="bi bi-geo-alt"></i> ${prestador.cidade}, ${prestador.estado}
        `;

        // Certificado
        if (prestador.certificado) {
            document.getElementById('prestadorCertificado').innerHTML = `
                <div class="alert alert-success mb-0">
                    <i class="bi bi-patch-check-fill"></i> Profissional Certificado
                </div>
            `;
        }

        // Contatos
        document.getElementById('prestadorContatos').innerHTML = `
            <p><i class="bi bi-telephone"></i> ${prestador.telefone}</p>
            <p><i class="bi bi-envelope"></i> ${prestador.email}</p>
            ${prestador.whatsapp ? `<p><i class="bi bi-whatsapp"></i> ${prestador.whatsapp}</p>` : ''}
        `;

        // Disponibilidade
        document.getElementById('prestadorDisponibilidade').innerHTML = `
            <p><strong>Status:</strong> ${prestador.status_disponibilidade}</p>
            <p><strong>Horários:</strong></p>
            <ul class="list-unstyled">
                ${prestador.horarios.map(h => `<li>${h}</li>`).join('')}
            </ul>
        `;

        // Sobre
        document.getElementById('prestadorSobre').textContent = prestador.sobre;

        // Certificações
        const certContainer = document.getElementById('prestadorCertificacoes');
        if (prestador.certificacoes && prestador.certificacoes.length > 0) {
            certContainer.innerHTML = prestador.certificacoes.map(cert => `
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6>${cert.titulo}</h6>
                            <p class="text-muted mb-0">
                                <small>${cert.instituicao} - ${cert.ano}</small>
                            </p>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            certContainer.innerHTML = '<p class="text-muted">Nenhuma certificação cadastrada.</p>';
        }

        // Portfólio
        const portfolioContainer = document.getElementById('prestadorPortfolio');
        if (prestador.portfolio && prestador.portfolio.length > 0) {
            portfolioContainer.innerHTML = prestador.portfolio.map(item => `
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <img src="${item.imagem}" class="card-img-top" alt="${item.titulo}">
                        <div class="card-body">
                            <h6>${item.titulo}</h6>
                            <p class="text-muted mb-0"><small>${item.descricao}</small></p>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            portfolioContainer.innerHTML = '<p class="text-muted">Nenhum item no portfólio.</p>';
        }

        // Avaliações
        const avaliacoesContainer = document.getElementById('prestadorAvaliacoes');
        if (prestador.avaliacoes && prestador.avaliacoes.length > 0) {
            avaliacoesContainer.innerHTML = prestador.avaliacoes.map(av => `
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6>${av.cliente_nome}</h6>
                            <p class="text-warning mb-1">${'★'.repeat(av.nota)}${'☆'.repeat(5-av.nota)}</p>
                        </div>
                        <small class="text-muted">${new Date(av.data).toLocaleDateString('pt-BR')}</small>
                    </div>
                    <p class="mb-0">${av.comentario}</p>
                </div>
            `).join('');
        } else {
            avaliacoesContainer.innerHTML = '<p class="text-muted">Nenhuma avaliação ainda.</p>';
        }

    } catch (error) {
        console.error('Erro ao carregar perfil:', error);
        alert('Erro ao carregar dados do prestador. Por favor, tente novamente.');
    }
}

function formatarEstrelas(avaliacao) {
    const estrelas = '★'.repeat(Math.floor(avaliacao)) + '☆'.repeat(5 - Math.floor(avaliacao));
    return `<span class="text-warning">${estrelas}</span> (${avaliacao.toFixed(1)})`;
}

// Modal de Orçamento
function solicitarOrcamento() {
    const modal = new bootstrap.Modal(document.getElementById('orcamentoModal'));
    modal.show();
}

async function enviarOrcamento() {
    const descricao = document.getElementById('descricaoServico').value;
    const data = document.getElementById('dataPreferencial').value;
    const orcamento = document.getElementById('orcamentoEstimado').value;

    if (!descricao || !data) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return;
    }

    const prestadorId = new URLSearchParams(window.location.search).get('id');
    
    try {
        const formData = new FormData();
        formData.append('prestador_id', prestadorId);
        formData.append('descricao', descricao);
        formData.append('data_preferencial', data);
        formData.append('orcamento_estimado', orcamento);

        const response = await fetch('../../php/orcamento/solicitar.php', {
            method: 'POST',
            body: formData
        });

        const responseData = await response.json();

        if (responseData.success) {
            alert('Solicitação de orçamento enviada com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('orcamentoModal')).hide();
        } else {
            throw new Error(responseData.message);
        }

    } catch (error) {
        console.error('Erro ao enviar orçamento:', error);
        alert('Erro ao enviar solicitação de orçamento. Por favor, tente novamente.');
    }
}

// Função de mensagem (pode ser implementada posteriormente)
function enviarMensagem() {
    alert('Funcionalidade de mensagens em desenvolvimento.');
}