// Função para alternar a visibilidade dos filtros avançados
function toggleFiltrosAvancados() {
    const filtrosAvancados = document.getElementById('filtrosAvancados');
    if (filtrosAvancados.style.display === 'none') {
        filtrosAvancados.style.display = 'flex';
    } else {
        filtrosAvancados.style.display = 'none';
    }
}

// Função para formatar a avaliação em estrelas
function formatarEstrelas(avaliacao) {
    const estrelas = '★'.repeat(Math.floor(avaliacao)) + '☆'.repeat(5 - Math.floor(avaliacao));
    return `<span class="text-warning">${estrelas}</span> (${avaliacao.toFixed(1)})`;
}

// Função para formatar preço
function formatarPreco(preco) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(preco);
}

// Função para formatar distância
function formatarDistancia(distancia) {
    return `${distancia.toFixed(1)} km`;
}

// Função para criar o card de um prestador
function criarCardPrestador(prestador) {
    return `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">${prestador.nome}</h5>
                        <span class="badge bg-primary">${prestador.categoria}</span>
                    </div>
                    <p class="mb-1">
                        <i class="bi bi-star-fill text-warning"></i> 
                        ${formatarEstrelas(prestador.avaliacao)}
                    </p>
                    <p class="mb-1">
                        <i class="bi bi-cash"></i> 
                        Preço médio: ${formatarPreco(prestador.precoMedio)}
                    </p>
                    <p class="mb-1">
                        <i class="bi bi-geo-alt"></i>
                        ${formatarDistancia(prestador.distancia)} de distância
                    </p>
                    <p class="mb-3">
                        <i class="bi bi-clock"></i>
                        ${prestador.disponibilidade}
                    </p>
                    <div class="d-grid gap-2">
                        <a href="prestador/perfil.html?id=${prestador.id}" class="btn btn-outline-primary">
                            Ver Perfil Completo
                        </a>
                    </div>
                </div>
                ${prestador.certificado ? `
                <div class="card-footer text-center">
                    <i class="bi bi-patch-check-fill text-primary"></i> Profissional Certificado
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Função para realizar a busca de prestadores
async function buscarPrestadores(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    try {
        // Mostrar loading
        const resultadosDiv = document.getElementById('resultadosBusca');
        resultadosDiv.style.display = 'block';
        const listaResultados = document.getElementById('listaResultados');
        listaResultados.innerHTML = '<div class="col-12 text-center"><div class="spinner-border" role="status"></div></div>';

        // Fazer a requisição
        const response = await fetch(`../php/prestador/buscar.php?${params.toString()}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        // Limpar e mostrar resultados
        listaResultados.innerHTML = '';
        
        if (data.prestadores.length === 0) {
            listaResultados.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        Nenhum prestador encontrado com os critérios especificados.
                    </div>
                </div>
            `;
            return;
        }

        // Mostrar resultados
        data.prestadores.forEach(prestador => {
            listaResultados.innerHTML += criarCardPrestador(prestador);
        });

    } catch (error) {
        console.error('Erro na busca:', error);
        alert('Erro ao buscar prestadores. Por favor, tente novamente.');
    }
}

// Configurar o formulário quando o documento carregar
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', buscarPrestadores);
    }
});