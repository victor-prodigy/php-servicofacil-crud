# Como Adicionar um Novo Campo ao Formulário de Solicitação de Serviço

Este documento descreve o processo completo para adicionar um novo campo ao formulário de criação de solicitação de serviço no sistema ServiçoFácil.

## Visão Geral

Para adicionar um novo campo ao formulário de solicitação, você precisará modificar 4 arquivos principais:
1. **HTML** - Interface do usuário
2. **JavaScript** - Validação no frontend
3. **PHP** - Processamento no backend
4. **Banco de Dados** - Estrutura da tabela

## Estrutura dos Arquivos

- **HTML**: `client/servico/nova-solicitacao.html`
- **JavaScript**: `assets/js/nova-solicitacao.js`
- **PHP**: `php/servico/nova-solicitacao.php`
- **CSS**: `assets/css/forms.css` (se necessário estilização específica)

## Passo a Passo

### 1. Atualizar o Banco de Dados

Primeiro, adicione a nova coluna na tabela `service_request`:

```sql
ALTER TABLE service_request 
ADD COLUMN nome_do_campo INT DEFAULT 0;
```

**Exemplo prático** - Adicionando campo "prioridade":
```sql
ALTER TABLE service_request 
ADD COLUMN prioridade INT DEFAULT 1;
```

### 2. Modificar o HTML

Localize o formulário em `client/servico/nova-solicitacao.html` e adicione o novo campo:

```html
<!-- Adicione antes do botão de submit -->
<div class="mb-3">
    <label for="nome_do_campo" class="form-label">Nome do Campo *</label>
    <input 
        type="number" 
        class="form-control" 
        id="nome_do_campo" 
        name="nome_do_campo" 
        required
        min="1"
        max="10"
        placeholder="Digite o valor..."
    >
    <div class="invalid-feedback">
        Por favor, insira um valor válido.
    </div>
</div>
```

**Exemplo prático** - Campo prioridade:
```html
<div class="mb-3">
    <label for="prioridade" class="form-label">Prioridade *</label>
    <select class="form-select" id="prioridade" name="prioridade" required>
        <option value="">Selecione a prioridade</option>
        <option value="1">Baixa</option>
        <option value="2">Média</option>
        <option value="3">Alta</option>
        <option value="4">Urgente</option>
    </select>
    <div class="invalid-feedback">
        Por favor, selecione uma prioridade.
    </div>
</div>
```

### 3. Atualizar o JavaScript

Modifique `assets/js/nova-solicitacao.js` para incluir validação:

#### 3.1 Adicionar validação no evento submit:

```javascript
// Localize a função de validação e adicione:
const nomeDoCampo = document.getElementById('nome_do_campo').value;

if (!nomeDoCampo || nomeDoCampo < 1 || nomeDoCampo > 10) {
    document.getElementById('nome_do_campo').classList.add('is-invalid');
    isValid = false;
} else {
    document.getElementById('nome_do_campo').classList.remove('is-invalid');
}
```

#### 3.2 Incluir o campo no FormData:

```javascript
// Localize onde o FormData é criado e adicione:
formData.append('nome_do_campo', document.getElementById('nome_do_campo').value);
```

**Exemplo completo da validação**:
```javascript
document.getElementById('nova-solicitacao-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validações existentes...
    
    // Nova validação
    const prioridade = document.getElementById('prioridade').value;
    
    if (!prioridade) {
        document.getElementById('prioridade').classList.add('is-invalid');
        isValid = false;
    } else {
        document.getElementById('prioridade').classList.remove('is-invalid');
    }
    
    if (!isValid) {
        return;
    }
    
    // Criar FormData
    const formData = new FormData();
    formData.append('titulo', document.getElementById('titulo').value);
    formData.append('descricao', document.getElementById('descricao').value);
    formData.append('categoria', document.getElementById('categoria').value);
    formData.append('prioridade', prioridade); // Novo campo
    
    // Enviar dados...
});
```

### 4. Modificar o PHP

Atualize `php/servico/nova-solicitacao.php` para processar o novo campo:

#### 4.1 Validar o campo recebido:

```php
// Adicione após as validações existentes
if (!isset($_POST['nome_do_campo']) || empty($_POST['nome_do_campo'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do campo é obrigatório']);
    exit;
}

$nome_do_campo = (int)$_POST['nome_do_campo'];

if ($nome_do_campo < 1 || $nome_do_campo > 10) {
    echo json_encode(['success' => false, 'message' => 'Valor deve estar entre 1 e 10']);
    exit;
}
```

#### 4.2 Incluir na query de inserção:

```php
// Modifique a query SQL para incluir o novo campo
$sql = "INSERT INTO service_request (
    user_id, 
    titulo, 
    descricao, 
    categoria, 
    nome_do_campo,
    data_criacao
) VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);
$result = $stmt->execute([
    $_SESSION['user_id'],
    $titulo,
    $descricao,
    $categoria,
    $nome_do_campo
]);
```

**Exemplo completo do PHP**:
```php
<?php
session_start();
require_once '../conexao.php';

// Verificações de autenticação...

try {
    // Validações existentes...
    
    // Nova validação
    if (!isset($_POST['prioridade']) || empty($_POST['prioridade'])) {
        echo json_encode(['success' => false, 'message' => 'Prioridade é obrigatória']);
        exit;
    }
    
    $prioridade = (int)$_POST['prioridade'];
    
    if ($prioridade < 1 || $prioridade > 4) {
        echo json_encode(['success' => false, 'message' => 'Prioridade inválida']);
        exit;
    }
    
    // Inserir no banco
    $sql = "INSERT INTO service_request (
        user_id, 
        titulo, 
        descricao, 
        categoria, 
        prioridade,
        data_criacao
    ) VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $_SESSION['user_id'],
        $titulo,
        $descricao,
        $categoria,
        $prioridade
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Solicitação criada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
}
?>
```

## Tipos de Campo Suportados

### Campos Numéricos
```html
<input type="number" min="1" max="100" step="1">
```

### Campos de Seleção
```html
<select class="form-select">
    <option value="1">Opção 1</option>
    <option value="2">Opção 2</option>
</select>
```

### Campos de Texto
```html
<input type="text" maxlength="255">
```

### Campos de Data
```html
<input type="date" min="2024-01-01">
```

## Validações Recomendadas

### Frontend (JavaScript)
- Verificar se o campo não está vazio
- Validar range de valores numéricos
- Verificar comprimento de strings
- Validar formato de dados

### Backend (PHP)
- Sanitizar dados recebidos
- Validar tipos de dados
- Verificar ranges permitidos
- Escapar caracteres especiais

## Testando as Modificações

1. **Teste de Interface**:
   - Abrir `client/servico/solicitar-servico.html`
   - Verificar se o campo aparece corretamente
   - Testar validação de campo vazio

2. **Teste de Validação**:
   - Tentar enviar com valores inválidos
   - Verificar mensagens de erro
   - Testar valores nos limites

3. **Teste de Banco**:
   - Criar uma solicitação válida
   - Verificar se os dados foram salvos
   - Consultar a tabela: `SELECT * FROM service_request ORDER BY id DESC LIMIT 1;`

## Troubleshooting

### Erros Comuns

1. **Campo não aparece**:
   - Verificar se o HTML está correto
   - Verificar cache do navegador

2. **Validação não funciona**:
   - Verificar console do navegador
   - Verificar se o ID do campo está correto

3. **Erro no PHP**:
   - Verificar logs de erro do servidor
   - Verificar se a coluna existe no banco

4. **Dados não salvam**:
   - Verificar se a query SQL está correta
   - Verificar se todos os parâmetros estão sendo passados

### Comandos Úteis

```sql
-- Verificar estrutura da tabela
DESCRIBE service_request;

-- Ver últimas solicitações
SELECT * FROM service_request ORDER BY id DESC LIMIT 5;

-- Verificar se coluna existe
SHOW COLUMNS FROM service_request LIKE 'nome_do_campo';
```

## Observações Importantes

- Sempre faça backup do banco antes de modificar a estrutura
- Teste em ambiente de desenvolvimento primeiro
- Mantenha consistência nos nomes de campos (snake_case)
- Documente as mudanças para a equipe
- Considere migração de dados existentes se necessário

## Exemplo de Migração Completa

Para adicionar um campo "prazo_dias" (número de dias para conclusão):

1. **SQL**: `ALTER TABLE service_request ADD COLUMN prazo_dias INT DEFAULT 7;`
2. **HTML**: Campo number com min="1" max="365"
3. **JS**: Validação de range 1-365 dias
4. **PHP**: Validação e inserção do campo

Isso criará um campo funcional para definir prazo em dias para cada solicitação de serviço.
