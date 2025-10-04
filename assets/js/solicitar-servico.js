// Validação e submissão do formulário de solicitação de serviço
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('solicitarServicoForm');
    const submitBtn = document.getElementById('submit-btn');
    const alertArea = document.getElementById('alert-area');

    // Função para mostrar alertas
    function mostrarAlerta(tipo, titulo, mensagem) {
        const alertClass = tipo === 'sucesso' ? 'alert-success' : 'alert-danger';
        const icon = tipo === 'sucesso' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
        
        alertArea.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>
                <strong>${titulo}</strong> ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Scroll para o alerta
        alertArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Função para limpar alertas
    function limparAlertas() {
        alertArea.innerHTML = '';
    }

    // Validação em tempo real dos campos
    function adicionarValidacaoTempoReal() {
        const campos = {
            'titulo': { max: 100, obrigatorio: true },
            'descricao': { max: 500, obrigatorio: true },
            'endereco': { max: 200, obrigatorio: true },
            'cidade': { max: 100, obrigatorio: true },
            'observacoes': { max: 300, obrigatorio: false }
        };

        Object.keys(campos).forEach(campoId => {
            const campo = document.getElementById(campoId);
            const config = campos[campoId];
            
            if (campo) {
                // Contador de caracteres
                if (config.max) {
                    const contadorDiv = document.createElement('div');
                    contadorDiv.className = 'form-text text-end contador-caracteres';
                    contadorDiv.style.fontSize = '0.8rem';
                    campo.parentNode.appendChild(contadorDiv);
                    
                    function atualizarContador() {
                        const atual = campo.value.length;
                        contadorDiv.textContent = `${atual}/${config.max}`;
                        contadorDiv.className = atual > config.max ? 
                            'form-text text-end contador-caracteres text-danger' : 
                            'form-text text-end contador-caracteres text-muted';
                    }
                    
                    atualizarContador();
                    campo.addEventListener('input', atualizarContador);
                }

                // Validação de obrigatoriedade
                campo.addEventListener('blur', function() {
                    if (config.obrigatorio && !campo.value.trim()) {
                        campo.classList.add('is-invalid');
                    } else if (config.max && campo.value.length > config.max) {
                        campo.classList.add('is-invalid');
                    } else {
                        campo.classList.remove('is-invalid');
                        campo.classList.add('is-valid');
                    }
                });
            }
        });

        // Validação do select de categoria
        const categoria = document.getElementById('categoria');
        categoria.addEventListener('change', function() {
            if (categoria.value) {
                categoria.classList.remove('is-invalid');
                categoria.classList.add('is-valid');
            } else {
                categoria.classList.add('is-invalid');
            }
        });

        // Validação do select de prazo
        const prazo = document.getElementById('prazo_desejado');
        prazo.addEventListener('change', function() {
            if (prazo.value) {
                prazo.classList.remove('is-invalid');
                prazo.classList.add('is-valid');
            } else {
                prazo.classList.add('is-invalid');
            }
        });

        // Validação do orçamento
        const orcamento = document.getElementById('orcamento_maximo');
        orcamento.addEventListener('input', function() {
            const valor = parseFloat(orcamento.value);
            if (orcamento.value && (isNaN(valor) || valor < 0 || valor > 99999.99)) {
                orcamento.classList.add('is-invalid');
            } else {
                orcamento.classList.remove('is-invalid');
                if (orcamento.value) {
                    orcamento.classList.add('is-valid');
                }
            }
        });
    }

    // Função de validação completa do formulário
    function validarFormulario() {
        let valido = true;
        const erros = [];

        // Campos obrigatórios
        const camposObrigatorios = [
            { id: 'titulo', nome: 'Título' },
            { id: 'categoria', nome: 'Categoria' },
            { id: 'descricao', nome: 'Descrição' },
            { id: 'endereco', nome: 'Endereço' },
            { id: 'cidade', nome: 'Cidade' },
            { id: 'prazo_desejado', nome: 'Prazo desejado' }
        ];

        camposObrigatorios.forEach(campo => {
            const elemento = document.getElementById(campo.id);
            if (!elemento.value.trim()) {
                elemento.classList.add('is-invalid');
                erros.push(`${campo.nome} é obrigatório`);
                valido = false;
            }
        });

        // Validação de tamanho dos campos
        const limitesCaracteres = [
            { id: 'titulo', max: 100, nome: 'Título' },
            { id: 'descricao', max: 500, nome: 'Descrição' },
            { id: 'endereco', max: 200, nome: 'Endereço' },
            { id: 'cidade', max: 100, nome: 'Cidade' },
            { id: 'observacoes', max: 300, nome: 'Observações' }
        ];

        limitesCaracteres.forEach(campo => {
            const elemento = document.getElementById(campo.id);
            if (elemento.value.length > campo.max) {
                elemento.classList.add('is-invalid');
                erros.push(`${campo.nome} deve ter no máximo ${campo.max} caracteres`);
                valido = false;
            }
        });

        // Validação do orçamento
        const orcamento = document.getElementById('orcamento_maximo');
        if (orcamento.value) {
            const valor = parseFloat(orcamento.value);
            if (isNaN(valor) || valor < 0) {
                orcamento.classList.add('is-invalid');
                erros.push('Orçamento deve ser um valor positivo');
                valido = false;
            } else if (valor > 99999.99) {
                orcamento.classList.add('is-invalid');
                erros.push('Orçamento máximo é R$ 99.999,99');
                valido = false;
            }
        }

        if (!valido) {
            mostrarAlerta('erro', 'Dados inválidos:', erros.join(', '));
        }

        return valido;
    }

    // Função para enviar o formulário
    async function enviarFormulario(dadosForm) {
        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';

            const response = await fetch('../../php/servico/solicitar-servico.php', {
                method: 'POST',
                body: dadosForm
            });

            // Verificar se a resposta é JSON válido
            let resultado;
            try {
                resultado = await response.json();
            } catch (jsonError) {
                console.error('Erro ao parsear JSON:', jsonError);
                throw new Error('Resposta inválida do servidor');
            }

            if (response.ok && resultado.sucesso) {
                mostrarAlerta('sucesso', 'Sucesso!', resultado.mensagem);
                
                // Limpar formulário
                form.reset();
                document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                    el.classList.remove('is-valid', 'is-invalid');
                });

                // Redirecionar após 3 segundos
                setTimeout(() => {
                    window.location.href = resultado.redirect;
                }, 3000);

            } else {
                // Tratar diferentes tipos de erro
                let mensagemErro = 'Erro desconhecido';
                
                if (resultado.erro) {
                    mensagemErro = resultado.erro;
                    if (resultado.detalhes && Array.isArray(resultado.detalhes)) {
                        mensagemErro += ': ' + resultado.detalhes.join(', ');
                    } else if (resultado.detalhes) {
                        mensagemErro += ': ' + resultado.detalhes;
                    }
                }
                
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
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Solicitar Serviço';
        }
    }

    // Event listener para o formulário
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        limparAlertas();

        if (validarFormulario()) {
            const dadosForm = new FormData(form);
            enviarFormulario(dadosForm);
        }
    });

    // Inicializar validação em tempo real
    adicionarValidacaoTempoReal();

    // Formatação automática do campo de orçamento
    const orcamentoInput = document.getElementById('orcamento_maximo');
    orcamentoInput.addEventListener('input', function() {
        let valor = this.value;
        
        // Remover caracteres não numéricos (exceto ponto e vírgula)
        valor = valor.replace(/[^\d.,]/g, '');
        
        // Substituir vírgula por ponto
        valor = valor.replace(',', '.');
        
        // Garantir apenas um ponto decimal
        const partes = valor.split('.');
        if (partes.length > 2) {
            valor = partes[0] + '.' + partes.slice(1).join('');
        }
        
        // Limitar casas decimais a 2
        if (partes[1] && partes[1].length > 2) {
            valor = partes[0] + '.' + partes[1].substring(0, 2);
        }
        
        this.value = valor;
    });

    // Confirmação ao sair da página com dados preenchidos
    let formModificado = false;
    form.addEventListener('input', () => formModificado = true);
    
    window.addEventListener('beforeunload', function(e) {
        if (formModificado && !form.querySelector('.alert-success')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
});