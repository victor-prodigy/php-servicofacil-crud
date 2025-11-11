# Guia de Implementação do Campo Instagram

Este guia documenta a implementação do campo **Instagram** no sistema ServiçoFácil, incluindo o cadastro de clientes e a exibição no dashboard do administrador.

---

## 📋 Índice

1. [Migração do Banco de Dados](#1-migração-do-banco-de-dados)
2. [Formulário de Cadastro (Frontend)](#2-formulário-de-cadastro-frontend)
3. [Processamento do Cadastro (Backend)](#3-processamento-do-cadastro-backend)
4. [Listagem de Usuários (Backend)](#4-listagem-de-usuários-backend)
5. [Dashboard do Administrador (Frontend)](#5-dashboard-do-administrador-frontend)
6. [JavaScript do Dashboard](#6-javascript-do-dashboard)

---

## 1. Migração do Banco de Dados

### Arquivo: `lib/add-instagram-field.sql`

```sql
-- ============================================
-- MIGRAÇÃO: Adicionar campo instagram à tabela user
-- ============================================
USE servicofacil;

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'servicofacil'
    AND TABLE_NAME = 'user'
    AND COLUMN_NAME = 'instagram'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `user` ADD COLUMN `instagram` VARCHAR(100) DEFAULT NULL AFTER `phone_number`',
    'SELECT ''Coluna instagram já existe na tabela user'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
```

**Execução:**

- Execute este script SQL no banco de dados MySQL antes de usar o campo Instagram
- O campo será adicionado na tabela `user` após a coluna `phone_number`
- Tipo: `VARCHAR(100)` - Permite até 100 caracteres
- Permite valores `NULL` (campo opcional)

---

## 2. Formulário de Cadastro (Frontend)

### Arquivo: `client/registro/index.html`

O campo Instagram foi adicionado na seção de campos específicos do cliente:

```html
<!-- Cliente-specific Fields -->
<div id="cliente-fields">
  <!-- Phone Field (Cliente only) -->
  <div class="mb-3 form-field">
    <label for="phone_number" class="form-label fw-medium">Telefone</label>
    <input
      type="tel"
      class="form-control"
      id="phone_number"
      name="phone_number"
      placeholder="(11) 99999-9999"
      maxlength="15"
    />
  </div>

  <!-- Instagram Field (Cliente only) -->
  <div class="mb-3 form-field">
    <label for="instagram" class="form-label fw-medium">Instagram</label>
    <input
      type="text"
      class="form-control"
      id="instagram"
      name="instagram"
      placeholder="@seuinstagram"
    />
  </div>
</div>
```

**Características:**

- Campo opcional (sem atributo `required`)
- Placeholder: `@seuinstagram`
- Visível apenas quando o perfil "Cliente" está selecionado
- Tipo de input: `text` (permite caracteres especiais como @)

---

## 3. Processamento do Cadastro (Backend)

### Arquivo: `php/cliente/cliente-signup.php`

#### 3.1. Coleta do Dado

```php
// 📋 Coleta de dados
$dados = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone_number' => $_POST['phone_number'] ?? '',
    'password' => $_POST['password'] ?? '',
    'instagram' => $_POST['instagram'] ?? ''  // ← Campo Instagram
];
```

#### 3.2. Verificação e Inserção

```php
function criarUsuario($pdo, $dados)
{
    // Verificar se a coluna instagram existe na tabela user
    $checkColumn = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                                 WHERE TABLE_SCHEMA = DATABASE()
                                 AND TABLE_NAME = 'user'
                                 AND COLUMN_NAME = 'instagram'");
    $result = $checkColumn->fetch(PDO::FETCH_ASSOC);
    $columnExists = ($result && $result['count'] > 0);

    if ($columnExists) {
        $sql = "INSERT INTO user (email, password, name, phone_number, instagram, user_type, identity_verified)
                VALUES (?, ?, ?, ?, ?, 'cliente', FALSE)";
        $stmt = $pdo->prepare($sql);
        $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);
        $instagram = !empty($dados['instagram']) ? $dados['instagram'] : null;

        if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number'], $instagram])) {
            return $pdo->lastInsertId();
        }
    } else {
        // Fallback: inserir sem instagram se a coluna não existir
        $sql = "INSERT INTO user (email, password, name, phone_number, user_type, identity_verified)
                VALUES (?, ?, ?, ?, 'cliente', FALSE)";
        // ... código de fallback
    }
    return false;
}
```

**Características:**

- Verifica se a coluna existe antes de inserir (compatibilidade)
- Converte string vazia em `NULL` para o banco
- Usa prepared statements para segurança

---

## 4. Listagem de Usuários (Backend)

### Arquivo: `php/admin/listar-usuarios.php`

#### 4.1. Query SQL

```php
$sql = "SELECT
            u.user_id,
            u.email,
            u.name,
            u.phone_number,
            u.instagram,  // ← Campo Instagram na query
            u.status,
            u.identity_verified,
            u.created_at,
            u.updated_at,
            CASE
                WHEN c.id IS NOT NULL THEN 'cliente'
                WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                ELSE 'indefinido'
            END as tipo_usuario,
            -- ... outros campos
        FROM user u
        LEFT JOIN cliente c ON u.user_id = c.user_id
        LEFT JOIN service_provider sp ON u.user_id = sp.user_id
        WHERE u.user_type IN ('cliente', 'prestador')
        ORDER BY u.created_at DESC";
```

#### 4.2. Formatação dos Dados

```php
$usuarios_formatados[] = [
    'user_id' => $usuario['user_id'],
    'nome' => $usuario['name'],
    'email' => $usuario['email'],
    'telefone' => $usuario['phone_number'] ?: 'Não informado',
    'instagram' => $usuario['instagram'] ?: 'Não informado',  // ← Campo formatado
    'tipo_usuario' => $usuario['tipo_usuario'],
    // ... outros campos
];
```

**Resposta JSON:**

```json
{
  "success": true,
  "usuarios": [
    {
      "user_id": 1,
      "nome": "João Silva",
      "email": "joao@example.com",
      "telefone": "(11) 99999-9999",
      "instagram": "@joaosilva",
      "tipo_usuario": "cliente",
      // ... outros campos
    }
  ],
  "estatisticas": { ... }
}
```

---

## 5. Dashboard do Administrador (Frontend)

### Arquivo: `client/administrador-dashboard.html`

#### 5.1. Cabeçalho da Tabela

```html
<table class="table table-hover" id="usersTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nome</th>
      <th>Email</th>
      <th>Telefone</th>
      <th>Instagram</th>
      <!-- ← Coluna Instagram -->
      <th>Tipo</th>
      <th>Status</th>
      <th>Data Cadastro</th>
      <th>Ações</th>
    </tr>
  </thead>
  <tbody></tbody>
</table>
```

---

## 6. JavaScript do Dashboard

### Arquivo: `assets/js/administrador-dashboard.js`

#### 6.1. Renderização da Tabela

```javascript
function renderUsersTable(users) {
  const tbody = document.querySelector("#usersTable tbody");
  tbody.innerHTML = users
    .map(
      (user) => `
    <tr>
      <td>${user.user_id}</td>
      <td>
        <div>
          ${user.nome || ""}
          ${
            user.verificado
              ? '<br><small class="text-success"><i class="fas fa-check-circle"></i> Verificado</small>'
              : ""
          }
        </div>
      </td>
      <td>${user.email || ""}</td>
      <td>${user.telefone || "Não informado"}</td>
      <td>${
        user.instagram || "Não informado"
      }</td>  <!-- ← Exibição do Instagram -->
      <td>
        <span class="badge ${
          user.tipo_usuario === "cliente" ? "bg-primary" : "bg-info"
        }">
          ${user.tipo_usuario === "cliente" ? "Cliente" : "Prestador"}
        </span>
      </td>
      <td>
        <span class="badge ${
          user.status === "ativo" ? "bg-success" : "bg-danger"
        }">
          ${user.status === "ativo" ? "Ativo" : "Inativo"}
        </span>
      </td>
      <td><small>${user.data_cadastro || ""}</small></td>
      <td>
        <!-- Botões de ação -->
      </td>
    </tr>
  `
    )
    .join("");
}
```

#### 6.2. Modal de Detalhes do Usuário

```javascript
const content = `
  <div class="row">
    <div class="col-md-6">
      <h6><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
      <p><strong>Nome:</strong> ${user.nome}</p>
      <p><strong>Email:</strong> ${user.email}</p>
      <p><strong>Telefone:</strong> ${user.telefone}</p>
      ${
        user.instagram
          ? `<p><strong>Instagram:</strong> ${user.instagram}</p>`
          : ""
      }  <!-- ← Exibição condicional -->
      <p><strong>Tipo:</strong> <span class="badge ...">...</span></p>
    </div>
    <!-- ... outras informações -->
  </div>
`;
```

**Características:**

- Exibe "Não informado" quando o campo está vazio ou `null`
- No modal de detalhes, só exibe se o valor existir (renderização condicional)

---

## 📝 Resumo da Implementação

### Arquivos Modificados/Criados:

1. **`lib/add-instagram-field.sql`** - Script de migração do banco de dados
2. **`client/registro/index.html`** - Campo no formulário de cadastro
3. **`php/cliente/cliente-signup.php`** - Processamento e salvamento do campo
4. **`php/admin/listar-usuarios.php`** - Query e formatação para listagem
5. **`client/administrador-dashboard.html`** - Coluna na tabela do dashboard
6. **`assets/js/administrador-dashboard.js`** - Renderização JavaScript

### Fluxo Completo:

1. **Cadastro:** Usuário preenche o campo Instagram no formulário
2. **Backend:** PHP valida e salva no banco de dados (tabela `user`)
3. **Listagem:** PHP busca e formata o campo para JSON
4. **Dashboard:** JavaScript renderiza o campo na tabela e no modal de detalhes

### Observações Importantes:

- ✅ Campo é **opcional** (não obrigatório)
- ✅ Suporta valores `NULL` no banco de dados
- ✅ Verificação de existência da coluna antes de inserir (compatibilidade)
- ✅ Exibe "Não informado" quando vazio
- ✅ Funciona apenas para usuários do tipo **Cliente**

---

## 🚀 Próximos Passos (Opcional)

- Adicionar validação de formato do Instagram (ex: deve começar com @)
- Adicionar link clicável para o perfil do Instagram
- Implementar campo Instagram também para Prestadores
- Adicionar campo Instagram no formulário de edição de perfil

---

**Última atualização:** Dezembro 2024

