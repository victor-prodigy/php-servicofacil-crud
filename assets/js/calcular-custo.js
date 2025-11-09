// Cálculo automático de custo do serviço em tempo real
document.addEventListener('DOMContentLoaded', function () {
  console.log('DOM carregado, inicializando calculadora de custo...');

  // Elementos do formulário
  const form = document.getElementById('calcularCustoForm');
  const tipoServico = document.getElementById('tipo_servico');
  const largura = document.getElementById('largura');
  const comprimento = document.getElementById('comprimento');
  const altura = document.getElementById('altura');
  const especificacoes = document.getElementById('especificacoes');
  const costEstimateArea = document.getElementById('cost-estimate-area');
  const alertArea = document.getElementById('alert-area');

  // Elementos de resultado
  const custoMateriais = document.getElementById('custo-materiais');
  const custoMaoObra = document.getElementById('custo-mao-obra');
  const custoTotal = document.getElementById('custo-total');

  // Variável para controlar debounce
  let timeoutId = null;

  // Função para formatar valor monetário
  function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    }).format(valor);
  }

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

  // Função para validar campos obrigatórios
  function validarCampos() {
    if (!tipoServico.value) {
      return false;
    }
    if (!largura.value || parseFloat(largura.value) <= 0) {
      return false;
    }
    if (!comprimento.value || parseFloat(comprimento.value) <= 0) {
      return false;
    }
    return true;
  }

  // Função para calcular custo
  async function calcularCusto() {
    // Limpar alertas anteriores
    alertArea.innerHTML = '';

    // Validar campos
    if (!validarCampos()) {
      costEstimateArea.style.display = 'none';
      return;
    }

    try {
      // Preparar dados do formulário
      const formData = new FormData();
      formData.append('tipo_servico', tipoServico.value);
      formData.append('largura', largura.value);
      formData.append('comprimento', comprimento.value);
      formData.append('altura', altura.value || 0);
      formData.append('especificacoes', especificacoes.value);

      // Enviar requisição
      const response = await fetch('../../php/servico/calcular-custo.php', {
        method: 'POST',
        body: formData
      });

      const responseText = await response.text();
      console.log('Resposta do servidor:', responseText);

      let resultado;
      try {
        resultado = JSON.parse(responseText);
      } catch (jsonError) {
        console.error('Erro ao parsear JSON:', jsonError);
        throw new Error('Resposta inválida do servidor');
      }

      if (resultado.success) {
        // Atualizar valores na interface
        custoMateriais.textContent = formatarMoeda(resultado.custo_materiais);
        custoMaoObra.textContent = formatarMoeda(resultado.custo_mao_obra);
        custoTotal.textContent = formatarMoeda(resultado.custo_total);

        // Mostrar área de resultado
        costEstimateArea.style.display = 'block';
        costEstimateArea.classList.add('show');

        // Scroll suave para o resultado
        setTimeout(() => {
          costEstimateArea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
      } else {
        mostrarAlerta('erro', 'Erro:', resultado.message || 'Erro ao calcular custo');
        costEstimateArea.style.display = 'none';
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
      mostrarAlerta('erro', 'Erro de conexão:', 'Não foi possível calcular o custo. Verifique sua conexão e tente novamente.');
      costEstimateArea.style.display = 'none';
    }
  }

  // Função para calcular com debounce (evitar muitas requisições)
  function calcularComDebounce() {
    // Limpar timeout anterior
    if (timeoutId) {
      clearTimeout(timeoutId);
    }

    // Aguardar 500ms antes de calcular (debounce)
    timeoutId = setTimeout(() => {
      calcularCusto();
    }, 500);
  }

  // Adicionar event listeners para cálculo em tempo real
  tipoServico.addEventListener('change', calcularComDebounce);
  largura.addEventListener('input', calcularComDebounce);
  comprimento.addEventListener('input', calcularComDebounce);
  altura.addEventListener('input', calcularComDebounce);
  especificacoes.addEventListener('change', calcularComDebounce);

  // Prevenir submissão do formulário (não é necessário enviar)
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    calcularCusto();
  });

  // Validação visual dos campos
  [largura, comprimento, altura].forEach(campo => {
    campo.addEventListener('blur', function () {
      const valor = parseFloat(this.value);
      if (this.value && (isNaN(valor) || valor < 0)) {
        this.classList.add('is-invalid');
      } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
      }
    });
  });

  console.log('Calculadora de custo inicializada com sucesso!');
});

