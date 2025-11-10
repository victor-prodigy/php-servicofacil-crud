// Validação e submissão do formulário de alteração de solicitação de serviço
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM carregado, inicializando formulário de alteração...');

    // Obter ID da solicitação da URL
    const urlParams = new URLSearchParams(window.location.search);
    const solicitacaoId = urlParams.get('id');

    if (!solicitacaoId) {
        alert('ID da solicitação não fornecido. Redirecionando...');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    // Verificar se todos os elementos necessários existem
    const elementosNecessarios = {
        form: 'alterarServicoForm',
        submitBtn: 'submit-btn',
        alertArea: 'alert-area',
        titulo: 'titulo',
        categoria: 'categoria',
        descricao: 'descricao',
        endereco: 'endereco',
        cidade: 'cidade',
        prazoDesejado: 'prazo_desejado',
        orcamentoMaximo: 'orcamento_maximo',
        observacoes: 'observacoes'
    };

    const elementos = {};
    let todosElementosEncontrados = true;

    // Verificar cada elemento
    for (const [nome, id] of Object.entries(elementosNecessarios)) {
        elementos[nome] = document.getElementById(id);
        if (!elementos[nome]) {
            console.error(`Elemento '${nome}' com ID '${id}' não encontrado!`);
            todosElementosEncontrados = false;
        } else {
            console.log(`✓ Elemento '${nome}' encontrado`);
        }
    }

    if (!todosElementosEncontrados) {
        console.error('Nem todos os elementos necessários foram encontrados. Abortando inicialização.');
        return;
    }

    console.log('Todos os elementos encontrados, carregando dados da solicitação...');

    // Função para mostrar alertas
    function mostrarAlerta(tipo, titulo, mensagem) {
        console.log('Mostrando alerta:', tipo, titulo, mensagem);
        const alertClass = tipo === 'sucesso' ? 'alert-success' : 'alert-danger';
        const icon = tipo === 'sucesso' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

        elementos.alertArea.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>
                <strong>${titulo}</strong> ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Scroll para o alerta
        elementos.alertArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Função para converter data em prazo texto
    function convertDateToPrazo(data) {
        if (!data) return '';
        
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0); // Resetar horas para comparação precisa
        
        const prazo = new Date(data);
        prazo.setHours(0, 0, 0, 0);
        
        const diffTime = prazo - hoje;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        // Mapear para as opções do formulário
        if (diffDays <= 1) {
            return 'Urgente (até 24h)';
        } else if (diffDays <= 3) {
            return 'Até 3 dias';
        } else if (diffDays <= 7) {
            return 'Até 1 semana';
        } else if (diffDays <= 14) {
            return 'Até 2 semanas';
        } else {
            return 'Sem pressa';
        }
    }

    // Função para carregar dados da solicitação
    async function carregarSolicitacao() {
        try {
            console.log('Carregando solicitação ID:', solicitacaoId);
            
            const response = await fetch(`../../php/servico/obter-solicitacao.php?id=${solicitacaoId}`);
            
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Resposta bruta:', responseText);

            const data = JSON.parse(responseText);

            if (!data.success) {
                throw new Error(data.message || 'Erro ao carregar solicitação');
            }

            const solicitacao = data.solicitacao;

            // Preencher formulário com os dados
            elementos.titulo.value = solicitacao.titulo || '';
            elementos.categoria.value = solicitacao.categoria || '';
            elementos.descricao.value = solicitacao.descricao || '';
            elementos.endereco.value = solicitacao.endereco || '';
            elementos.cidade.value = solicitacao.cidade || '';
            
            // Converter prazo_desejado (data) para texto
            if (solicitacao.prazo_desejado) {
                elementos.prazoDesejado.value = convertDateToPrazo(solicitacao.prazo_desejado);
            }
            
            if (solicitacao.orcamento_maximo) {
                elementos.orcamentoMaximo.value = parseFloat(solicitacao.orcamento_maximo).toFixed(2);
            }
            
            if (solicitacao.observacoes) {
                elementos.observacoes.value = solicitacao.observacoes;
            }

            console.log('Dados carregados com sucesso!');

        } catch (error) {
            console.error('Erro ao carregar solicitação:', error);
            mostrarAlerta('erro', 'Erro:', 'Não foi possível carregar os dados da solicitação. ' + error.message);
            
            setTimeout(() => {
                window.location.href = '../cliente-dashboard.html';
            }, 3000);
        }
    }

    // Função de validação
    function validarFormulario() {
        console.log('Validando formulário...');

        // Validar campos obrigatórios
        const validacoes = [
            { elemento: elementos.titulo, nome: 'Título', maxLength: 100 },
            { elemento: elementos.categoria, nome: 'Categoria' },
            { elemento: elementos.descricao, nome: 'Descrição', maxLength: 500 },
            { elemento: elementos.endereco, nome: 'Endereço', maxLength: 200 },
            { elemento: elementos.cidade, nome: 'Cidade', maxLength: 100 },
            { elemento: elementos.prazoDesejado, nome: 'Prazo desejado' }
        ];

        for (const validacao of validacoes) {
            const { elemento, nome, maxLength } = validacao;

            if (!elemento.value.trim()) {
                mostrarAlerta('erro', 'Erro:', `${nome} é obrigatório`);
                elemento.focus();
                return false;
            }

            if (maxLength && elemento.value.trim().length > maxLength) {
                mostrarAlerta('erro', 'Erro:', `${nome} deve ter no máximo ${maxLength} caracteres`);
                elemento.focus();
                return false;
            }
        }

        // Validar orçamento se preenchido
        if (elementos.orcamentoMaximo.value.trim()) {
            const valorOrcamento = parseFloat(elementos.orcamentoMaximo.value);
            if (isNaN(valorOrcamento) || valorOrcamento <= 0 || valorOrcamento > 99999.99) {
                mostrarAlerta('erro', 'Erro:', 'Orçamento deve estar entre R$ 0,01 e R$ 99.999,99');
                elementos.orcamentoMaximo.focus();
                return false;
            }
        }

        console.log('Validação passou com sucesso!');
        return true;
    }

    // Função para enviar o formulário
    async function enviarFormulario(dadosForm) {
        try {
            console.log('Enviando formulário...', dadosForm);
            elementos.submitBtn.disabled = true;
            elementos.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';

            // Adicionar ID da solicitação
            dadosForm.append('solicitacao_id', solicitacaoId);

            const response = await fetch('../../php/servico/altera-solicitacao.php', {
                method: 'POST',
                body: dadosForm
            });

            console.log('Status da resposta:', response.status);

            const responseText = await response.text();
            console.log('Resposta bruta:', responseText);

            // Verificar se a resposta é JSON válido
            let resultado;
            try {
                resultado = JSON.parse(responseText);
                console.log('Resultado parseado:', resultado);
            } catch (jsonError) {
                console.error('Erro ao parsear JSON:', jsonError);
                throw new Error('Resposta inválida do servidor: ' + responseText.substring(0, 100));
            }

            if (response.ok && resultado.success) {
                mostrarAlerta('sucesso', 'Sucesso!', resultado.message);

                // Redirecionar após 2 segundos
                setTimeout(() => {
                    window.location.href = '../cliente-dashboard.html';
                }, 2000);

            } else {
                // Tratar diferentes tipos de erro
                let mensagemErro = resultado.message || 'Erro desconhecido';

                // Verificar se é erro de autenticação
                if (response.status === 401) {
                    mensagemErro = 'Sessão expirada. Faça login novamente.';
                    setTimeout(() => {
                        window.location.href = '../login/index.html';
                    }, 2000);
                }

                mostrarAlerta('erro', 'Erro:', mensagemErro);
            }

        } catch (error) {
            console.error('Erro na requisição:', error);
            mostrarAlerta('erro', 'Erro de conexão:', 'Não foi possível salvar as alterações. Verifique sua conexão e tente novamente.');
        } finally {
            elementos.submitBtn.disabled = false;
            elementos.submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Salvar Alterações';
        }
    }

    // Event listener para o formulário
    elementos.form.addEventListener('submit', function (e) {
        e.preventDefault();
        console.log('Formulário submetido!');

        if (!validarFormulario()) {
            console.log('Validação falhou');
            return;
        }

        console.log('Validação passou, enviando dados...');

        // Preparar dados
        const dadosForm = new FormData(elementos.form);

        // Log dos dados para debug
        console.log('Dados do formulário:');
        for (let [key, value] of dadosForm.entries()) {
            console.log(key + ':', value);
        }

        // Enviar requisição
        enviarFormulario(dadosForm);
    });

    // Carregar dados da solicitação ao inicializar
    carregarSolicitacao();

    console.log('Formulário de alteração configurado com sucesso!');
});

