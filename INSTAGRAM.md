# Adicionar campo Instagram no cadastro
_Exported on 11/10/2025 at 10:12:37 GMT-3 from Cursor (2.0.34)_

---

**User**

adicione o campo campo 'instagram' @index.html no cadastro cliente e esse faca co que esse campo apareca no administrador-dashboard@administrador-dashboard.html  como uma nova coluna. 


---

**Cursor**

Analisando os arquivos para entender a estrutura e adicionar o campo 'instagram' no cadastro e exibi-lo no dashboard.


Procurando o formulário de cadastro de cliente e o dashboard do administrador:


Lendo os arquivos necessários para entender a estrutura completa:


Buscando os arquivos que carregam os dados dos usuários no dashboard do administrador:


Lendo o arquivo PHP que lista os usuários:


Adicionando o campo 'instagram' no cadastro e exibindo-o no dashboard. Criando a lista de tarefas e iniciando:


Adicionando o campo 'instagram' no formulário de cadastro:


```html
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <title>ServiçoFácil - Cadastro</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap"
      rel="stylesheet"
    />
    <link href="../../assets/css/auth.css" rel="stylesheet" />
    <link href="../../assets/css/forms.css" rel="stylesheet" />
  </head>
  <body>
    <div
      class="container-fluid d-flex align-items-center justify-content-center min-vh-100"
    >
      <div class="row justify-content-center w-100">
        <div class="col-12 col-md-6 col-lg-4">
          <!-- Header -->
          <div class="text-center mb-4">
            <h1 class="logo-title">ServiçoFácil</h1>
            <p class="text-muted fw-semibold">Cadastre sua conta</p>
          </div>

          <!-- Form -->
          <form id="dynamicSignupForm" method="POST">
            <!-- Profile Selection -->
            <div class="mb-4">
              <label class="form-label fw-medium text-dark"
                >Selecione seu perfil</label
              >
              <div class="row g-3">
                <div class="col-6">
                  <div
                    class="profile-card"
                    id="cliente-card"
                    data-user-type="customer"
                  >
                    <div class="profile-icon">
                      <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="white"
                      >
                        <path
                          d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"
                        />
                      </svg>
                    </div>
                    <h6 class="fw-semibold mb-1">Cliente</h6>
                    <small class="text-muted">Publicação de demanda</small>
                  </div>
                </div>
                <div class="col-6">
                  <div
                    class="profile-card"
                    id="prestador-card"
                    data-user-type="service_provider"
                  >
                    <div class="profile-icon">
                      <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="white"
                      >
                        <path
                          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                        />
                      </svg>
                    </div>
                    <h6 class="fw-semibold mb-1">Prestador</h6>
                    <small class="text-muted"
                      >Prestadores veem lista de demandas</small
                    >
                  </div>
                </div>
              </div>
            </div>

            <!-- Common Fields -->
            <!-- Nome Field -->
            <div class="mb-3 form-field">
              <label for="name" class="form-label fw-medium"
                >Nome Completo</label
              >
              <input
                type="text"
                class="form-control"
                id="name"
                name="name"
                placeholder="Digite seu nome completo"
                required
              />
            </div>

            <!-- Email Field -->
            <div class="mb-3 form-field">
              <label for="email" class="form-label fw-medium">E-mail</label>
              <input
                type="email"
                class="form-control"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                required
              />
            </div>

            <!-- Cliente-specific Fields -->
            <div id="cliente-fields">
              <!-- Phone Field (Cliente only) -->
              <div class="mb-3 form-field">
                <label for="phone_number" class="form-label fw-medium"
                  >Telefone</label
                >
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
                <label for="instagram" class="form-label fw-medium"
                  >Instagram</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="instagram"
                  name="instagram"
                  placeholder="@seuinstagram"
                />
              </div>
            </div>

            <!-- Prestador-specific Fields -->
            <div id="prestador-fields" style="display: none">
              <!-- Specialty Field (Prestador only) -->
              <div class="mb-3 form-field">
                <label for="specialty" class="form-label fw-medium"
                  >Especialidade</label
                >
                <select class="form-control" id="specialty" name="specialty">
                  <option value="">Selecione sua especialidade</option>
                  <option value="Encanamento">Encanamento</option>
                  <option value="Elétrica">Elétrica</option>
                  <option value="Pintura">Pintura</option>
                  <option value="Limpeza">Limpeza</option>
                  <option value="Jardinagem">Jardinagem</option>
                  <option value="Marcenaria">Marcenaria</option>
                  <option value="Pedreiro">Pedreiro</option>
                  <option value="Mecânica">Mecânica</option>
                  <option value="Informática">Informática</option>
                  <option value="Outros">Outros</option>
                </select>
              </div>

              <!-- Location Field (Prestador only) -->
              <div class="mb-3 form-field">
                <label for="location" class="form-label fw-medium"
                  >Localização</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="location"
                  name="location"
                  placeholder="Ex: São Paulo, SP"
                />
              </div>
            </div>

            <!-- Common Password Fields -->
            <!-- Password Field -->
            <div class="mb-3 form-field">
              <label for="password" class="form-label fw-medium">Senha</label>
              <input
                type="password"
                class="form-control"
                id="password"
                name="password"
                placeholder="Digite sua senha"
                minlength="6"
                required
              />
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-4 form-field">
              <label for="confirm_password" class="form-label fw-medium"
                >Confirmar Senha</label
              >
              <input
                type="password"
                class="form-control"
                id="confirm_password"
                name="confirm_password"
                placeholder="Confirme sua senha"
                minlength="6"
                required
              />
            </div>

            <!-- Hidden field for user type -->
            <input
              type="hidden"
              id="user_type"
              name="user_type"
              value="customer"
            />

            <!-- Alert area (mensagem / message) -->
            <div id="alert-area" class="mb-3"></div>

            <!-- Submit Button -->
            <div class="d-grid mb-3">
              <button
                type="submit"
                class="btn btn-primary-custom py-2"
                id="submit-btn"
              >
                Cadastrar como Cliente
              </button>
            </div>

            <!-- Login Link -->
            <div class="text-center">
              <span class="text-muted">Já tem uma conta? </span>
              <a href="../login/index.html" class="text-link" id="signin-link"
                >Entrar</a
              >
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/dynamic-signup.js"></script>
  </body>
</html>


```

Atualizando o PHP de cadastro para salvar o campo Instagram e criando um script SQL para adicionar a coluna no banco:


```php
<?php
// 🛡️ CLIENTE SIGNUP - Clean Code & Normalized DB
include "../conexao.php";

// 📝 Função para validar dados de entrada
function validarDadosCliente($dados) {
    $erros = [];
    
    if (empty($dados['name'])) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email válido é obrigatório";
    }
    
    if (empty($dados['password']) || strlen($dados['password']) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres";
    }
    
    return $erros;
}

// 🔒 Função para criar usuário
function criarUsuario($pdo, $dados) {
    $sql = "INSERT INTO user (email, password, name, phone_number, user_type, identity_verified) VALUES (?, ?, ?, ?, 'cliente', FALSE)";
    $stmt = $pdo->prepare($sql);
    $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);
    
    if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number']])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// 👤 Função para criar cliente
function criarCliente($pdo, $user_id, $instagram = null) {
    $sql = "INSERT INTO cliente (user_id, instagram) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$user_id, $instagram])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// 📤 Função para enviar resposta
function enviarResposta($success, $message, $data = []) {
    header('Content-Type: application/json');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }
    
    echo json_encode($resposta);
    exit;
}

// 🚀 PROCESSO PRINCIPAL
try {
    // 📋 Coleta de dados
    $dados = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    
    // ✅ Validação
    $erros = validarDadosCliente($dados);
    if (!empty($erros)) {
        enviarResposta(false, implode(', ', $erros));
    }
    
    // 🔍 Verifica se email já existe
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->execute([$dados['email']]);
    if ($stmt->fetch()) {
        enviarResposta(false, "Email já está em uso");
    }
    
    // 💾 Criação do usuário
    $user_id = criarUsuario($pdo, $dados);
    if (!$user_id) {
        enviarResposta(false, "Erro ao criar usuário");
    }
    
    // 👤 Criação do cliente
    $cliente_id = criarCliente($pdo, $user_id);
    if (!$cliente_id) {
        enviarResposta(false, "Erro ao criar cliente");
    }
    
    // ✨ Sucesso
    enviarResposta(true, "Cliente cadastrado com sucesso", [
        'user_id' => $user_id,
        'cliente_id' => $cliente_id
    ]);
    
} catch (Exception $e) {
    enviarResposta(false, "Erro interno do servidor");
}
?>


```

```php
<?php
// 🛡️ CLIENTE SIGNUP - Clean Code & Normalized DB
include "../conexao.php";

// 📝 Função para validar dados de entrada
function validarDadosCliente($dados) {
    $erros = [];
    
    if (empty($dados['name'])) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($dados['email']) || !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Email válido é obrigatório";
    }
    
    if (empty($dados['password']) || strlen($dados['password']) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres";
    }
    
    return $erros;
}

// 🔒 Função para criar usuário
function criarUsuario($pdo, $dados) {
    $sql = "INSERT INTO user (email, password, name, phone_number, user_type, identity_verified) VALUES (?, ?, ?, ?, 'cliente', FALSE)";
    $stmt = $pdo->prepare($sql);
    $senha_hash = password_hash($dados['password'], PASSWORD_DEFAULT);
    
    if ($stmt->execute([$dados['email'], $senha_hash, $dados['name'], $dados['phone_number']])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// 👤 Função para criar cliente
function criarCliente($pdo, $user_id, $instagram = null) {
    $sql = "INSERT INTO cliente (user_id, instagram) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$user_id, $instagram])) {
        return $pdo->lastInsertId();
    }
    return false;
}

// 📤 Função para enviar resposta
function enviarResposta($success, $message, $data = []) {
    header('Content-Type: application/json');
    $resposta = [
        'success' => $success,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $resposta = array_merge($resposta, $data);
    }
    
    echo json_encode($resposta);
    exit;
}

// 🚀 PROCESSO PRINCIPAL
try {
    // 📋 Coleta de dados
    $dados = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone_number' => $_POST['phone_number'] ?? '',
        'password' => $_POST['password'] ?? '',
        'instagram' => $_POST['instagram'] ?? ''
    ];
    
    // ✅ Validação
    $erros = validarDadosCliente($dados);
    if (!empty($erros)) {
        enviarResposta(false, implode(', ', $erros));
    }
    
    // 🔍 Verifica se email já existe
    $stmt = $pdo->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->execute([$dados['email']]);
    if ($stmt->fetch()) {
        enviarResposta(false, "Email já está em uso");
    }
    
    // 💾 Criação do usuário
    $user_id = criarUsuario($pdo, $dados);
    if (!$user_id) {
        enviarResposta(false, "Erro ao criar usuário");
    }
    
    // 👤 Criação do cliente
    $cliente_id = criarCliente($pdo, $user_id, $dados['instagram']);
    if (!$cliente_id) {
        enviarResposta(false, "Erro ao criar cliente");
    }
    
    // ✨ Sucesso
    enviarResposta(true, "Cliente cadastrado com sucesso", [
        'user_id' => $user_id,
        'cliente_id' => $cliente_id
    ]);
    
} catch (Exception $e) {
    enviarResposta(false, "Erro interno do servidor");
}
?>


```

```sql
-- ============================================
-- MIGRAÇÃO: Adicionar campo 'instagram' à tabela cliente
-- ============================================
USE servicofacil;

-- Verificar se a coluna já existe antes de adicionar
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'servicofacil' 
    AND TABLE_NAME = 'cliente' 
    AND COLUMN_NAME = 'instagram'
);

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `cliente` ADD COLUMN `instagram` VARCHAR(100) DEFAULT NULL AFTER `user_id`',
    'SELECT ''Coluna instagram já existe na tabela cliente'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

```

Atualizando o PHP que lista os usuários para incluir o Instagram:


```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.']);
    exit;
}

require_once '../conexao.php';

try {
    // Query para buscar todos os usuários com informações detalhadas
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                CASE 
                    WHEN c.id IS NOT NULL THEN c.id
                    WHEN sp.service_provider_id IS NOT NULL THEN sp.service_provider_id
                    ELSE NULL
                END as tipo_id,
                c.instagram,
                sp.specialty,
                sp.location as prestador_location,
                -- Contagem de atividades
                (SELECT COUNT(*) FROM service_request sr WHERE sr.cliente_id = c.id) as total_solicitacoes,
                (SELECT COUNT(*) FROM proposal p WHERE p.service_provider_id = sp.service_provider_id) as total_propostas
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_type IN ('cliente', 'prestador')
            ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o frontend
    $usuarios_formatados = [];
    foreach ($usuarios as $usuario) {
        $usuarios_formatados[] = [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'tipo_id' => $usuario['tipo_id'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            // Dados específicos do prestador
            'especialidade' => $usuario['specialty'] ?: null,
            'localizacao' => $usuario['prestador_location'] ?: null,
            // Estatísticas
            'total_solicitacoes' => (int)$usuario['total_solicitacoes'],
            'total_propostas' => (int)$usuario['total_propostas'],
            // Dados raw para filtros
            'created_at_raw' => $usuario['created_at'],
            'updated_at_raw' => $usuario['updated_at']
        ];
    }
    
    // Calcular estatísticas gerais
    $stats = [
        'total_usuarios' => count($usuarios_formatados),
        'total_clientes' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'cliente')),
        'total_prestadores' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'prestador')),
        'usuarios_ativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'ativo')),
        'usuarios_inativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'inativo'))
    ];
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios_formatados,
        'estatisticas' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>


```

```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado. Apenas administradores podem acessar esta funcionalidade.']);
    exit;
}

require_once '../conexao.php';

try {
    // Query para buscar todos os usuários com informações detalhadas
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                CASE 
                    WHEN c.id IS NOT NULL THEN c.id
                    WHEN sp.service_provider_id IS NOT NULL THEN sp.service_provider_id
                    ELSE NULL
                END as tipo_id,
                c.instagram,
                sp.specialty,
                sp.location as prestador_location,
                -- Contagem de atividades
                (SELECT COUNT(*) FROM service_request sr WHERE sr.cliente_id = c.id) as total_solicitacoes,
                (SELECT COUNT(*) FROM proposal p WHERE p.service_provider_id = sp.service_provider_id) as total_propostas
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_type IN ('cliente', 'prestador')
            ORDER BY u.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o frontend
    $usuarios_formatados = [];
    foreach ($usuarios as $usuario) {
        $usuarios_formatados[] = [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'instagram' => $usuario['instagram'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'tipo_id' => $usuario['tipo_id'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            // Dados específicos do prestador
            'especialidade' => $usuario['specialty'] ?: null,
            'localizacao' => $usuario['prestador_location'] ?: null,
            // Estatísticas
            'total_solicitacoes' => (int)$usuario['total_solicitacoes'],
            'total_propostas' => (int)$usuario['total_propostas'],
            // Dados raw para filtros
            'created_at_raw' => $usuario['created_at'],
            'updated_at_raw' => $usuario['updated_at']
        ];
    }
    
    // Calcular estatísticas gerais
    $stats = [
        'total_usuarios' => count($usuarios_formatados),
        'total_clientes' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'cliente')),
        'total_prestadores' => count(array_filter($usuarios_formatados, fn($u) => $u['tipo_usuario'] === 'prestador')),
        'usuarios_ativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'ativo')),
        'usuarios_inativos' => count(array_filter($usuarios_formatados, fn($u) => $u['status'] === 'inativo'))
    ];
    
    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios_formatados,
        'estatisticas' => $stats
    ]);

} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>


```

Adicionando a coluna Instagram no cabeçalho da tabela do dashboard:


```html
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Administrador - ServiçoFácil</title>

    <!-- Script de verificação de autenticação ANTES de carregar qualquer coisa -->
    <script>
      (async function () {
        try {
          const response = await fetch("../php/admin/verificar-auth.php");
          const data = await response.json();

          if (!data.authenticated) {
            alert(
              data.usuario.message ||
                "Acesso negado. Você não tem permissões de administrador."
            );
            window.location.href = "login/administrador-signin.html";
            return;
          }
        } catch (error) {
          console.error("Erro na verificação de autenticação:", error);
          alert("Erro ao verificar autenticação. Redirecionando para login...");
          window.location.href = "login/administrador-signin.html";
        }
      })();
    </script>

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="../assets/css/administrador-dashboard.css" />
  </head>
  <body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <h4 class="mb-0">
          <i class="fas fa-shield-alt me-2"></i>
          Admin Panel
        </h4>
      </div>

      <div class="sidebar-user">
        <div class="user-avatar">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="user-info">
          <h6 class="mb-0" id="userName">Administrador</h6>
          <small class="text-muted" id="userEmail"
            >admin@servicofacil.com</small
          >
        </div>
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="nav-link active" data-section="overview">
          <i class="fas fa-chart-line"></i>
          <span>Visão Geral</span>
        </a>
        <a href="#" class="nav-link" data-section="services">
          <i class="fas fa-briefcase"></i>
          <span>Gerenciar Serviços</span>
        </a>
        <a href="#" class="nav-link" data-section="users">
          <i class="fas fa-users"></i>
          <span>Gerenciar Usuários</span>
        </a>
        <a href="#" class="nav-link" data-section="proposals">
          <i class="fas fa-file-invoice"></i>
          <span>Propostas</span>
        </a>
        <a href="#" class="nav-link" data-section="contracts">
          <i class="fas fa-file-contract"></i>
          <span>Contratos</span>
        </a>
        <a href="#" class="nav-link" data-section="reviews">
          <i class="fas fa-star"></i>
          <span>Avaliações</span>
        </a>
        <a href="#" class="nav-link" data-section="postagens">
          <i class="fas fa-newspaper"></i>
          <span>Gerenciar Postagens</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <a href="#" class="nav-link" id="logoutBtn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Sair</span>
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <button class="btn btn-link d-lg-none" id="sidebarToggle">
          <i class="fas fa-bars"></i>
        </button>
        <h5 class="mb-0" id="pageTitle">Visão Geral</h5>
        <div class="ms-auto d-flex align-items-center">
          <span class="text-muted me-3" id="currentDate"></span>
          <button class="btn btn-sm btn-primary" id="refreshBtn">
            <i class="fas fa-sync-alt me-1"></i>Atualizar
          </button>
        </div>
      </div>

      <!-- Content Area -->
      <div class="content-area">
        <!-- Overview Section -->
        <div id="overviewSection" class="content-section active">
          <div class="row g-4 mb-4">
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-icon bg-primary">
                  <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalUsers">0</h3>
                  <p>Total de Usuários</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-icon bg-success">
                  <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalServices">0</h3>
                  <p>Serviços Ativos</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-icon bg-warning">
                  <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalProposals">0</h3>
                  <p>Propostas Pendentes</p>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-card">
                <div class="stat-icon bg-info">
                  <i class="fas fa-file-contract"></i>
                </div>
                <div class="stat-info">
                  <h3 id="totalContracts">0</h3>
                  <p>Contratos Ativos</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Users Management Section -->
        <div id="usersSection" class="content-section">
          <div class="card">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <h5 class="mb-0">
                <i class="fas fa-users me-2"></i>
                Gerenciar Usuários
              </h5>
              <div class="d-flex gap-2">
                <select
                  class="form-select form-select-sm"
                  id="tipoFilter"
                  style="width: 150px"
                >
                  <option value="">Todos os Tipos</option>
                  <option value="cliente">Cliente</option>
                  <option value="prestador">Prestador</option>
                </select>
                <select
                  class="form-select form-select-sm"
                  id="statusFilterUsers"
                  style="width: 150px"
                >
                  <option value="">Todos os Status</option>
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
                <input
                  type="search"
                  class="form-control form-control-sm"
                  id="searchUsers"
                  placeholder="Buscar usuários..."
                  style="width: 250px"
                />
              </div>
            </div>
            <div class="card-body">
              <!-- Estatísticas de Usuários -->
              <div class="row g-3 mb-4">
                <div class="col-md-3">
                  <div class="card bg-primary text-white">
                    <div class="card-body">
                      <h6 class="card-title">Total de Usuários</h6>
                      <h3 id="statTotalUsuarios">0</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card bg-success text-white">
                    <div class="card-body">
                      <h6 class="card-title">Clientes</h6>
                      <h3 id="statTotalClientes">0</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card bg-info text-white">
                    <div class="card-body">
                      <h6 class="card-title">Prestadores</h6>
                      <h3 id="statTotalPrestadores">0</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card bg-warning text-white">
                    <div class="card-body">
                      <h6 class="card-title">Usuários Ativos</h6>
                      <h3 id="statUsuariosAtivos">0</h3>
                    </div>
                  </div>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Email</th>
                      <th>Telefone</th>
                      <th>Instagram</th>
                      <th>Tipo</th>
                      <th>Status</th>
                      <th>Data Cadastro</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Services Management Section -->
        <div id="servicesSection" class="content-section">
          <div class="card">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <h5 class="mb-0">
                <i class="fas fa-briefcase me-2"></i>
                Gerenciar Serviços de Usuário
              </h5>
              <div class="d-flex gap-2">
                <select
                  class="form-select form-select-sm"
                  id="statusFilter"
                  style="width: 150px"
                >
                  <option value="">Todos os Status</option>
                  <option value="pendente">Pendente</option>
                  <option value="em_andamento">Em Andamento</option>
                  <option value="concluido">Concluído</option>
                  <option value="cancelado">Cancelado</option>
                </select>
                <input
                  type="search"
                  class="form-control form-control-sm"
                  id="searchServices"
                  placeholder="Buscar serviços..."
                  style="width: 250px"
                />
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover" id="servicesTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Cliente</th>
                      <th>Título</th>
                      <th>Categoria</th>
                      <th>Localização</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Postagens Management Section -->
        <div id="postagensSection" class="content-section">
          <div class="card">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <h5 class="mb-0">
                <i class="fas fa-newspaper me-2"></i>
                Gerenciar Postagens de Prestadores
              </h5>
              <div class="d-flex gap-2">
                <select
                  class="form-select form-select-sm"
                  id="statusFilterPostagens"
                  style="width: 150px"
                >
                  <option value="">Todos os Status</option>
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
                <select
                  class="form-select form-select-sm"
                  id="disponibilidadeFilterPostagens"
                  style="width: 150px"
                >
                  <option value="">Todas</option>
                  <option value="disponivel">Disponível</option>
                  <option value="indisponivel">Indisponível</option>
                </select>
                <input
                  type="search"
                  class="form-control form-control-sm"
                  id="searchPostagens"
                  placeholder="Buscar postagens..."
                  style="width: 250px"
                />
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover" id="postagensTable">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Prestador</th>
                      <th>Título</th>
                      <th>Categoria</th>
                      <th>Preço</th>
                      <th>Status</th>
                      <th>Disponibilidade</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para detalhes do usuário -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-user me-2"></i>
              Detalhes do Usuário
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body" id="userDetailsContent">
            <!-- Conteúdo será carregado dinamicamente -->
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para detalhes do serviço -->
    <div class="modal fade" id="serviceDetailsModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-briefcase me-2"></i>
              Detalhes do Serviço
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body" id="serviceDetailsContent">
            <!-- Conteúdo será carregado dinamicamente -->
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Fechar
            </button>
            <button type="button" class="btn btn-primary" id="editServiceBtn">
              Editar Serviço
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para remover postagem -->
    <div class="modal fade" id="removePostagemModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-trash me-2"></i>
              Remover Postagem
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <p>
              Tem certeza que deseja remover esta postagem? Esta ação não pode
              ser desfeita.
            </p>
            <div class="mb-3">
              <label for="motivoRemocao" class="form-label"
                >Motivo da remoção:</label
              >
              <textarea
                class="form-control"
                id="motivoRemocao"
                rows="3"
                placeholder="Descreva o motivo da remoção (ex: conteúdo inapropriado)"
              >
Conteúdo inapropriado</textarea
              >
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="btn btn-danger"
              id="confirmRemovePostagemBtn"
            >
              Remover Postagem
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para editar serviço -->
    <div class="modal fade" id="editServiceModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-edit me-2"></i>
              Editar Serviço
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <form id="editServiceForm">
              <input type="hidden" id="editServiceId" />
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editTitulo" class="form-label">Título</label>
                    <input
                      type="text"
                      class="form-control"
                      id="editTitulo"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editCategoria" class="form-label"
                      >Categoria</label
                    >
                    <input
                      type="text"
                      class="form-control"
                      id="editCategoria"
                      required
                    />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="editDescricao" class="form-label">Descrição</label>
                <textarea
                  class="form-control"
                  id="editDescricao"
                  rows="3"
                  required
                ></textarea>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editEndereco" class="form-label"
                      >Endereço</label
                    >
                    <input
                      type="text"
                      class="form-control"
                      id="editEndereco"
                      required
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editCidade" class="form-label">Cidade</label>
                    <input
                      type="text"
                      class="form-control"
                      id="editCidade"
                      required
                    />
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editOrcamento" class="form-label"
                      >Orçamento Máximo</label
                    >
                    <input
                      type="number"
                      class="form-control"
                      id="editOrcamento"
                      step="0.01"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="editPrazo" class="form-label"
                      >Prazo Desejado</label
                    >
                    <input type="date" class="form-control" id="editPrazo" />
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label for="editStatus" class="form-label">Status</label>
                <select class="form-select" id="editStatus" required>
                  <option value="pendente">Pendente</option>
                  <option value="em_andamento">Em Andamento</option>
                  <option value="concluido">Concluído</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="editObservacoes" class="form-label"
                  >Observações</label
                >
                <textarea
                  class="form-control"
                  id="editObservacoes"
                  rows="2"
                ></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Cancelar
            </button>
            <button type="button" class="btn btn-primary" id="saveServiceBtn">
              Salvar Alterações
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dashboard JS -->
    <script src="../assets/js/administrador-dashboard.js"></script>
  </body>
</html>

```

Atualizando o JavaScript para exibir o Instagram na tabela:


```javascript
document.addEventListener('DOMContentLoaded', function () {
  // Elementos DOM
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const navLinks = document.querySelectorAll('.nav-link[data-section]');
  const contentSections = document.querySelectorAll('.content-section');
  const pageTitle = document.getElementById('pageTitle');
  const logoutBtn = document.getElementById('logoutBtn');
  const refreshBtn = document.getElementById('refreshBtn');
  const currentDate = document.getElementById('currentDate');

  // Verificar autenticação
  checkAuth();

  // Inicializar
  init();

  function init() {
    updateCurrentDate();
    loadDashboardData();
    setupEventListeners();
    showSection('overview'); // Mostrar seção de visão geral por padrão
  }

  function setupEventListeners() {
    // Toggle sidebar
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }

    // Navigation
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const section = link.dataset.section;
        showSection(section);

        // Update active nav
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Close sidebar on mobile
        if (window.innerWidth < 992) {
          sidebar.classList.remove('show');
        }
      });
    });

    // Logout
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      logout();
    });

    // Refresh
    refreshBtn.addEventListener('click', () => {
      loadDashboardData();
      const activeSection = document.querySelector('.content-section.active');
      if (activeSection) {
        if (activeSection.id === 'usersSection') {
          loadUsersData();
        } else if (activeSection.id === 'servicesSection') {
          loadServicesData();
        }
      }
    });

    // Search and filter for services
    const searchServices = document.getElementById('searchServices');
    const statusFilter = document.getElementById('statusFilter');

    if (searchServices) {
      searchServices.addEventListener('input', debounce(() => {
        loadServicesData();
      }, 300));
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', () => {
        loadServicesData();
      });
    }

    // Search and filter for users
    const searchUsers = document.getElementById('searchUsers');
    const tipoFilter = document.getElementById('tipoFilter');
    const statusFilterUsers = document.getElementById('statusFilterUsers');

    if (searchUsers) {
      searchUsers.addEventListener('input', debounce(() => {
        loadUsersData();
      }, 300));
    }

    if (tipoFilter) {
      tipoFilter.addEventListener('change', () => {
        loadUsersData();
      });
    }

    if (statusFilterUsers) {
      statusFilterUsers.addEventListener('change', () => {
        loadUsersData();
      });
    }

    // Search and filter for postagens
    const searchPostagens = document.getElementById('searchPostagens');
    const statusFilterPostagens = document.getElementById('statusFilterPostagens');
    const disponibilidadeFilterPostagens = document.getElementById('disponibilidadeFilterPostagens');

    if (searchPostagens) {
      searchPostagens.addEventListener('input', debounce(() => {
        loadPostagensData();
      }, 300));
    }

    if (statusFilterPostagens) {
      statusFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    if (disponibilidadeFilterPostagens) {
      disponibilidadeFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    // Confirm remove postagem button
    const confirmRemovePostagemBtn = document.getElementById('confirmRemovePostagemBtn');
    if (confirmRemovePostagemBtn) {
      confirmRemovePostagemBtn.addEventListener('click', () => {
        const serviceId = confirmRemovePostagemBtn.dataset.serviceId;
        const motivo = document.getElementById('motivoRemocao').value || 'Conteúdo inapropriado';
        removePostagem(serviceId, motivo);
      });
    }
  }

  async function checkAuth() {
    try {
      // Mostrar loading enquanto verifica autenticação
      const loadingDiv = document.createElement('div');
      loadingDiv.id = 'loadingAuth';
      loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
      loadingDiv.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
      loadingDiv.style.zIndex = '9999';
      loadingDiv.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Verificando acesso...</span>
          </div>
          <h5>Verificando permissões de administrador...</h5>
        </div>
      `;
      document.body.appendChild(loadingDiv);

      const response = await fetch('../php/admin/verificar-auth.php');
      const data = await response.json();

        // Remover loading
      document.getElementById('loadingAuth')?.remove();

      if (!data.authenticated) {
        // Mostrar alerta antes de redirecionar
        alert(data.usuario.message || 'Acesso negado. Você não tem permissões de administrador.');
        window.location.href = 'login/administrador-signin.html';
        return;
      }

      // Update user info
      document.getElementById('userName').textContent = data.usuario.nome;
      document.getElementById('userEmail').textContent = data.usuario.email;

      // Log de sucesso
      console.log('✅ Acesso autorizado para administrador:', data.usuario.nome);

    } catch (error) {
      console.error('❌ Erro na verificação de autenticação:', error);

      // Remover loading se houver erro
      document.getElementById('loadingAuth')?.remove();

      alert('Erro ao verificar autenticação. Redirecionando para login...');
      window.location.href = 'login/administrador-signin.html';
    }
  }

  async function loadDashboardData() {
    try {
      const response = await fetch('../php/admin/administrador-dashboard.php?action=stats');
      const data = await response.json();

      if (data.success) {
        const stats = data.data;
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('totalServices').textContent = stats.total_services || 0;
        document.getElementById('totalProposals').textContent = stats.total_proposals || 0;
        document.getElementById('totalContracts').textContent = stats.total_contracts || 0;
      }
    } catch (error) {
      console.error('Erro ao carregar estatísticas:', error);
    }
  }

  // ========== USER MANAGEMENT FUNCTIONS ==========
  let allUsers = [];

  async function loadUsersData() {
    try {
      const response = await fetch('../php/admin/listar-usuarios.php');
      const data = await response.json();

      if (data.success) {
        allUsers = data.usuarios;
        updateUsersStatistics(data.estatisticas);
        filterAndRenderUsers();
      } else {
        console.error('Erro ao carregar usuários:', data.error);
        alert('Erro ao carregar usuários: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
      alert('Erro de conexão ao carregar usuários');
    }
  }

  function updateUsersStatistics(stats) {
    document.getElementById('statTotalUsuarios').textContent = stats.total_usuarios || 0;
    document.getElementById('statTotalClientes').textContent = stats.total_clientes || 0;
    document.getElementById('statTotalPrestadores').textContent = stats.total_prestadores || 0;
    document.getElementById('statUsuariosAtivos').textContent = stats.usuarios_ativos || 0;
  }

  function filterAndRenderUsers() {
    const searchTerm = document.getElementById('searchUsers')?.value.toLowerCase() || '';
    const tipoFilter = document.getElementById('tipoFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilterUsers')?.value || '';

    let filteredUsers = allUsers.filter(user => {
      const matchesSearch = !searchTerm || 
        user.nome.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.telefone.toLowerCase().includes(searchTerm);
      
      const matchesTipo = !tipoFilter || user.tipo_usuario === tipoFilter;
      const matchesStatus = !statusFilter || user.status === statusFilter;

      return matchesSearch && matchesTipo && matchesStatus;
    });

    renderUsersTable(filteredUsers);
  }

  function renderUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhum usuário encontrado</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = users.map(user => `
      <tr>
        <td>#${user.user_id}</td>
        <td>
          <div>
            <strong>${user.nome}</strong>
            ${user.verificado ? '<br><small class="text-success"><i class="fas fa-check-circle"></i> Verificado</small>' : ''}
          </div>
        </td>
        <td>${user.email}</td>
        <td>${user.telefone}</td>
        <td>
          <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">
            ${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}
          </span>
        </td>
        <td>
          <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">
            ${user.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <small>${user.data_cadastro}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewUserDetails(${user.user_id})" title="Ver detalhes">
              <i class="fas fa-eye"></i>
            </button>
            ${user.status === 'ativo' 
              ? `<button class="btn btn-outline-warning" onclick="manageUser(${user.user_id}, 'desativar')" title="Desativar">
                   <i class="fas fa-ban"></i>
                 </button>`
              : `<button class="btn btn-outline-success" onclick="manageUser(${user.user_id}, 'ativar')" title="Ativar">
                   <i class="fas fa-check"></i>
                 </button>`
            }
            <button class="btn btn-outline-danger" onclick="manageUser(${user.user_id}, 'excluir')" title="Excluir">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Global functions for user management
  window.viewUserDetails = async function (userId) {
    try {
      const response = await fetch(`../php/admin/detalhes-usuario.php?user_id=${userId}`);
      const data = await response.json();

      if (data.success) {
        const user = data.usuario;
        const stats = data.estatisticas || {};
        
        let statsHtml = '';
        if (user.tipo_usuario === 'cliente') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Cliente</h6>
                <p><strong>Total de Solicitações:</strong> ${stats.total_solicitacoes || 0}</p>
                <p><strong>Solicitações Pendentes:</strong> ${stats.solicitacoes_pendentes || 0}</p>
                <p><strong>Solicitações Concluídas:</strong> ${stats.solicitacoes_concluidas || 0}</p>
                <p><strong>Propostas Recebidas:</strong> ${stats.propostas_recebidas || 0}</p>
              </div>
            </div>
          `;
        } else if (user.tipo_usuario === 'prestador') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Prestador</h6>
                <p><strong>Total de Propostas:</strong> ${stats.total_propostas || 0}</p>
                <p><strong>Propostas Pendentes:</strong> ${stats.propostas_pendentes || 0}</p>
                <p><strong>Propostas Aceitas:</strong> ${stats.propostas_aceitas || 0}</p>
                <p><strong>Valor Médio:</strong> R$ ${parseFloat(stats.valor_medio_propostas || 0).toFixed(2)}</p>
              </div>
            </div>
          `;
        }

        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
              <p><strong>Nome:</strong> ${user.nome}</p>
              <p><strong>Email:</strong> ${user.email}</p>
              <p><strong>Telefone:</strong> ${user.telefone}</p>
              <p><strong>Tipo:</strong> <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}</span></p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-info-circle me-2"></i>Informações da Conta</h6>
              <p><strong>Status:</strong> <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">${user.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></p>
              <p><strong>Verificado:</strong> ${user.verificado ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-warning">Não</span>'}</p>
              <p><strong>Data de Cadastro:</strong> ${user.data_cadastro}</p>
              <p><strong>Última Atualização:</strong> ${user.ultima_atualizacao}</p>
            </div>
          </div>
          ${user.especialidade ? `<p><strong>Especialidade:</strong> ${user.especialidade}</p>` : ''}
          ${user.localizacao ? `<p><strong>Localização:</strong> ${user.localizacao}</p>` : ''}
          <hr>
          ${statsHtml}
        `;

        document.getElementById('userDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do usuário: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do usuário');
    }
  };

  window.manageUser = async function (userId, acao) {
    const acoes = {
      'ativar': 'ativar',
      'desativar': 'desativar',
      'excluir': 'excluir permanentemente'
    };

    const confirmMessage = acao === 'excluir' 
      ? 'Tem certeza que deseja EXCLUIR PERMANENTEMENTE este usuário? Esta ação não pode ser desfeita!'
      : `Tem certeza que deseja ${acoes[acao]} este usuário?`;

    if (!confirm(confirmMessage)) {
      return;
    }

    try {
      const formData = new FormData();
      formData.append('user_id', userId);
      formData.append('acao', acao);

      const response = await fetch('../php/admin/gerenciar-usuario.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        loadUsersData(); // Recarregar lista de usuários
      } else {
        alert('Erro: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao processar ação');
    }
  };

  // ========== SERVICES MANAGEMENT FUNCTIONS ==========
  async function loadServicesData() {
    try {
      const search = document.getElementById('searchServices')?.value || '';
      const status = document.getElementById('statusFilter')?.value || '';

      const params = new URLSearchParams({
        action: 'services',
        search: search,
        status: status
      });

      const response = await fetch(`../php/admin/administrador-dashboard.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderServicesTable(data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
    }
  }

  function renderServicesTable(services) {
    const tbody = document.querySelector('#servicesTable tbody');
    if (!tbody) {
      // Create tbody if it doesn't exist
      const table = document.getElementById('servicesTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderServicesTable(services);
    }

    if (services.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Nenhum serviço encontrado</p>
                    </td>
                </tr>
            `;
      return;
    }

    tbody.innerHTML = services.map(service => `
            <tr>
                <td>#${service.request_id}</td>
                <td>
                    <div>
                        <strong>${service.cliente_nome}</strong>
                        <br>
                        <small class="text-muted">${service.cliente_email}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${service.titulo}</strong>
                        <br>
                        <small class="text-muted">${truncateText(service.descricao, 50)}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-secondary">${service.categoria}</span>
                </td>
                <td>
                    <small>${service.cidade}</small>
                </td>
                <td>
                    <span class="badge ${getStatusClass(service.status)}">${service.status}</span>
                </td>
                <td>
                    <small>${formatDate(service.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewServiceDetails(${service.request_id})" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
  }

  // Global functions for service management
  window.viewServiceDetails = async function (serviceId) {
    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?action=details&id=${serviceId}`);
      const data = await response.json();

      if (data.success) {
        const service = data.data;
        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações do Cliente</h6>
              <p><strong>Nome:</strong> ${service.cliente_nome}</p>
              <p><strong>Email:</strong> ${service.cliente_email}</p>
              <p><strong>Telefone:</strong> ${service.cliente_telefone || 'Não informado'}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-briefcase me-2"></i>Informações do Serviço</h6>
              <p><strong>Status:</strong> <span class="badge ${getStatusClass(service.status)}">${service.status}</span></p>
              <p><strong>Categoria:</strong> ${service.categoria}</p>
              <p><strong>Data de Criação:</strong> ${formatDate(service.created_at)}</p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-info-circle me-2"></i>Detalhes do Serviço</h6>
              <p><strong>Título:</strong> ${service.titulo}</p>
              <p><strong>Descrição:</strong></p>
              <p class="bg-light p-3 rounded">${service.descricao}</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-map-marker-alt me-2"></i>Localização</h6>
              <p><strong>Endereço:</strong> ${service.endereco}</p>
              <p><strong>Cidade:</strong> ${service.cidade}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-dollar-sign me-2"></i>Orçamento e Prazo</h6>
              <p><strong>Orçamento Máximo:</strong> R$ ${parseFloat(service.orcamento_maximo || 0).toFixed(2)}</p>
              <p><strong>Prazo Desejado:</strong> ${service.prazo_desejado ? formatDate(service.prazo_desejado) : 'Não definido'}</p>
            </div>
          </div>
          ${service.observacoes ? `
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-sticky-note me-2"></i>Observações</h6>
              <p class="bg-light p-3 rounded">${service.observacoes}</p>
            </div>
          </div>
          ` : ''}
        `;

        document.getElementById('serviceDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do serviço');
    }
  };

  function showSection(sectionName) {
    // Hide all sections
    contentSections.forEach(section => {
      section.classList.remove('active');
    });

    // Show target section
    const targetSection = document.getElementById(sectionName + 'Section');
    if (targetSection) {
      targetSection.classList.add('active');
    }

    // Update page title
    const titles = {
      overview: 'Visão Geral',
      services: 'Gerenciar Serviços',
      users: 'Gerenciar Usuários',
      proposals: 'Propostas',
      contracts: 'Contratos',
      reviews: 'Avaliações',
      postagens: 'Gerenciar Postagens'
    };

    pageTitle.textContent = titles[sectionName] || 'Dashboard';

    // Load section specific data
    if (sectionName === 'services') {
      loadServicesData();
    } else if (sectionName === 'users') {
      loadUsersData();
    } else if (sectionName === 'postagens') {
      loadPostagensData();
    }
  }

  function getStatusClass(status) {
    const classes = {
      'Pendente': 'bg-warning',
      'Em Andamento': 'bg-info',
      'Concluído': 'bg-success',
      'Cancelado': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
  }

  function truncateText(text, length) {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  }

  function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR');
  }

  function updateCurrentDate() {
    const now = new Date();
    currentDate.textContent = now.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // ========== POSTAGENS MANAGEMENT FUNCTIONS ==========
  async function loadPostagensData() {
    try {
      const search = document.getElementById('searchPostagens')?.value || '';
      const status = document.getElementById('statusFilterPostagens')?.value || '';
      const disponibilidade = document.getElementById('disponibilidadeFilterPostagens')?.value || '';

      const params = new URLSearchParams({
        search: search,
        status: status,
        disponibilidade: disponibilidade
      });

      const response = await fetch(`../php/admin/listar-postagens.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderPostagensTable(data.postagens);
      } else {
        console.error('Erro ao carregar postagens:', data.message);
      }
    } catch (error) {
      console.error('Erro ao carregar postagens:', error);
    }
  }

  function renderPostagensTable(postagens) {
    const tbody = document.querySelector('#postagensTable tbody');
    if (!tbody) {
      const table = document.getElementById('postagensTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderPostagensTable(postagens);
    }

    if (postagens.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhuma postagem encontrada</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = postagens.map(postagem => `
      <tr>
        <td>#${postagem.service_id}</td>
        <td>
          <div>
            <strong>${postagem.prestador_nome}</strong>
            <br>
            <small class="text-muted">${postagem.prestador_email}</small>
          </div>
        </td>
        <td>
          <div>
            <strong>${postagem.titulo}</strong>
            <br>
            <small class="text-muted">${truncateText(postagem.descricao, 50)}</small>
          </div>
        </td>
        <td>
          <span class="badge bg-secondary">${postagem.categoria}</span>
        </td>
        <td>
          <strong>R$ ${postagem.preco}</strong>
        </td>
        <td>
          <span class="badge ${postagem.status === 'ativo' ? 'bg-success' : 'bg-secondary'}">
            ${postagem.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <span class="badge ${postagem.disponibilidade === 'disponivel' ? 'bg-info' : 'bg-warning'}">
            ${postagem.disponibilidade === 'disponivel' ? 'Disponível' : 'Indisponível'}
          </span>
        </td>
        <td>
          <small>${postagem.created_at}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-danger" onclick="showRemovePostagemModal(${postagem.service_id}, '${escapeHtml(postagem.titulo)}')" title="Remover">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  window.showRemovePostagemModal = function (serviceId, titulo) {
    const modal = document.getElementById('removePostagemModal');
    const confirmBtn = document.getElementById('confirmRemovePostagemBtn');
    confirmBtn.dataset.serviceId = serviceId;
    
    // Atualizar texto do modal se necessário
    const modalBody = modal.querySelector('.modal-body p');
    if (modalBody) {
      modalBody.textContent = `Tem certeza que deseja remover a postagem "${titulo}"? Esta ação não pode ser desfeita.`;
    }
    
    new bootstrap.Modal(modal).show();
  };

  async function removePostagem(serviceId, motivo) {
    try {
      const formData = new FormData();
      formData.append('service_id', serviceId);
      formData.append('motivo', motivo);

      const response = await fetch('../php/admin/remover-postagem.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Postagem removida com sucesso! A ação foi registrada no histórico administrativo.');
        bootstrap.Modal.getInstance(document.getElementById('removePostagemModal')).hide();
        loadPostagensData();
      } else {
        alert('Erro ao remover postagem: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao remover postagem');
    }
  }

  async function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
      try {
        // Clear session
        await fetch('../php/logout.php', { method: 'POST' });
        window.location.href = 'login/administrador-signin.html';
      } catch (error) {
        // Force redirect even if logout fails
        window.location.href = 'login/administrador-signin.html';
      }
    }
  }
});


```

```javascript
document.addEventListener('DOMContentLoaded', function () {
  // Elementos DOM
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const navLinks = document.querySelectorAll('.nav-link[data-section]');
  const contentSections = document.querySelectorAll('.content-section');
  const pageTitle = document.getElementById('pageTitle');
  const logoutBtn = document.getElementById('logoutBtn');
  const refreshBtn = document.getElementById('refreshBtn');
  const currentDate = document.getElementById('currentDate');

  // Verificar autenticação
  checkAuth();

  // Inicializar
  init();

  function init() {
    updateCurrentDate();
    loadDashboardData();
    setupEventListeners();
    showSection('overview'); // Mostrar seção de visão geral por padrão
  }

  function setupEventListeners() {
    // Toggle sidebar
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }

    // Navigation
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const section = link.dataset.section;
        showSection(section);

        // Update active nav
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Close sidebar on mobile
        if (window.innerWidth < 992) {
          sidebar.classList.remove('show');
        }
      });
    });

    // Logout
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      logout();
    });

    // Refresh
    refreshBtn.addEventListener('click', () => {
      loadDashboardData();
      const activeSection = document.querySelector('.content-section.active');
      if (activeSection) {
        if (activeSection.id === 'usersSection') {
          loadUsersData();
        } else if (activeSection.id === 'servicesSection') {
          loadServicesData();
        }
      }
    });

    // Search and filter for services
    const searchServices = document.getElementById('searchServices');
    const statusFilter = document.getElementById('statusFilter');

    if (searchServices) {
      searchServices.addEventListener('input', debounce(() => {
        loadServicesData();
      }, 300));
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', () => {
        loadServicesData();
      });
    }

    // Search and filter for users
    const searchUsers = document.getElementById('searchUsers');
    const tipoFilter = document.getElementById('tipoFilter');
    const statusFilterUsers = document.getElementById('statusFilterUsers');

    if (searchUsers) {
      searchUsers.addEventListener('input', debounce(() => {
        loadUsersData();
      }, 300));
    }

    if (tipoFilter) {
      tipoFilter.addEventListener('change', () => {
        loadUsersData();
      });
    }

    if (statusFilterUsers) {
      statusFilterUsers.addEventListener('change', () => {
        loadUsersData();
      });
    }

    // Search and filter for postagens
    const searchPostagens = document.getElementById('searchPostagens');
    const statusFilterPostagens = document.getElementById('statusFilterPostagens');
    const disponibilidadeFilterPostagens = document.getElementById('disponibilidadeFilterPostagens');

    if (searchPostagens) {
      searchPostagens.addEventListener('input', debounce(() => {
        loadPostagensData();
      }, 300));
    }

    if (statusFilterPostagens) {
      statusFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    if (disponibilidadeFilterPostagens) {
      disponibilidadeFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    // Confirm remove postagem button
    const confirmRemovePostagemBtn = document.getElementById('confirmRemovePostagemBtn');
    if (confirmRemovePostagemBtn) {
      confirmRemovePostagemBtn.addEventListener('click', () => {
        const serviceId = confirmRemovePostagemBtn.dataset.serviceId;
        const motivo = document.getElementById('motivoRemocao').value || 'Conteúdo inapropriado';
        removePostagem(serviceId, motivo);
      });
    }
  }

  async function checkAuth() {
    try {
      // Mostrar loading enquanto verifica autenticação
      const loadingDiv = document.createElement('div');
      loadingDiv.id = 'loadingAuth';
      loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
      loadingDiv.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
      loadingDiv.style.zIndex = '9999';
      loadingDiv.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Verificando acesso...</span>
          </div>
          <h5>Verificando permissões de administrador...</h5>
        </div>
      `;
      document.body.appendChild(loadingDiv);

      const response = await fetch('../php/admin/verificar-auth.php');
      const data = await response.json();

        // Remover loading
      document.getElementById('loadingAuth')?.remove();

      if (!data.authenticated) {
        // Mostrar alerta antes de redirecionar
        alert(data.usuario.message || 'Acesso negado. Você não tem permissões de administrador.');
        window.location.href = 'login/administrador-signin.html';
        return;
      }

      // Update user info
      document.getElementById('userName').textContent = data.usuario.nome;
      document.getElementById('userEmail').textContent = data.usuario.email;

      // Log de sucesso
      console.log('✅ Acesso autorizado para administrador:', data.usuario.nome);

    } catch (error) {
      console.error('❌ Erro na verificação de autenticação:', error);

      // Remover loading se houver erro
      document.getElementById('loadingAuth')?.remove();

      alert('Erro ao verificar autenticação. Redirecionando para login...');
      window.location.href = 'login/administrador-signin.html';
    }
  }

  async function loadDashboardData() {
    try {
      const response = await fetch('../php/admin/administrador-dashboard.php?action=stats');
      const data = await response.json();

      if (data.success) {
        const stats = data.data;
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('totalServices').textContent = stats.total_services || 0;
        document.getElementById('totalProposals').textContent = stats.total_proposals || 0;
        document.getElementById('totalContracts').textContent = stats.total_contracts || 0;
      }
    } catch (error) {
      console.error('Erro ao carregar estatísticas:', error);
    }
  }

  // ========== USER MANAGEMENT FUNCTIONS ==========
  let allUsers = [];

  async function loadUsersData() {
    try {
      const response = await fetch('../php/admin/listar-usuarios.php');
      const data = await response.json();

      if (data.success) {
        allUsers = data.usuarios;
        updateUsersStatistics(data.estatisticas);
        filterAndRenderUsers();
      } else {
        console.error('Erro ao carregar usuários:', data.error);
        alert('Erro ao carregar usuários: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
      alert('Erro de conexão ao carregar usuários');
    }
  }

  function updateUsersStatistics(stats) {
    document.getElementById('statTotalUsuarios').textContent = stats.total_usuarios || 0;
    document.getElementById('statTotalClientes').textContent = stats.total_clientes || 0;
    document.getElementById('statTotalPrestadores').textContent = stats.total_prestadores || 0;
    document.getElementById('statUsuariosAtivos').textContent = stats.usuarios_ativos || 0;
  }

  function filterAndRenderUsers() {
    const searchTerm = document.getElementById('searchUsers')?.value.toLowerCase() || '';
    const tipoFilter = document.getElementById('tipoFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilterUsers')?.value || '';

    let filteredUsers = allUsers.filter(user => {
      const matchesSearch = !searchTerm || 
        user.nome.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.telefone.toLowerCase().includes(searchTerm);
      
      const matchesTipo = !tipoFilter || user.tipo_usuario === tipoFilter;
      const matchesStatus = !statusFilter || user.status === statusFilter;

      return matchesSearch && matchesTipo && matchesStatus;
    });

    renderUsersTable(filteredUsers);
  }

  function renderUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhum usuário encontrado</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = users.map(user => `
      <tr>
        <td>#${user.user_id}</td>
        <td>
          <div>
            <strong>${user.nome}</strong>
            ${user.verificado ? '<br><small class="text-success"><i class="fas fa-check-circle"></i> Verificado</small>' : ''}
          </div>
        </td>
        <td>${user.email}</td>
        <td>${user.telefone}</td>
        <td>${user.instagram || 'Não informado'}</td>
        <td>
          <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">
            ${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}
          </span>
        </td>
        <td>
          <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">
            ${user.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <small>${user.data_cadastro}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewUserDetails(${user.user_id})" title="Ver detalhes">
              <i class="fas fa-eye"></i>
            </button>
            ${user.status === 'ativo' 
              ? `<button class="btn btn-outline-warning" onclick="manageUser(${user.user_id}, 'desativar')" title="Desativar">
                   <i class="fas fa-ban"></i>
                 </button>`
              : `<button class="btn btn-outline-success" onclick="manageUser(${user.user_id}, 'ativar')" title="Ativar">
                   <i class="fas fa-check"></i>
                 </button>`
            }
            <button class="btn btn-outline-danger" onclick="manageUser(${user.user_id}, 'excluir')" title="Excluir">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Global functions for user management
  window.viewUserDetails = async function (userId) {
    try {
      const response = await fetch(`../php/admin/detalhes-usuario.php?user_id=${userId}`);
      const data = await response.json();

      if (data.success) {
        const user = data.usuario;
        const stats = data.estatisticas || {};
        
        let statsHtml = '';
        if (user.tipo_usuario === 'cliente') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Cliente</h6>
                <p><strong>Total de Solicitações:</strong> ${stats.total_solicitacoes || 0}</p>
                <p><strong>Solicitações Pendentes:</strong> ${stats.solicitacoes_pendentes || 0}</p>
                <p><strong>Solicitações Concluídas:</strong> ${stats.solicitacoes_concluidas || 0}</p>
                <p><strong>Propostas Recebidas:</strong> ${stats.propostas_recebidas || 0}</p>
              </div>
            </div>
          `;
        } else if (user.tipo_usuario === 'prestador') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Prestador</h6>
                <p><strong>Total de Propostas:</strong> ${stats.total_propostas || 0}</p>
                <p><strong>Propostas Pendentes:</strong> ${stats.propostas_pendentes || 0}</p>
                <p><strong>Propostas Aceitas:</strong> ${stats.propostas_aceitas || 0}</p>
                <p><strong>Valor Médio:</strong> R$ ${parseFloat(stats.valor_medio_propostas || 0).toFixed(2)}</p>
              </div>
            </div>
          `;
        }

        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
              <p><strong>Nome:</strong> ${user.nome}</p>
              <p><strong>Email:</strong> ${user.email}</p>
              <p><strong>Telefone:</strong> ${user.telefone}</p>
              <p><strong>Tipo:</strong> <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}</span></p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-info-circle me-2"></i>Informações da Conta</h6>
              <p><strong>Status:</strong> <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">${user.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></p>
              <p><strong>Verificado:</strong> ${user.verificado ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-warning">Não</span>'}</p>
              <p><strong>Data de Cadastro:</strong> ${user.data_cadastro}</p>
              <p><strong>Última Atualização:</strong> ${user.ultima_atualizacao}</p>
            </div>
          </div>
          ${user.especialidade ? `<p><strong>Especialidade:</strong> ${user.especialidade}</p>` : ''}
          ${user.localizacao ? `<p><strong>Localização:</strong> ${user.localizacao}</p>` : ''}
          <hr>
          ${statsHtml}
        `;

        document.getElementById('userDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do usuário: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do usuário');
    }
  };

  window.manageUser = async function (userId, acao) {
    const acoes = {
      'ativar': 'ativar',
      'desativar': 'desativar',
      'excluir': 'excluir permanentemente'
    };

    const confirmMessage = acao === 'excluir' 
      ? 'Tem certeza que deseja EXCLUIR PERMANENTEMENTE este usuário? Esta ação não pode ser desfeita!'
      : `Tem certeza que deseja ${acoes[acao]} este usuário?`;

    if (!confirm(confirmMessage)) {
      return;
    }

    try {
      const formData = new FormData();
      formData.append('user_id', userId);
      formData.append('acao', acao);

      const response = await fetch('../php/admin/gerenciar-usuario.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        loadUsersData(); // Recarregar lista de usuários
      } else {
        alert('Erro: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao processar ação');
    }
  };

  // ========== SERVICES MANAGEMENT FUNCTIONS ==========
  async function loadServicesData() {
    try {
      const search = document.getElementById('searchServices')?.value || '';
      const status = document.getElementById('statusFilter')?.value || '';

      const params = new URLSearchParams({
        action: 'services',
        search: search,
        status: status
      });

      const response = await fetch(`../php/admin/administrador-dashboard.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderServicesTable(data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
    }
  }

  function renderServicesTable(services) {
    const tbody = document.querySelector('#servicesTable tbody');
    if (!tbody) {
      // Create tbody if it doesn't exist
      const table = document.getElementById('servicesTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderServicesTable(services);
    }

    if (services.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Nenhum serviço encontrado</p>
                    </td>
                </tr>
            `;
      return;
    }

    tbody.innerHTML = services.map(service => `
            <tr>
                <td>#${service.request_id}</td>
                <td>
                    <div>
                        <strong>${service.cliente_nome}</strong>
                        <br>
                        <small class="text-muted">${service.cliente_email}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${service.titulo}</strong>
                        <br>
                        <small class="text-muted">${truncateText(service.descricao, 50)}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-secondary">${service.categoria}</span>
                </td>
                <td>
                    <small>${service.cidade}</small>
                </td>
                <td>
                    <span class="badge ${getStatusClass(service.status)}">${service.status}</span>
                </td>
                <td>
                    <small>${formatDate(service.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewServiceDetails(${service.request_id})" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
  }

  // Global functions for service management
  window.viewServiceDetails = async function (serviceId) {
    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?action=details&id=${serviceId}`);
      const data = await response.json();

      if (data.success) {
        const service = data.data;
        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações do Cliente</h6>
              <p><strong>Nome:</strong> ${service.cliente_nome}</p>
              <p><strong>Email:</strong> ${service.cliente_email}</p>
              <p><strong>Telefone:</strong> ${service.cliente_telefone || 'Não informado'}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-briefcase me-2"></i>Informações do Serviço</h6>
              <p><strong>Status:</strong> <span class="badge ${getStatusClass(service.status)}">${service.status}</span></p>
              <p><strong>Categoria:</strong> ${service.categoria}</p>
              <p><strong>Data de Criação:</strong> ${formatDate(service.created_at)}</p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-info-circle me-2"></i>Detalhes do Serviço</h6>
              <p><strong>Título:</strong> ${service.titulo}</p>
              <p><strong>Descrição:</strong></p>
              <p class="bg-light p-3 rounded">${service.descricao}</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-map-marker-alt me-2"></i>Localização</h6>
              <p><strong>Endereço:</strong> ${service.endereco}</p>
              <p><strong>Cidade:</strong> ${service.cidade}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-dollar-sign me-2"></i>Orçamento e Prazo</h6>
              <p><strong>Orçamento Máximo:</strong> R$ ${parseFloat(service.orcamento_maximo || 0).toFixed(2)}</p>
              <p><strong>Prazo Desejado:</strong> ${service.prazo_desejado ? formatDate(service.prazo_desejado) : 'Não definido'}</p>
            </div>
          </div>
          ${service.observacoes ? `
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-sticky-note me-2"></i>Observações</h6>
              <p class="bg-light p-3 rounded">${service.observacoes}</p>
            </div>
          </div>
          ` : ''}
        `;

        document.getElementById('serviceDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do serviço');
    }
  };

  function showSection(sectionName) {
    // Hide all sections
    contentSections.forEach(section => {
      section.classList.remove('active');
    });

    // Show target section
    const targetSection = document.getElementById(sectionName + 'Section');
    if (targetSection) {
      targetSection.classList.add('active');
    }

    // Update page title
    const titles = {
      overview: 'Visão Geral',
      services: 'Gerenciar Serviços',
      users: 'Gerenciar Usuários',
      proposals: 'Propostas',
      contracts: 'Contratos',
      reviews: 'Avaliações',
      postagens: 'Gerenciar Postagens'
    };

    pageTitle.textContent = titles[sectionName] || 'Dashboard';

    // Load section specific data
    if (sectionName === 'services') {
      loadServicesData();
    } else if (sectionName === 'users') {
      loadUsersData();
    } else if (sectionName === 'postagens') {
      loadPostagensData();
    }
  }

  function getStatusClass(status) {
    const classes = {
      'Pendente': 'bg-warning',
      'Em Andamento': 'bg-info',
      'Concluído': 'bg-success',
      'Cancelado': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
  }

  function truncateText(text, length) {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  }

  function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR');
  }

  function updateCurrentDate() {
    const now = new Date();
    currentDate.textContent = now.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // ========== POSTAGENS MANAGEMENT FUNCTIONS ==========
  async function loadPostagensData() {
    try {
      const search = document.getElementById('searchPostagens')?.value || '';
      const status = document.getElementById('statusFilterPostagens')?.value || '';
      const disponibilidade = document.getElementById('disponibilidadeFilterPostagens')?.value || '';

      const params = new URLSearchParams({
        search: search,
        status: status,
        disponibilidade: disponibilidade
      });

      const response = await fetch(`../php/admin/listar-postagens.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderPostagensTable(data.postagens);
      } else {
        console.error('Erro ao carregar postagens:', data.message);
      }
    } catch (error) {
      console.error('Erro ao carregar postagens:', error);
    }
  }

  function renderPostagensTable(postagens) {
    const tbody = document.querySelector('#postagensTable tbody');
    if (!tbody) {
      const table = document.getElementById('postagensTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderPostagensTable(postagens);
    }

    if (postagens.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhuma postagem encontrada</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = postagens.map(postagem => `
      <tr>
        <td>#${postagem.service_id}</td>
        <td>
          <div>
            <strong>${postagem.prestador_nome}</strong>
            <br>
            <small class="text-muted">${postagem.prestador_email}</small>
          </div>
        </td>
        <td>
          <div>
            <strong>${postagem.titulo}</strong>
            <br>
            <small class="text-muted">${truncateText(postagem.descricao, 50)}</small>
          </div>
        </td>
        <td>
          <span class="badge bg-secondary">${postagem.categoria}</span>
        </td>
        <td>
          <strong>R$ ${postagem.preco}</strong>
        </td>
        <td>
          <span class="badge ${postagem.status === 'ativo' ? 'bg-success' : 'bg-secondary'}">
            ${postagem.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <span class="badge ${postagem.disponibilidade === 'disponivel' ? 'bg-info' : 'bg-warning'}">
            ${postagem.disponibilidade === 'disponivel' ? 'Disponível' : 'Indisponível'}
          </span>
        </td>
        <td>
          <small>${postagem.created_at}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-danger" onclick="showRemovePostagemModal(${postagem.service_id}, '${escapeHtml(postagem.titulo)}')" title="Remover">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  window.showRemovePostagemModal = function (serviceId, titulo) {
    const modal = document.getElementById('removePostagemModal');
    const confirmBtn = document.getElementById('confirmRemovePostagemBtn');
    confirmBtn.dataset.serviceId = serviceId;
    
    // Atualizar texto do modal se necessário
    const modalBody = modal.querySelector('.modal-body p');
    if (modalBody) {
      modalBody.textContent = `Tem certeza que deseja remover a postagem "${titulo}"? Esta ação não pode ser desfeita.`;
    }
    
    new bootstrap.Modal(modal).show();
  };

  async function removePostagem(serviceId, motivo) {
    try {
      const formData = new FormData();
      formData.append('service_id', serviceId);
      formData.append('motivo', motivo);

      const response = await fetch('../php/admin/remover-postagem.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Postagem removida com sucesso! A ação foi registrada no histórico administrativo.');
        bootstrap.Modal.getInstance(document.getElementById('removePostagemModal')).hide();
        loadPostagensData();
      } else {
        alert('Erro ao remover postagem: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao remover postagem');
    }
  }

  async function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
      try {
        // Clear session
        await fetch('../php/logout.php', { method: 'POST' });
        window.location.href = 'login/administrador-signin.html';
      } catch (error) {
        // Force redirect even if logout fails
        window.location.href = 'login/administrador-signin.html';
      }
    }
  }
});


```

Verificando se o arquivo de detalhes do usuário também precisa exibir o Instagram:


Atualizando o arquivo de detalhes do usuário para incluir o Instagram:


```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
    
    if (!$user_id) {
        echo json_encode(['error' => 'ID de usuário inválido']);
        exit;
    }
    
    // Buscar informações detalhadas do usuário
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                c.id as cliente_id,
                c.instagram,
                sp.service_provider_id,
                sp.specialty,
                sp.location as prestador_location
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }
    
    // Buscar estatísticas específicas do usuário
    $stats = [];
    
    if ($usuario['tipo_usuario'] === 'cliente') {
        // Estatísticas para clientes
        $stats_sql = "SELECT 
                        COUNT(sr.request_id) as total_solicitacoes,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as solicitacoes_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as solicitacoes_concluidas,
                        (SELECT COUNT(*) FROM proposal p 
                         JOIN service_request sr2 ON p.request_id = sr2.request_id 
                         WHERE sr2.cliente_id = ?) as propostas_recebidas
                      FROM service_request sr 
                      WHERE sr.cliente_id = ?";
        
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['cliente_id'], $usuario['cliente_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar últimas solicitações
        $solicitacoes_sql = "SELECT titulo, categoria, status, created_at 
                            FROM service_request 
                            WHERE cliente_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5";
        $solicitacoes_stmt = $pdo->prepare($solicitacoes_sql);
        $solicitacoes_stmt->execute([$usuario['cliente_id']]);
        $stats['ultimas_solicitacoes'] = $solicitacoes_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($usuario['tipo_usuario'] === 'prestador') {
        // Estatísticas para prestadores
        $stats_sql = "SELECT 
                        COUNT(p.proposal_id) as total_propostas,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as propostas_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as propostas_aceitas,
                        AVG(p.amount) as valor_medio_propostas
                      FROM proposal p 
                      JOIN service_request sr ON p.request_id = sr.request_id
                      WHERE p.service_provider_id = ?";
        
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['service_provider_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar últimas propostas
        $propostas_sql = "SELECT sr.titulo, p.amount, p.submitted_at, sr.status
                         FROM proposal p 
                         JOIN service_request sr ON p.request_id = sr.request_id
                         WHERE p.service_provider_id = ? 
                         ORDER BY p.submitted_at DESC 
                         LIMIT 5";
        $propostas_stmt = $pdo->prepare($propostas_sql);
        $propostas_stmt->execute([$usuario['service_provider_id']]);
        $stats['ultimas_propostas'] = $propostas_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Formatar resposta
    $resposta = [
        'success' => true,
        'usuario' => [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            'especialidade' => $usuario['specialty'],
            'localizacao' => $usuario['prestador_location']
        ],
        'estatisticas' => $stats
    ];
    
    echo json_encode($resposta);

} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>


```

```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
    
    if (!$user_id) {
        echo json_encode(['error' => 'ID de usuário inválido']);
        exit;
    }
    
    // Buscar informações detalhadas do usuário
    $sql = "SELECT 
                u.user_id,
                u.email,
                u.name,
                u.phone_number,
                u.status,
                u.identity_verified,
                u.created_at,
                u.updated_at,
                CASE 
                    WHEN c.id IS NOT NULL THEN 'cliente'
                    WHEN sp.service_provider_id IS NOT NULL THEN 'prestador'
                    ELSE 'indefinido'
                END as tipo_usuario,
                c.id as cliente_id,
                c.instagram,
                sp.service_provider_id,
                sp.specialty,
                sp.location as prestador_location
            FROM user u
            LEFT JOIN cliente c ON u.user_id = c.user_id
            LEFT JOIN service_provider sp ON u.user_id = sp.user_id
            WHERE u.user_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }
    
    // Buscar estatísticas específicas do usuário
    $stats = [];
    
    if ($usuario['tipo_usuario'] === 'cliente') {
        // Estatísticas para clientes
        $stats_sql = "SELECT 
                        COUNT(sr.request_id) as total_solicitacoes,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as solicitacoes_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as solicitacoes_concluidas,
                        (SELECT COUNT(*) FROM proposal p 
                         JOIN service_request sr2 ON p.request_id = sr2.request_id 
                         WHERE sr2.cliente_id = ?) as propostas_recebidas
                      FROM service_request sr 
                      WHERE sr.cliente_id = ?";
        
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['cliente_id'], $usuario['cliente_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar últimas solicitações
        $solicitacoes_sql = "SELECT titulo, categoria, status, created_at 
                            FROM service_request 
                            WHERE cliente_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5";
        $solicitacoes_stmt = $pdo->prepare($solicitacoes_sql);
        $solicitacoes_stmt->execute([$usuario['cliente_id']]);
        $stats['ultimas_solicitacoes'] = $solicitacoes_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($usuario['tipo_usuario'] === 'prestador') {
        // Estatísticas para prestadores
        $stats_sql = "SELECT 
                        COUNT(p.proposal_id) as total_propostas,
                        COUNT(CASE WHEN sr.status = 'Pendente' THEN 1 END) as propostas_pendentes,
                        COUNT(CASE WHEN sr.status = 'Concluído' THEN 1 END) as propostas_aceitas,
                        AVG(p.amount) as valor_medio_propostas
                      FROM proposal p 
                      JOIN service_request sr ON p.request_id = sr.request_id
                      WHERE p.service_provider_id = ?";
        
        $stats_stmt = $pdo->prepare($stats_sql);
        $stats_stmt->execute([$usuario['service_provider_id']]);
        $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Buscar últimas propostas
        $propostas_sql = "SELECT sr.titulo, p.amount, p.submitted_at, sr.status
                         FROM proposal p 
                         JOIN service_request sr ON p.request_id = sr.request_id
                         WHERE p.service_provider_id = ? 
                         ORDER BY p.submitted_at DESC 
                         LIMIT 5";
        $propostas_stmt = $pdo->prepare($propostas_sql);
        $propostas_stmt->execute([$usuario['service_provider_id']]);
        $stats['ultimas_propostas'] = $propostas_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Formatar resposta
    $resposta = [
        'success' => true,
        'usuario' => [
            'user_id' => $usuario['user_id'],
            'nome' => $usuario['name'],
            'email' => $usuario['email'],
            'telefone' => $usuario['phone_number'] ?: 'Não informado',
            'instagram' => $usuario['instagram'] ?: 'Não informado',
            'tipo_usuario' => $usuario['tipo_usuario'],
            'verificado' => $usuario['identity_verified'] ? true : false,
            'status' => $usuario['status'] ?: 'ativo',
            'data_cadastro' => date('d/m/Y H:i', strtotime($usuario['created_at'])),
            'ultima_atualizacao' => date('d/m/Y H:i', strtotime($usuario['updated_at'])),
            'especialidade' => $usuario['specialty'],
            'localizacao' => $usuario['prestador_location']
        ],
        'estatisticas' => $stats
    ];
    
    echo json_encode($resposta);

} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>


```

```javascript
document.addEventListener('DOMContentLoaded', function () {
  // Elementos DOM
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  const navLinks = document.querySelectorAll('.nav-link[data-section]');
  const contentSections = document.querySelectorAll('.content-section');
  const pageTitle = document.getElementById('pageTitle');
  const logoutBtn = document.getElementById('logoutBtn');
  const refreshBtn = document.getElementById('refreshBtn');
  const currentDate = document.getElementById('currentDate');

  // Verificar autenticação
  checkAuth();

  // Inicializar
  init();

  function init() {
    updateCurrentDate();
    loadDashboardData();
    setupEventListeners();
    showSection('overview'); // Mostrar seção de visão geral por padrão
  }

  function setupEventListeners() {
    // Toggle sidebar
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }

    // Navigation
    navLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const section = link.dataset.section;
        showSection(section);

        // Update active nav
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');

        // Close sidebar on mobile
        if (window.innerWidth < 992) {
          sidebar.classList.remove('show');
        }
      });
    });

    // Logout
    logoutBtn.addEventListener('click', (e) => {
      e.preventDefault();
      logout();
    });

    // Refresh
    refreshBtn.addEventListener('click', () => {
      loadDashboardData();
      const activeSection = document.querySelector('.content-section.active');
      if (activeSection) {
        if (activeSection.id === 'usersSection') {
          loadUsersData();
        } else if (activeSection.id === 'servicesSection') {
          loadServicesData();
        }
      }
    });

    // Search and filter for services
    const searchServices = document.getElementById('searchServices');
    const statusFilter = document.getElementById('statusFilter');

    if (searchServices) {
      searchServices.addEventListener('input', debounce(() => {
        loadServicesData();
      }, 300));
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', () => {
        loadServicesData();
      });
    }

    // Search and filter for users
    const searchUsers = document.getElementById('searchUsers');
    const tipoFilter = document.getElementById('tipoFilter');
    const statusFilterUsers = document.getElementById('statusFilterUsers');

    if (searchUsers) {
      searchUsers.addEventListener('input', debounce(() => {
        loadUsersData();
      }, 300));
    }

    if (tipoFilter) {
      tipoFilter.addEventListener('change', () => {
        loadUsersData();
      });
    }

    if (statusFilterUsers) {
      statusFilterUsers.addEventListener('change', () => {
        loadUsersData();
      });
    }

    // Search and filter for postagens
    const searchPostagens = document.getElementById('searchPostagens');
    const statusFilterPostagens = document.getElementById('statusFilterPostagens');
    const disponibilidadeFilterPostagens = document.getElementById('disponibilidadeFilterPostagens');

    if (searchPostagens) {
      searchPostagens.addEventListener('input', debounce(() => {
        loadPostagensData();
      }, 300));
    }

    if (statusFilterPostagens) {
      statusFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    if (disponibilidadeFilterPostagens) {
      disponibilidadeFilterPostagens.addEventListener('change', () => {
        loadPostagensData();
      });
    }

    // Confirm remove postagem button
    const confirmRemovePostagemBtn = document.getElementById('confirmRemovePostagemBtn');
    if (confirmRemovePostagemBtn) {
      confirmRemovePostagemBtn.addEventListener('click', () => {
        const serviceId = confirmRemovePostagemBtn.dataset.serviceId;
        const motivo = document.getElementById('motivoRemocao').value || 'Conteúdo inapropriado';
        removePostagem(serviceId, motivo);
      });
    }
  }

  async function checkAuth() {
    try {
      // Mostrar loading enquanto verifica autenticação
      const loadingDiv = document.createElement('div');
      loadingDiv.id = 'loadingAuth';
      loadingDiv.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
      loadingDiv.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
      loadingDiv.style.zIndex = '9999';
      loadingDiv.innerHTML = `
        <div class="text-center">
          <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Verificando acesso...</span>
          </div>
          <h5>Verificando permissões de administrador...</h5>
        </div>
      `;
      document.body.appendChild(loadingDiv);

      const response = await fetch('../php/admin/verificar-auth.php');
      const data = await response.json();

        // Remover loading
      document.getElementById('loadingAuth')?.remove();

      if (!data.authenticated) {
        // Mostrar alerta antes de redirecionar
        alert(data.usuario.message || 'Acesso negado. Você não tem permissões de administrador.');
        window.location.href = 'login/administrador-signin.html';
        return;
      }

      // Update user info
      document.getElementById('userName').textContent = data.usuario.nome;
      document.getElementById('userEmail').textContent = data.usuario.email;

      // Log de sucesso
      console.log('✅ Acesso autorizado para administrador:', data.usuario.nome);

    } catch (error) {
      console.error('❌ Erro na verificação de autenticação:', error);

      // Remover loading se houver erro
      document.getElementById('loadingAuth')?.remove();

      alert('Erro ao verificar autenticação. Redirecionando para login...');
      window.location.href = 'login/administrador-signin.html';
    }
  }

  async function loadDashboardData() {
    try {
      const response = await fetch('../php/admin/administrador-dashboard.php?action=stats');
      const data = await response.json();

      if (data.success) {
        const stats = data.data;
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('totalServices').textContent = stats.total_services || 0;
        document.getElementById('totalProposals').textContent = stats.total_proposals || 0;
        document.getElementById('totalContracts').textContent = stats.total_contracts || 0;
      }
    } catch (error) {
      console.error('Erro ao carregar estatísticas:', error);
    }
  }

  // ========== USER MANAGEMENT FUNCTIONS ==========
  let allUsers = [];

  async function loadUsersData() {
    try {
      const response = await fetch('../php/admin/listar-usuarios.php');
      const data = await response.json();

      if (data.success) {
        allUsers = data.usuarios;
        updateUsersStatistics(data.estatisticas);
        filterAndRenderUsers();
      } else {
        console.error('Erro ao carregar usuários:', data.error);
        alert('Erro ao carregar usuários: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro ao carregar usuários:', error);
      alert('Erro de conexão ao carregar usuários');
    }
  }

  function updateUsersStatistics(stats) {
    document.getElementById('statTotalUsuarios').textContent = stats.total_usuarios || 0;
    document.getElementById('statTotalClientes').textContent = stats.total_clientes || 0;
    document.getElementById('statTotalPrestadores').textContent = stats.total_prestadores || 0;
    document.getElementById('statUsuariosAtivos').textContent = stats.usuarios_ativos || 0;
  }

  function filterAndRenderUsers() {
    const searchTerm = document.getElementById('searchUsers')?.value.toLowerCase() || '';
    const tipoFilter = document.getElementById('tipoFilter')?.value || '';
    const statusFilter = document.getElementById('statusFilterUsers')?.value || '';

    let filteredUsers = allUsers.filter(user => {
      const matchesSearch = !searchTerm || 
        user.nome.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.telefone.toLowerCase().includes(searchTerm);
      
      const matchesTipo = !tipoFilter || user.tipo_usuario === tipoFilter;
      const matchesStatus = !statusFilter || user.status === statusFilter;

      return matchesSearch && matchesTipo && matchesStatus;
    });

    renderUsersTable(filteredUsers);
  }

  function renderUsersTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) return;

    if (users.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhum usuário encontrado</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = users.map(user => `
      <tr>
        <td>#${user.user_id}</td>
        <td>
          <div>
            <strong>${user.nome}</strong>
            ${user.verificado ? '<br><small class="text-success"><i class="fas fa-check-circle"></i> Verificado</small>' : ''}
          </div>
        </td>
        <td>${user.email}</td>
        <td>${user.telefone}</td>
        <td>${user.instagram || 'Não informado'}</td>
        <td>
          <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">
            ${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}
          </span>
        </td>
        <td>
          <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">
            ${user.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <small>${user.data_cadastro}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="viewUserDetails(${user.user_id})" title="Ver detalhes">
              <i class="fas fa-eye"></i>
            </button>
            ${user.status === 'ativo' 
              ? `<button class="btn btn-outline-warning" onclick="manageUser(${user.user_id}, 'desativar')" title="Desativar">
                   <i class="fas fa-ban"></i>
                 </button>`
              : `<button class="btn btn-outline-success" onclick="manageUser(${user.user_id}, 'ativar')" title="Ativar">
                   <i class="fas fa-check"></i>
                 </button>`
            }
            <button class="btn btn-outline-danger" onclick="manageUser(${user.user_id}, 'excluir')" title="Excluir">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  // Global functions for user management
  window.viewUserDetails = async function (userId) {
    try {
      const response = await fetch(`../php/admin/detalhes-usuario.php?user_id=${userId}`);
      const data = await response.json();

      if (data.success) {
        const user = data.usuario;
        const stats = data.estatisticas || {};
        
        let statsHtml = '';
        if (user.tipo_usuario === 'cliente') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Cliente</h6>
                <p><strong>Total de Solicitações:</strong> ${stats.total_solicitacoes || 0}</p>
                <p><strong>Solicitações Pendentes:</strong> ${stats.solicitacoes_pendentes || 0}</p>
                <p><strong>Solicitações Concluídas:</strong> ${stats.solicitacoes_concluidas || 0}</p>
                <p><strong>Propostas Recebidas:</strong> ${stats.propostas_recebidas || 0}</p>
              </div>
            </div>
          `;
        } else if (user.tipo_usuario === 'prestador') {
          statsHtml = `
            <div class="row">
              <div class="col-md-6">
                <h6><i class="fas fa-chart-bar me-2"></i>Estatísticas do Prestador</h6>
                <p><strong>Total de Propostas:</strong> ${stats.total_propostas || 0}</p>
                <p><strong>Propostas Pendentes:</strong> ${stats.propostas_pendentes || 0}</p>
                <p><strong>Propostas Aceitas:</strong> ${stats.propostas_aceitas || 0}</p>
                <p><strong>Valor Médio:</strong> R$ ${parseFloat(stats.valor_medio_propostas || 0).toFixed(2)}</p>
              </div>
            </div>
          `;
        }

        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações Pessoais</h6>
              <p><strong>Nome:</strong> ${user.nome}</p>
              <p><strong>Email:</strong> ${user.email}</p>
              <p><strong>Telefone:</strong> ${user.telefone}</p>
              ${user.instagram ? `<p><strong>Instagram:</strong> ${user.instagram}</p>` : ''}
              <p><strong>Tipo:</strong> <span class="badge ${user.tipo_usuario === 'cliente' ? 'bg-primary' : 'bg-info'}">${user.tipo_usuario === 'cliente' ? 'Cliente' : 'Prestador'}</span></p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-info-circle me-2"></i>Informações da Conta</h6>
              <p><strong>Status:</strong> <span class="badge ${user.status === 'ativo' ? 'bg-success' : 'bg-danger'}">${user.status === 'ativo' ? 'Ativo' : 'Inativo'}</span></p>
              <p><strong>Verificado:</strong> ${user.verificado ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-warning">Não</span>'}</p>
              <p><strong>Data de Cadastro:</strong> ${user.data_cadastro}</p>
              <p><strong>Última Atualização:</strong> ${user.ultima_atualizacao}</p>
            </div>
          </div>
          ${user.especialidade ? `<p><strong>Especialidade:</strong> ${user.especialidade}</p>` : ''}
          ${user.localizacao ? `<p><strong>Localização:</strong> ${user.localizacao}</p>` : ''}
          <hr>
          ${statsHtml}
        `;

        document.getElementById('userDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do usuário: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do usuário');
    }
  };

  window.manageUser = async function (userId, acao) {
    const acoes = {
      'ativar': 'ativar',
      'desativar': 'desativar',
      'excluir': 'excluir permanentemente'
    };

    const confirmMessage = acao === 'excluir' 
      ? 'Tem certeza que deseja EXCLUIR PERMANENTEMENTE este usuário? Esta ação não pode ser desfeita!'
      : `Tem certeza que deseja ${acoes[acao]} este usuário?`;

    if (!confirm(confirmMessage)) {
      return;
    }

    try {
      const formData = new FormData();
      formData.append('user_id', userId);
      formData.append('acao', acao);

      const response = await fetch('../php/admin/gerenciar-usuario.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert(data.message);
        loadUsersData(); // Recarregar lista de usuários
      } else {
        alert('Erro: ' + (data.error || 'Erro desconhecido'));
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao processar ação');
    }
  };

  // ========== SERVICES MANAGEMENT FUNCTIONS ==========
  async function loadServicesData() {
    try {
      const search = document.getElementById('searchServices')?.value || '';
      const status = document.getElementById('statusFilter')?.value || '';

      const params = new URLSearchParams({
        action: 'services',
        search: search,
        status: status
      });

      const response = await fetch(`../php/admin/administrador-dashboard.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderServicesTable(data.data);
      }
    } catch (error) {
      console.error('Erro ao carregar serviços:', error);
    }
  }

  function renderServicesTable(services) {
    const tbody = document.querySelector('#servicesTable tbody');
    if (!tbody) {
      // Create tbody if it doesn't exist
      const table = document.getElementById('servicesTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderServicesTable(services);
    }

    if (services.length === 0) {
      tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Nenhum serviço encontrado</p>
                    </td>
                </tr>
            `;
      return;
    }

    tbody.innerHTML = services.map(service => `
            <tr>
                <td>#${service.request_id}</td>
                <td>
                    <div>
                        <strong>${service.cliente_nome}</strong>
                        <br>
                        <small class="text-muted">${service.cliente_email}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${service.titulo}</strong>
                        <br>
                        <small class="text-muted">${truncateText(service.descricao, 50)}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-secondary">${service.categoria}</span>
                </td>
                <td>
                    <small>${service.cidade}</small>
                </td>
                <td>
                    <span class="badge ${getStatusClass(service.status)}">${service.status}</span>
                </td>
                <td>
                    <small>${formatDate(service.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewServiceDetails(${service.request_id})" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
  }

  // Global functions for service management
  window.viewServiceDetails = async function (serviceId) {
    try {
      const response = await fetch(`../php/admin/administrador-gerenciar.php?action=details&id=${serviceId}`);
      const data = await response.json();

      if (data.success) {
        const service = data.data;
        const content = `
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-user me-2"></i>Informações do Cliente</h6>
              <p><strong>Nome:</strong> ${service.cliente_nome}</p>
              <p><strong>Email:</strong> ${service.cliente_email}</p>
              <p><strong>Telefone:</strong> ${service.cliente_telefone || 'Não informado'}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-briefcase me-2"></i>Informações do Serviço</h6>
              <p><strong>Status:</strong> <span class="badge ${getStatusClass(service.status)}">${service.status}</span></p>
              <p><strong>Categoria:</strong> ${service.categoria}</p>
              <p><strong>Data de Criação:</strong> ${formatDate(service.created_at)}</p>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-info-circle me-2"></i>Detalhes do Serviço</h6>
              <p><strong>Título:</strong> ${service.titulo}</p>
              <p><strong>Descrição:</strong></p>
              <p class="bg-light p-3 rounded">${service.descricao}</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <h6><i class="fas fa-map-marker-alt me-2"></i>Localização</h6>
              <p><strong>Endereço:</strong> ${service.endereco}</p>
              <p><strong>Cidade:</strong> ${service.cidade}</p>
            </div>
            <div class="col-md-6">
              <h6><i class="fas fa-dollar-sign me-2"></i>Orçamento e Prazo</h6>
              <p><strong>Orçamento Máximo:</strong> R$ ${parseFloat(service.orcamento_maximo || 0).toFixed(2)}</p>
              <p><strong>Prazo Desejado:</strong> ${service.prazo_desejado ? formatDate(service.prazo_desejado) : 'Não definido'}</p>
            </div>
          </div>
          ${service.observacoes ? `
          <div class="row">
            <div class="col-12">
              <h6><i class="fas fa-sticky-note me-2"></i>Observações</h6>
              <p class="bg-light p-3 rounded">${service.observacoes}</p>
            </div>
          </div>
          ` : ''}
        `;

        document.getElementById('serviceDetailsContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
      } else {
        alert('Erro ao carregar detalhes do serviço: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao carregar detalhes do serviço');
    }
  };

  function showSection(sectionName) {
    // Hide all sections
    contentSections.forEach(section => {
      section.classList.remove('active');
    });

    // Show target section
    const targetSection = document.getElementById(sectionName + 'Section');
    if (targetSection) {
      targetSection.classList.add('active');
    }

    // Update page title
    const titles = {
      overview: 'Visão Geral',
      services: 'Gerenciar Serviços',
      users: 'Gerenciar Usuários',
      proposals: 'Propostas',
      contracts: 'Contratos',
      reviews: 'Avaliações',
      postagens: 'Gerenciar Postagens'
    };

    pageTitle.textContent = titles[sectionName] || 'Dashboard';

    // Load section specific data
    if (sectionName === 'services') {
      loadServicesData();
    } else if (sectionName === 'users') {
      loadUsersData();
    } else if (sectionName === 'postagens') {
      loadPostagensData();
    }
  }

  function getStatusClass(status) {
    const classes = {
      'Pendente': 'bg-warning',
      'Em Andamento': 'bg-info',
      'Concluído': 'bg-success',
      'Cancelado': 'bg-danger'
    };
    return classes[status] || 'bg-secondary';
  }

  function truncateText(text, length) {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  }

  function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('pt-BR');
  }

  function updateCurrentDate() {
    const now = new Date();
    currentDate.textContent = now.toLocaleDateString('pt-BR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // ========== POSTAGENS MANAGEMENT FUNCTIONS ==========
  async function loadPostagensData() {
    try {
      const search = document.getElementById('searchPostagens')?.value || '';
      const status = document.getElementById('statusFilterPostagens')?.value || '';
      const disponibilidade = document.getElementById('disponibilidadeFilterPostagens')?.value || '';

      const params = new URLSearchParams({
        search: search,
        status: status,
        disponibilidade: disponibilidade
      });

      const response = await fetch(`../php/admin/listar-postagens.php?${params}`);
      const data = await response.json();

      if (data.success) {
        renderPostagensTable(data.postagens);
      } else {
        console.error('Erro ao carregar postagens:', data.message);
      }
    } catch (error) {
      console.error('Erro ao carregar postagens:', error);
    }
  }

  function renderPostagensTable(postagens) {
    const tbody = document.querySelector('#postagensTable tbody');
    if (!tbody) {
      const table = document.getElementById('postagensTable');
      const newTbody = document.createElement('tbody');
      table.appendChild(newTbody);
      return renderPostagensTable(postagens);
    }

    if (postagens.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted">Nenhuma postagem encontrada</p>
          </td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = postagens.map(postagem => `
      <tr>
        <td>#${postagem.service_id}</td>
        <td>
          <div>
            <strong>${postagem.prestador_nome}</strong>
            <br>
            <small class="text-muted">${postagem.prestador_email}</small>
          </div>
        </td>
        <td>
          <div>
            <strong>${postagem.titulo}</strong>
            <br>
            <small class="text-muted">${truncateText(postagem.descricao, 50)}</small>
          </div>
        </td>
        <td>
          <span class="badge bg-secondary">${postagem.categoria}</span>
        </td>
        <td>
          <strong>R$ ${postagem.preco}</strong>
        </td>
        <td>
          <span class="badge ${postagem.status === 'ativo' ? 'bg-success' : 'bg-secondary'}">
            ${postagem.status === 'ativo' ? 'Ativo' : 'Inativo'}
          </span>
        </td>
        <td>
          <span class="badge ${postagem.disponibilidade === 'disponivel' ? 'bg-info' : 'bg-warning'}">
            ${postagem.disponibilidade === 'disponivel' ? 'Disponível' : 'Indisponível'}
          </span>
        </td>
        <td>
          <small>${postagem.created_at}</small>
        </td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-danger" onclick="showRemovePostagemModal(${postagem.service_id}, '${escapeHtml(postagem.titulo)}')" title="Remover">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `).join('');
  }

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  window.showRemovePostagemModal = function (serviceId, titulo) {
    const modal = document.getElementById('removePostagemModal');
    const confirmBtn = document.getElementById('confirmRemovePostagemBtn');
    confirmBtn.dataset.serviceId = serviceId;
    
    // Atualizar texto do modal se necessário
    const modalBody = modal.querySelector('.modal-body p');
    if (modalBody) {
      modalBody.textContent = `Tem certeza que deseja remover a postagem "${titulo}"? Esta ação não pode ser desfeita.`;
    }
    
    new bootstrap.Modal(modal).show();
  };

  async function removePostagem(serviceId, motivo) {
    try {
      const formData = new FormData();
      formData.append('service_id', serviceId);
      formData.append('motivo', motivo);

      const response = await fetch('../php/admin/remover-postagem.php', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Postagem removida com sucesso! A ação foi registrada no histórico administrativo.');
        bootstrap.Modal.getInstance(document.getElementById('removePostagemModal')).hide();
        loadPostagensData();
      } else {
        alert('Erro ao remover postagem: ' + data.message);
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Erro de conexão ao remover postagem');
    }
  }

  async function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
      try {
        // Clear session
        await fetch('../php/logout.php', { method: 'POST' });
        window.location.href = 'login/administrador-signin.html';
      } catch (error) {
        // Force redirect even if logout fails
        window.location.href = 'login/administrador-signin.html';
      }
    }
  }
});


```

Verificando erros e preparando um resumo. O usuário mencionou `index.html`, mas o formulário de cadastro está em `client/registro/index.html`. Verificando se há um `index.html` na raiz que também precise ser atualizado:


## Resumo das alterações

### 1. Formulário de cadastro (`client/registro/index.html`)
- Adicionado campo "Instagram" na seção de campos específicos do cliente

### 2. Backend - Cadastro (`php/cliente/cliente-signup.php`)
- Atualizado para coletar e salvar o campo Instagram no banco de dados
- Função `criarCliente()` atualizada para aceitar o parâmetro Instagram

### 3. Banco de dados
- Criado script de migração (`lib/migration-add-instagram.sql`) para adicionar a coluna `instagram` na tabela `cliente`
- Execute este script no MySQL antes de usar a funcionalidade

### 4. Listagem de usuários (`php/admin/listar-usuarios.php`)
- Query SQL atualizada para incluir o campo Instagram
- Resposta JSON atualizada para incluir o Instagram

### 5. Dashboard do administrador (`client/administrador-dashboard.html`)
- Adicionada coluna "Instagram" no cabeçalho da tabela de usuários

### 6. JavaScript do dashboard (`assets/js/administrador-dashboard.js`)
- Função `renderUsersTable()` atualizada para exibir o Instagram na tabela
- Colspan atualizado para 9 colunas
- Modal de detalhes do usuário atualizado para exibir o Instagram

### 7. Detalhes do usuário (`php/admin/detalhes-usuario.php`)
- Query SQL atualizada para incluir o Instagram
- Resposta JSON atualizada para incluir o Instagram

## Próximos passos

1. Execute o script SQL de migração:
   ```sql
   -- Execute o arquivo lib/migration-add-instagram.sql no seu banco de dados MySQL
   ```

2. Teste o cadastro:
   - Acesse o formulário de cadastro de cliente
   - Preencha o campo Instagram
   - Verifique se o dado é salvo corretamente

3. Verifique no dashboard:
   - Acesse o dashboard do administrador
   - Vá para a seção "Gerenciar Usuários"
   - Verifique se a coluna Instagram aparece e exibe os dados corretamente

Todas as alterações foram concluídas. O campo Instagram agora está integrado no cadastro de clientes e aparece no dashboard do administrador.

```sql
   -- Execute o arquivo lib/migration-add-instagram.sql no seu banco de dados MySQL
```

