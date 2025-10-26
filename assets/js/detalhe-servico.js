document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const servicoId = urlParams.get('id');

    if (!servicoId) {
        alert('ID do serviço não fornecido');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    carregarDetalhesServico(servicoId);
});

async function carregarDetalhesServico(id) {
    try {
        const response = await fetch(`../../php/servico/detalhe-servico.php?id=${id}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const servico = data.servico;

        // Atualizar elementos da página
        document.getElementById('servicoTitulo').textContent = servico.titulo;
        document.getElementById('servicoCategoria').textContent = servico.categoria;
        document.getElementById('servicoDescricao').textContent = servico.descricao;
        document.getElementById('servicoOrcamento').textContent = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(servico.orcamento);
        document.getElementById('servicoPrazo').textContent = new Date(servico.prazo).toLocaleDateString('pt-BR');
        document.getElementById('servicoLocalizacao').textContent = servico.localizacao;
        
        // Configurar status com cor apropriada
        const statusElement = document.getElementById('servicoStatus');
        statusElement.textContent = servico.status;
        statusElement.className = `badge ${getStatusClass(servico.status)}`;

        // Configurar links dos botões
        document.getElementById('btnEditar').href = `servico-editar.html?id=${id}`;
        document.getElementById('btnExcluir').onclick = () => excluirServico(id);

    } catch (error) {
        console.error('Erro ao carregar detalhes do serviço:', error);
        alert('Erro ao carregar detalhes do serviço. Por favor, tente novamente.');
        window.location.href = '../cliente-dashboard.html';
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

        const response = await fetch('../../php/servico/apaga-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Serviço excluído com sucesso!');
            window.location.href = '../cliente-dashboard.html';
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        alert('Erro ao excluir serviço: ' + error.message);
    }
}