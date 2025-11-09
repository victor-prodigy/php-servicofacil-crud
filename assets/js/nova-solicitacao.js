// Validação e submissão do formulário de solicitação de serviço
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM carregado, inicializando formulário...');

    // Verificar se todos os elementos necessários existem
    const elementosNecessarios = {
        form: 'solicitarServicoForm',
        submitBtn: 'submit-btn',
        alertArea: 'alert-area',
        titulo: 'titulo',
        categoria: 'categoria',
        descricao: 'descricao',
        endereco: 'endereco',
        cidade: 'cidade',
        prazoDesejado: 'prazo_desejado',
        orcamentoMaximo: 'orcamento_maximo'
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

    console.log('Todos os elementos encontrados, configurando eventos...');

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

    // Validação básica do formulário
    function validarFormulario() {
        console.log('Validando formulário...');

        const camposObrigatorios = ['titulo', 'categoria', 'descricao', 'endereco', 'cidade', 'prazoDesejado'];
        let valido = true;

        for (const campo of camposObrigatorios) {
            const elemento = elementos[campo];
            if (!elemento || !elemento.value.trim()) {
                console.log('Campo obrigatório vazio:', campo);
                valido = false;
                if (elemento) elemento.classList.add('is-invalid');
            } else {
                if (elemento) elemento.classList.remove('is-invalid');
            }
        }

        if (!valido) {
            mostrarAlerta('erro', 'Erro:', 'Por favor, preencha todos os campos obrigatórios.');
        }

        return valido;
    }

    // Função para enviar o formulário
    async function enviarFormulario(dadosForm) {
        try {
            console.log('Enviando formulário...', dadosForm);
            elementos.submitBtn.disabled = true;
            elementos.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

            const response = await fetch('../../php/servico/nova-solicitacao.php', {
                method: 'POST',
                body: dadosForm
            });

            console.log('Status da resposta:', response.status);
            console.log('Headers da resposta:', response.headers);

            // Primeiro, pegar o texto da resposta para debug
            const responseText = await response.text();
            console.log('Resposta bruta:', responseText);

            // Verificar se a resposta é JSON válido
            let resultado;
            try {
                resultado = JSON.parse(responseText);
                console.log('Resultado parseado:', resultado);
            } catch (jsonError) {
                console.error('Erro ao parsear JSON:', jsonError);
                console.error('Texto da resposta:', responseText);
                throw new Error('Resposta inválida do servidor: ' + responseText.substring(0, 100));
            }

            if (response.ok && resultado.success) {
                mostrarAlerta('sucesso', 'Sucesso!', resultado.message);

                // Limpar formulário
                elementos.form.reset();
                document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                    el.classList.remove('is-valid', 'is-invalid');
                });

                // Redirecionar após 3 segundos
                setTimeout(() => {
                    if (resultado.redirect) {
                        window.location.href = resultado.redirect;
                    } else {
                        window.location.href = '../cliente-dashboard.html';
                    }
                }, 3000);

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
            mostrarAlerta('erro', 'Erro de conexão:', 'Não foi possível enviar a solicitação. Verifique sua conexão e tente novamente.');
        } finally {
            elementos.submitBtn.disabled = false;
            elementos.submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Solicitar Serviço';
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

        // Desabilitar botão
        elementos.submitBtn.disabled = true;
        elementos.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

        // Enviar requisição
        fetch('../../php/servico/nova-solicitacao.php', {
            method: 'POST',
            body: dadosForm
        })
            .then(response => {
                console.log('Status da resposta:', response.status);
                return response.text();
            })
            .then(responseText => {
                console.log('Resposta bruta:', responseText);

                try {
                    const resultado = JSON.parse(responseText);
                    console.log('Resultado parseado:', resultado);

                    if (resultado.success) {
                        mostrarAlerta('sucesso', 'Sucesso!', resultado.message);

                        // Limpar formulário
                        elementos.form.reset();

                        // Redirecionar após 3 segundos
                        setTimeout(() => {
                            window.location.href = '../cliente-dashboard.html';
                        }, 3000);
                    } else {
                        mostrarAlerta('erro', 'Erro:', resultado.message || 'Erro desconhecido');
                    }
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    mostrarAlerta('erro', 'Erro:', 'Resposta inválida do servidor: ' + responseText.substring(0, 200));
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                mostrarAlerta('erro', 'Erro de conexão:', error.message);
            })
            .finally(() => {
                // Reabilitar botão
                elementos.submitBtn.disabled = false;
                elementos.submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Solicitar Serviço';
            });
    });

    console.log('Formulário configurado com sucesso!');

    // Função de validação corrigida e simplificada
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


});

