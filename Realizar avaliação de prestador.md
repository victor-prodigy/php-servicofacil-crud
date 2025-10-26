# Como realizar avaliação de prestador
_Exported on 10/20/2025 at 10:00:51 GMT-3 from Cursor (1.7.46)_

---

**User**

@https://trello.com/b/mjSYhzVd/gestao-de-projeto  de acordo com o trello como fazer o pbi "[SF-003] Realizar avaliação de prestador" nesse projeto?

---

**Cursor**

Infelizmente, não consegui acessar o conteúdo do Trello board - a página não carregou os recursos. No entanto, posso ajudá-lo a implementar o PBI "[SF-003] Realizar avaliação de prestador" analisando a estrutura atual do projeto.

Deixe-me explorar o código existente para entender melhor a estrutura e propor uma solução:

Perfeito! Analisando o código do projeto, posso te orientar sobre como implementar o **PBI "[SF-003] Realizar avaliação de prestador"**. 

Notei que a **tabela `review` já existe no banco de dados** (linhas 142-154 do `lib/db.sql`), mas ainda não há implementação da funcionalidade. Aqui está o guia completo:

## 📋 Implementação do PBI [SF-003] - Realizar Avaliação de Prestador

### 🎯 Objetivo
Permitir que clientes avaliem prestadores após a conclusão de um serviço contratado.

### 📊 Estrutura do Banco de Dados (Já Existe)
A tabela `review` já está criada com:
- `review_id` - ID da avaliação
- `contract_id` - Referência ao contrato
- `cliente_id` - Cliente que avalia
- `rating` - Nota de 1 a 5 estrelas
- `comment` - Comentário opcional
- `created_at` - Data da avaliação

### 🔧 Arquivos a Criar/Modificar

#### **1. Backend - PHP**

**Criar: `php/servico/criar-avaliacao.php`**
```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    $contract_id = $data['contract_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? '';
    $cliente_id = $_SESSION['cliente_id'];

    // Validações
    if (!$contract_id || !$rating) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Nota deve estar entre 1 e 5');
    }

    // Verificar se o contrato pertence ao cliente e está concluído
    $sql_check = "SELECT c.contract_id, c.status, c.cliente_id 
                  FROM contract c 
                  WHERE c.contract_id = ? AND c.cliente_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$contract_id, $cliente_id]);
    $contract = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        throw new Exception('Contrato não encontrado ou você não tem permissão');
    }

    if ($contract['status'] !== 'completed') {
        throw new Exception('Só é possível avaliar contratos concluídos');
    }

    // Verificar se já existe avaliação para este contrato
    $sql_exists = "SELECT review_id FROM review WHERE contract_id = ? AND cliente_id = ?";
    $stmt_exists = $pdo->prepare($sql_exists);
    $stmt_exists->execute([$contract_id, $cliente_id]);
    
    if ($stmt_exists->fetch()) {
        throw new Exception('Você já avaliou este serviço');
    }

    // Inserir avaliação
    $sql_insert = "INSERT INTO review (contract_id, cliente_id, rating, comment) 
                   VALUES (?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$contract_id, $cliente_id, $rating, $comment]);

    echo json_encode([
        'success' => true,
        'message' => 'Avaliação registrada com sucesso!',
        'review_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

**Criar: `php/servico/listar-avaliacoes-prestador.php`**
```php
<?php
session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

try {
    $prestador_id = $_GET['prestador_id'] ?? null;

    if (!$prestador_id) {
        throw new Exception('ID do prestador não fornecido');
    }

    // Buscar avaliações do prestador
    $sql = "SELECT 
                r.review_id,
                r.rating,
                r.comment,
                r.created_at,
                u.name as cliente_nome,
                sr.titulo as servico_titulo,
                sr.categoria
            FROM review r
            INNER JOIN contract c ON r.contract_id = c.contract_id
            INNER JOIN cliente cl ON r.cliente_id = cl.id
            INNER JOIN user u ON cl.user_id = u.user_id
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            WHERE c.service_provider_id = ?
            ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestador_id]);
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular média de avaliações
    $sql_media = "SELECT AVG(r.rating) as media, COUNT(r.review_id) as total
                  FROM review r
                  INNER JOIN contract c ON r.contract_id = c.contract_id
                  WHERE c.service_provider_id = ?";
    $stmt_media = $pdo->prepare($sql_media);
    $stmt_media->execute([$prestador_id]);
    $estatisticas = $stmt_media->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'avaliacoes' => $avaliacoes,
        'media' => round($estatisticas['media'], 1),
        'total' => $estatisticas['total']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

**Criar: `php/servico/listar-contratos-cliente.php`**
```php
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $cliente_id = $_SESSION['cliente_id'];

    // Buscar contratos do cliente
    $sql = "SELECT 
                c.contract_id,
                c.status,
                c.created_at,
                sr.titulo,
                sr.categoria,
                u.name as prestador_nome,
                sp.specialty,
                sp.location,
                CASE 
                    WHEN r.review_id IS NOT NULL THEN 1
                    ELSE 0
                END as ja_avaliado
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            LEFT JOIN review r ON c.contract_id = r.contract_id AND r.cliente_id = ?
            WHERE c.cliente_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id, $cliente_id]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'contratos' => $contratos
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

#### **2. Frontend - HTML**

**Criar: `client/servico/avaliar-prestador.html`**
```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Prestador - ServiçoFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/forms.css" rel="stylesheet">
    <style>
        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 2rem;
        }
        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        .star:hover,
        .star.active {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-star-fill"></i> Avaliar Prestador</h3>
                    </div>
                    <div class="card-body">
                        <div id="contratoInfo" class="mb-4">
                            <!-- Informações do contrato serão carregadas aqui -->
                        </div>

                        <form id="formAvaliacao">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nota do Serviço *</label>
                                <div class="star-rating" id="starRating">
                                    <i class="bi bi-star-fill star" data-rating="1"></i>
                                    <i class="bi bi-star-fill star" data-rating="2"></i>
                                    <i class="bi bi-star-fill star" data-rating="3"></i>
                                    <i class="bi bi-star-fill star" data-rating="4"></i>
                                    <i class="bi bi-star-fill star" data-rating="5"></i>
                                </div>
                                <input type="hidden" id="rating" name="rating" required>
                                <div class="invalid-feedback">Selecione uma nota</div>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentário (opcional)</label>
                                <textarea 
                                    class="form-control" 
                                    id="comment" 
                                    name="comment" 
                                    rows="4" 
                                    placeholder="Compartilhe sua experiência com este prestador..."
                                    maxlength="500"
                                ></textarea>
                                <div class="form-text">Máximo 500 caracteres</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Enviar Avaliação
                                </button>
                                <a href="../cliente-dashboard.html" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/avaliar-prestador.js"></script>
</body>
</html>
```

#### **3. Frontend - JavaScript**

**Criar: `assets/js/avaliar-prestador.js`**
```javascript
// Obter ID do contrato da URL
const urlParams = new URLSearchParams(window.location.search);
const contractId = urlParams.get('contract_id');

let selectedRating = 0;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    if (!contractId) {
        alert('ID do contrato não fornecido');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    carregarInfoContrato();
    inicializarEstrelas();
    inicializarFormulario();
});

// Carregar informações do contrato
async function carregarInfoContrato() {
    try {
        const response = await fetch(`../../php/servico/listar-contratos-cliente.php`);
        const data = await response.json();

        if (data.success) {
            const contrato = data.contratos.find(c => c.contract_id == contractId);
            
            if (!contrato) {
                throw new Error('Contrato não encontrado');
            }

            if (contrato.ja_avaliado == 1) {
                alert('Você já avaliou este serviço');
                window.location.href = '../cliente-dashboard.html';
                return;
            }

            if (contrato.status !== 'completed') {
                alert('Este contrato ainda não foi concluído');
                window.location.href = '../cliente-dashboard.html';
                return;
            }

            // Exibir informações
            document.getElementById('contratoInfo').innerHTML = `
                <div class="alert alert-info">
                    <h5 class="mb-2"><i class="bi bi-info-circle"></i> Informações do Serviço</h5>
                    <p class="mb-1"><strong>Serviço:</strong> ${contrato.titulo}</p>
                    <p class="mb-1"><strong>Categoria:</strong> ${contrato.categoria}</p>
                    <p class="mb-1"><strong>Prestador:</strong> ${contrato.prestador_nome}</p>
                    <p class="mb-0"><strong>Especialidade:</strong> ${contrato.specialty || 'Não informada'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar informações do contrato');
    }
}

// Sistema de estrelas
function inicializarEstrelas() {
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-rating'));
            ratingInput.value = selectedRating;
            
            // Atualizar visual das estrelas
            stars.forEach(s => {
                const starRating = parseInt(s.getAttribute('data-rating'));
                if (starRating <= selectedRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });

        // Efeito hover
        star.addEventListener('mouseenter', function() {
            const hoverRating = parseInt(this.getAttribute('data-rating'));
            stars.forEach(s => {
                const starRating = parseInt(s.getAttribute('data-rating'));
                if (starRating <= hoverRating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });

    // Resetar ao sair do mouse
    document.getElementById('starRating').addEventListener('mouseleave', function() {
        stars.forEach(s => {
            const starRating = parseInt(s.getAttribute('data-rating'));
            if (starRating <= selectedRating) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
}

// Enviar avaliação
function inicializarFormulario() {
    document.getElementById('formAvaliacao').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (selectedRating === 0) {
            alert('Por favor, selecione uma nota');
            return;
        }

        const formData = {
            contract_id: contractId,
            rating: selectedRating,
            comment: document.getElementById('comment').value.trim()
        };

        try {
            const response = await fetch('../../php/servico/criar-avaliacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                alert('Avaliação enviada com sucesso!');
                window.location.href = '../cliente-dashboard.html';
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar avaliação');
        }
    });
}
```

#### **4. Modificar Dashboard do Cliente**

Adicione na tabela de contratos do `cliente-dashboard.html` (após linha 76):
```html
<th>Avaliação</th>
```

E no JavaScript `cliente-dashboard.js`, adicione botão de avaliação para contratos concluídos:
```javascript
// Adicionar coluna de avaliação na tabela
if (contrato.status === 'completed' && !contrato.ja_avaliado) {
    html += `<td>
        <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
           class="btn btn-sm btn-warning">
            <i class="bi bi-star-fill"></i> Avaliar
        </a>
    </td>`;
} else if (contrato.ja_avaliado) {
    html += `<td><span class="badge bg-success">Já avaliado</span></td>`;
} else {
    html += `<td><span class="badge bg-secondary">Pendente</span></td>`;
}
```

### ✅ Checklist de Implementação

- [ ] Criar `php/servico/criar-avaliacao.php`
- [ ] Criar `php/servico/listar-avaliacoes-prestador.php`
- [ ] Criar `php/servico/listar-contratos-cliente.php`
- [ ] Criar `client/servico/avaliar-prestador.html`
- [ ] Criar `assets/js/avaliar-prestador.js`
- [ ] Modificar `cliente-dashboard.html` para incluir botão de avaliação
- [ ] Modificar `cliente-dashboard.js` para carregar contratos
- [ ] (Opcional) Exibir média de avaliações no perfil do prestador

### 🧪 Testes Recomendados

1. Cliente só pode avaliar contratos concluídos
2. Cliente não pode avaliar o mesmo contrato duas vezes
3. Nota deve ser entre 1 e 5
4. Comentário é opcional
5. Visualizar avaliações no perfil do prestador

Este é um guia completo baseado na estrutura atual do seu projeto! 🚀

```php
<?php
session_start();
header('Content-Type: application/json');

// Verificar se o usuário está logado e é cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    $contract_id = $data['contract_id'] ?? null;
    $rating = $data['rating'] ?? null;
    $comment = $data['comment'] ?? '';
    $cliente_id = $_SESSION['cliente_id'];

    // Validações
    if (!$contract_id || !$rating) {
        throw new Exception('Dados obrigatórios não fornecidos');
    }

    if ($rating < 1 || $rating > 5) {
        throw new Exception('Nota deve estar entre 1 e 5');
    }

    // Verificar se o contrato pertence ao cliente e está concluído
    $sql_check = "SELECT c.contract_id, c.status, c.cliente_id 
                  FROM contract c 
                  WHERE c.contract_id = ? AND c.cliente_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$contract_id, $cliente_id]);
    $contract = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$contract) {
        throw new Exception('Contrato não encontrado ou você não tem permissão');
    }

    if ($contract['status'] !== 'completed') {
        throw new Exception('Só é possível avaliar contratos concluídos');
    }

    // Verificar se já existe avaliação para este contrato
    $sql_exists = "SELECT review_id FROM review WHERE contract_id = ? AND cliente_id = ?";
    $stmt_exists = $pdo->prepare($sql_exists);
    $stmt_exists->execute([$contract_id, $cliente_id]);
    
    if ($stmt_exists->fetch()) {
        throw new Exception('Você já avaliou este serviço');
    }

    // Inserir avaliação
    $sql_insert = "INSERT INTO review (contract_id, cliente_id, rating, comment) 
                   VALUES (?, ?, ?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$contract_id, $cliente_id, $rating, $comment]);

    echo json_encode([
        'success' => true,
        'message' => 'Avaliação registrada com sucesso!',
        'review_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

```php
<?php
session_start();
header('Content-Type: application/json');

require_once '../conexao.php';

try {
    $prestador_id = $_GET['prestador_id'] ?? null;

    if (!$prestador_id) {
        throw new Exception('ID do prestador não fornecido');
    }

    // Buscar avaliações do prestador
    $sql = "SELECT 
                r.review_id,
                r.rating,
                r.comment,
                r.created_at,
                u.name as cliente_nome,
                sr.titulo as servico_titulo,
                sr.categoria
            FROM review r
            INNER JOIN contract c ON r.contract_id = c.contract_id
            INNER JOIN cliente cl ON r.cliente_id = cl.id
            INNER JOIN user u ON cl.user_id = u.user_id
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            WHERE c.service_provider_id = ?
            ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prestador_id]);
    $avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular média de avaliações
    $sql_media = "SELECT AVG(r.rating) as media, COUNT(r.review_id) as total
                  FROM review r
                  INNER JOIN contract c ON r.contract_id = c.contract_id
                  WHERE c.service_provider_id = ?";
    $stmt_media = $pdo->prepare($sql_media);
    $stmt_media->execute([$prestador_id]);
    $estatisticas = $stmt_media->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'avaliacoes' => $avaliacoes,
        'media' => round($estatisticas['media'], 1),
        'total' => $estatisticas['total']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

```php
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

require_once '../conexao.php';

try {
    $cliente_id = $_SESSION['cliente_id'];

    // Buscar contratos do cliente
    $sql = "SELECT 
                c.contract_id,
                c.status,
                c.created_at,
                sr.titulo,
                sr.categoria,
                u.name as prestador_nome,
                sp.specialty,
                sp.location,
                CASE 
                    WHEN r.review_id IS NOT NULL THEN 1
                    ELSE 0
                END as ja_avaliado
            FROM contract c
            INNER JOIN service_request sr ON c.request_id = sr.request_id
            INNER JOIN service_provider sp ON c.service_provider_id = sp.service_provider_id
            INNER JOIN user u ON sp.user_id = u.user_id
            LEFT JOIN review r ON c.contract_id = r.contract_id AND r.cliente_id = ?
            WHERE c.cliente_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id, $cliente_id]);
    $contratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'contratos' => $contratos
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
```

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Prestador - ServiçoFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="../../assets/css/forms.css" rel="stylesheet">
    <style>
        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 2rem;
        }
        .star {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        .star:hover,
        .star.active {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="bi bi-star-fill"></i> Avaliar Prestador</h3>
                    </div>
                    <div class="card-body">
                        <div id="contratoInfo" class="mb-4">
                            <!-- Informações do contrato serão carregadas aqui -->
                        </div>

                        <form id="formAvaliacao">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Nota do Serviço *</label>
                                <div class="star-rating" id="starRating">
                                    <i class="bi bi-star-fill star" data-rating="1"></i>
                                    <i class="bi bi-star-fill star" data-rating="2"></i>
                                    <i class="bi bi-star-fill star" data-rating="3"></i>
                                    <i class="bi bi-star-fill star" data-rating="4"></i>
                                    <i class="bi bi-star-fill star" data-rating="5"></i>
                                </div>
                                <input type="hidden" id="rating" name="rating" required>
                                <div class="invalid-feedback">Selecione uma nota</div>
                            </div>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Comentário (opcional)</label>
                                <textarea 
                                    class="form-control" 
                                    id="comment" 
                                    name="comment" 
                                    rows="4" 
                                    placeholder="Compartilhe sua experiência com este prestador..."
                                    maxlength="500"
                                ></textarea>
                                <div class="form-text">Máximo 500 caracteres</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Enviar Avaliação
                                </button>
                                <a href="../cliente-dashboard.html" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/avaliar-prestador.js"></script>
</body>
</html>
```

```javascript
// Obter ID do contrato da URL
const urlParams = new URLSearchParams(window.location.search);
const contractId = urlParams.get('contract_id');

let selectedRating = 0;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    if (!contractId) {
        alert('ID do contrato não fornecido');
        window.location.href = '../cliente-dashboard.html';
        return;
    }

    carregarInfoContrato();
    inicializarEstrelas();
    inicializarFormulario();
});

// Carregar informações do contrato
async function carregarInfoContrato() {
    try {
        const response = await fetch(`../../php/servico/listar-contratos-cliente.php`);
        const data = await response.json();

        if (data.success) {
            const contrato = data.contratos.find(c => c.contract_id == contractId);
            
            if (!contrato) {
                throw new Error('Contrato não encontrado');
            }

            if (contrato.ja_avaliado == 1) {
                alert('Você já avaliou este serviço');
                window.location.href = '../cliente-dashboard.html';
                return;
            }

            if (contrato.status !== 'completed') {
                alert('Este contrato ainda não foi concluído');
                window.location.href = '../cliente-dashboard.html';
                return;
            }

            // Exibir informações
            document.getElementById('contratoInfo').innerHTML = `
                <div class="alert alert-info">
                    <h5 class="mb-2"><i class="bi bi-info-circle"></i> Informações do Serviço</h5>
                    <p class="mb-1"><strong>Serviço:</strong> ${contrato.titulo}</p>
                    <p class="mb-1"><strong>Categoria:</strong> ${contrato.categoria}</p>
                    <p class="mb-1"><strong>Prestador:</strong> ${contrato.prestador_nome}</p>
                    <p class="mb-0"><strong>Especialidade:</strong> ${contrato.specialty || 'Não informada'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao carregar informações do contrato');
    }
}

// Sistema de estrelas
function inicializarEstrelas() {
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-rating'));
            ratingInput.value = selectedRating;
            
            // Atualizar visual das estrelas
            stars.forEach(s => {
                const starRating = parseInt(s.getAttribute('data-rating'));
                if (starRating <= selectedRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });

        // Efeito hover
        star.addEventListener('mouseenter', function() {
            const hoverRating = parseInt(this.getAttribute('data-rating'));
            stars.forEach(s => {
                const starRating = parseInt(s.getAttribute('data-rating'));
                if (starRating <= hoverRating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });

    // Resetar ao sair do mouse
    document.getElementById('starRating').addEventListener('mouseleave', function() {
        stars.forEach(s => {
            const starRating = parseInt(s.getAttribute('data-rating'));
            if (starRating <= selectedRating) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
    });
}

// Enviar avaliação
function inicializarFormulario() {
    document.getElementById('formAvaliacao').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (selectedRating === 0) {
            alert('Por favor, selecione uma nota');
            return;
        }

        const formData = {
            contract_id: contractId,
            rating: selectedRating,
            comment: document.getElementById('comment').value.trim()
        };

        try {
            const response = await fetch('../../php/servico/criar-avaliacao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                alert('Avaliação enviada com sucesso!');
                window.location.href = '../cliente-dashboard.html';
            } else {
                alert('Erro: ' + data.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('Erro ao enviar avaliação');
        }
    });
}
```

```html
<th>Avaliação</th>
```

```javascript
// Adicionar coluna de avaliação na tabela
if (contrato.status === 'completed' && !contrato.ja_avaliado) {
    html += `<td>
        <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
           class="btn btn-sm btn-warning">
            <i class="bi bi-star-fill"></i> Avaliar
        </a>
    </td>`;
} else if (contrato.ja_avaliado) {
    html += `<td><span class="badge bg-success">Já avaliado</span></td>`;
} else {
    html += `<td><span class="badge bg-secondary">Pendente</span></td>`;
}
```

---

**User**

@cliente-dashboard.js "// Adicionar coluna de avaliação na tabela
if (contrato.status === 'completed' && !contrato.ja_avaliado) {
    html += `<td>
        <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
           class="btn btn-sm btn-warning">
            <i class="bi bi-star-fill"></i> Avaliar
        </a>
    </td>`;
} else if (contrato.ja_avaliado) {
    html += `<td><span class="badge bg-success">Já avaliado</span></td>`;
} else {
    html += `<td><span class="badge bg-secondary">Pendente</span></td>`;
}" onde adicionar isso no cliente-dashboard.js

---

**Cursor**

Perfeito! Vou ajudá-lo a adicionar a funcionalidade de avaliação no `cliente-dashboard.js`. Você precisa criar uma nova função para carregar os **contratos** do cliente (diferente das solicitações). Vou implementar isso:

Agora vou implementar as mudanças necessárias:

```html
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ServiçoFácil - Dashboard Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet" />
    <link href="../assets/css/dashboard.css" rel="stylesheet" />
  </head>
  <body>
    <div class="container mt-4">
      <div class="row">
        <div class="col-12">
          <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
              <span class="navbar-brand">ServiçoFácil</span>
              <div class="d-flex">
                <span class="navbar-text me-3">
                  Bem-vindo(a), <span id="userName">Carregando...</span>
                </span>
                <button class="btn btn-outline-danger btn-sm" onclick="logout()">
                  <i class="bi bi-box-arrow-right"></i> Sair
                </button>
              </div>
            </div>
          </nav>
        </div>
      </div>
      
      <h1>Dashboard do Cliente</h1>
      <div id="dashboardContent" style="display: none;">
        
        <!-- Ações Rápidas -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-light">
              <div class="card-body">
                <h5 class="card-title">
                  <i class="bi bi-lightning-fill text-warning"></i>
                  Ações Rápidas
                </h5>
                <div class="d-flex flex-wrap gap-2">
                  <a href="./servico/nova-solicitacao.html" class="btn btn-success">
                    <i class="bi bi-plus-circle-fill"></i>
                    Solicitar Serviço
                  </a>
                  <a href="#" onclick="carregarServicos()" class="btn btn-info">
                    <i class="bi bi-arrow-clockwise"></i>
                    Atualizar Serviços
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

          <div class="card">
            <div class="card-header">
              <h3>
                <i class="bi bi-journal-text"></i>
                Minhas Solicitações de Serviço
              </h3>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Título</th>
                      <th>Categoria</th>
                      <th>Prazo</th>
                      <th>Orçamento</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="solicitacoesTable">
                    <!-- Solicitações serão carregadas aqui via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Seção de Contratos (para avaliação) -->
        <div class="mt-4">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="bi bi-file-earmark-text"></i>
                Meus Contratos
              </h3>
              <p class="mb-0 text-muted">Acompanhe seus contratos e avalie os prestadores</p>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Serviço</th>
                      <th>Prestador</th>
                      <th>Categoria</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Avaliação</th>
                    </tr>
                  </thead>
                  <tbody id="contratosTable">
                    <!-- Contratos serão carregados aqui via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Seção de Serviços Disponíveis (Visualização) -->
        <div class="mt-4">
          <div class="card">
            <div class="card-header">
              <h3>
                <i class="bi bi-shop"></i>
                Serviços Disponíveis
              </h3>
              <p class="mb-0 text-muted">Explore serviços oferecidos pelos prestadores</p>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Título</th>
                      <th>Categoria</th>
                      <th>Preço</th>
                      <th>Status</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody id="servicosTable">
                    <!-- Serviços serão carregados aqui via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      
      <!-- Botões de Ação Fixos -->
      <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="./servico/nova-solicitacao.html" class="btn btn-success">
          <i class="bi bi-plus-circle-fill"></i>
          Solicitar Novo Serviço
        </a>
        <button onclick="carregarServicos()" class="btn btn-info">
          <i class="bi bi-arrow-clockwise"></i>
          Atualizar Lista
        </button>
      </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/cliente-dashboard.js"></script>
  </body>
</html>

```

Agora vou adicionar a função para carregar contratos no JavaScript:

```javascript
document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/cliente/cliente-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para a página de login
            alert(data.message);
            window.location.href = './login/index.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza o nome do usuário
        document.getElementById('userName').textContent = data.nome;
        document.getElementById('dashboardContent').style.display = 'block';

        // Carregar a lista de serviços, solicitações e contratos
        carregarServicos();
        carregarSolicitacoes();
        carregarContratos();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarServicos() {
    try {
        const response = await fetch('../php/servico/listar-servico.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum serviço disponível no momento.
                    </td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');

            // Formatar data
            const data_formatada = new Date(servico.data_postagem).toLocaleDateString('pt-BR');

            // Definir classe do status
            const statusClass = getStatusClass(servico.status);

            // Formatar orçamento
            const orcamento = servico.orcamento ?
                new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(servico.orcamento) : 'A combinar';

            row.innerHTML = `
                <td>
                    <strong>${servico.titulo}</strong>
                    <br>
                    <small class="text-muted">Por: ${servico.prestador_nome || 'Prestador'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${servico.categoria}</span>
                </td>
                <td>${orcamento}</td>
                <td><span class="badge ${statusClass}">${getStatusText(servico.status)}</span></td>
                <td>
                    <small>${data_formatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhesServicoPublicado(${servico.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="contratarServico(${servico.id})" 
                                title="Contratar">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar serviços: ${error.message}
                </td>
            </tr>
        `;
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

        const response = await fetch('../php/servico/apaga-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Serviço excluído com sucesso!');
            carregarServicos(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        alert('Erro ao excluir serviço: ' + error.message);
    }
}

function logout() {
    // Limpar dados da sessão no localStorage
    localStorage.clear();

    // Fazer requisição para destruir a sessão no servidor
    fetch('../php/logout.php')
        .then(() => {
            // Redirecionar para a página de login
            window.location.href = './login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            // Mesmo com erro, redireciona para garantir que o usuário saia
            window.location.href = './login/index.html';
        });
}

// Função para carregar solicitações de serviço
async function carregarSolicitacoes() {
    try {
        const response = await fetch('../php/servico/listar-solicitacoes.php');
        const data = await response.json();

        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de serviço encontrada.
                        <br>
                        <a href="./servico/nova-solicitacao.html" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Fazer primeira solicitação
                        </a>
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada solicitação à tabela
        data.solicitacoes.forEach(solicitacao => {
            const row = document.createElement('tr');

            const dataFormatada = new Date(solicitacao.data_criacao).toLocaleDateString('pt-BR');
            const orcamento = solicitacao.orcamento_maximo ?
                `R$ ${parseFloat(solicitacao.orcamento_maximo).toFixed(2)}` :
                'Flexível';

            row.innerHTML = `
                <td>
                    <strong>${solicitacao.titulo}</strong>
                    <br>
                    <small class="text-muted">${solicitacao.cidade}</small>
                </td>
                <td>
                    <span class="badge bg-info">${solicitacao.categoria}</span>
                </td>
                <td>
                    <small>${solicitacao.prazo_desejado}</small>
                </td>
                <td>${orcamento}</td>
                <td>
                    <span class="badge ${getStatusClassSolicitacao(solicitacao.status)}">
                        ${getStatusTextSolicitacao(solicitacao.status)}
                    </span>
                </td>
                <td>
                    <small>${dataFormatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhes(${solicitacao.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editarSolicitacao(${solicitacao.id})" 
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="excluirSolicitacao(${solicitacao.id})" 
                                title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar solicitações:', error);
        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar solicitações: ${error.message}
                </td>
            </tr>
        `;
    }
}

// Funções auxiliares para status das solicitações
function getStatusClassSolicitacao(status) {
    const statusClasses = {
        'pendente': 'bg-warning text-dark',
        'em_andamento': 'bg-primary',
        'concluido': 'bg-success',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusTextSolicitacao(status) {
    const statusTexts = {
        'pendente': 'Pendente',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações das solicitações
function verDetalhes(id) {
    // Implementar modal ou página de detalhes
    alert(`Ver detalhes da solicitação ${id} - Funcionalidade em desenvolvimento`);
}

function editarSolicitacao(id) {
    // Redirecionar para página de edição
    window.location.href = `./servico/altera-solicitacao.html?id=${id}`;
}

async function excluirSolicitacao(id) {
    if (!confirm('Tem certeza que deseja excluir esta solicitação de serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('solicitacao_id', id);

        const response = await fetch('../php/servico/apaga-solicitacao.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Solicitação excluída com sucesso!');
            carregarSolicitacoes(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir solicitação:', error);
        alert('Erro ao excluir solicitação: ' + error.message);
    }
}

// Funções auxiliares para status dos serviços publicados
function getStatusText(status) {
    const statusTexts = {
        'ativo': 'Ativo',
        'inativo': 'Inativo',
        'pausado': 'Pausado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações dos serviços publicados (visualização pelos clientes)
function verDetalhesServicoPublicado(id) {
    alert(`Ver detalhes do serviço ${id} - Funcionalidade em desenvolvimento`);
}

function contratarServico(id) {
    alert(`Contratar serviço ${id} - Funcionalidade em desenvolvimento`);
}
```

```javascript
document.addEventListener('DOMContentLoaded', function () {
    checkAuthentication();
});

async function checkAuthentication() {
    try {
        const response = await fetch('../php/cliente/cliente-dashboard.php');
        const data = await response.json();

        if (!data.authenticated) {
            // Se não estiver autenticado, redireciona para a página de login
            alert(data.message);
            window.location.href = './login/index.html';
            return;
        }

        // Se estiver autenticado, mostra o conteúdo e atualiza o nome do usuário
        document.getElementById('userName').textContent = data.nome;
        document.getElementById('dashboardContent').style.display = 'block';

        // Carregar a lista de serviços, solicitações e contratos
        carregarServicos();
        carregarSolicitacoes();
        carregarContratos();

    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        alert('Erro ao carregar a página. Por favor, tente novamente.');
        window.location.href = './login/index.html';
    }
}

async function carregarServicos() {
    try {
        const response = await fetch('../php/servico/listar-servico.php');
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (data.servicos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum serviço disponível no momento.
                    </td>
                </tr>
            `;
            return;
        }

        data.servicos.forEach(servico => {
            const row = document.createElement('tr');

            // Formatar data
            const data_formatada = new Date(servico.data_postagem).toLocaleDateString('pt-BR');

            // Definir classe do status
            const statusClass = getStatusClass(servico.status);

            // Formatar orçamento
            const orcamento = servico.orcamento ?
                new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                }).format(servico.orcamento) : 'A combinar';

            row.innerHTML = `
                <td>
                    <strong>${servico.titulo}</strong>
                    <br>
                    <small class="text-muted">Por: ${servico.prestador_nome || 'Prestador'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${servico.categoria}</span>
                </td>
                <td>${orcamento}</td>
                <td><span class="badge ${statusClass}">${getStatusText(servico.status)}</span></td>
                <td>
                    <small>${data_formatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhesServicoPublicado(${servico.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" 
                                onclick="contratarServico(${servico.id})" 
                                title="Contratar">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar serviços:', error);
        const tableBody = document.getElementById('servicosTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar serviços: ${error.message}
                </td>
            </tr>
        `;
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

        const response = await fetch('../php/servico/apaga-servico.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Serviço excluído com sucesso!');
            carregarServicos(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir serviço:', error);
        alert('Erro ao excluir serviço: ' + error.message);
    }
}

function logout() {
    // Limpar dados da sessão no localStorage
    localStorage.clear();

    // Fazer requisição para destruir a sessão no servidor
    fetch('../php/logout.php')
        .then(() => {
            // Redirecionar para a página de login
            window.location.href = './login/index.html';
        })
        .catch(error => {
            console.error('Erro ao fazer logout:', error);
            // Mesmo com erro, redireciona para garantir que o usuário saia
            window.location.href = './login/index.html';
        });
}

// Função para carregar solicitações de serviço
async function carregarSolicitacoes() {
    try {
        const response = await fetch('../php/servico/listar-solicitacoes.php');
        const data = await response.json();

        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.solicitacoes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhuma solicitação de serviço encontrada.
                        <br>
                        <a href="./servico/nova-solicitacao.html" class="btn btn-success btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i> Fazer primeira solicitação
                        </a>
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada solicitação à tabela
        data.solicitacoes.forEach(solicitacao => {
            const row = document.createElement('tr');

            const dataFormatada = new Date(solicitacao.data_criacao).toLocaleDateString('pt-BR');
            const orcamento = solicitacao.orcamento_maximo ?
                `R$ ${parseFloat(solicitacao.orcamento_maximo).toFixed(2)}` :
                'Flexível';

            row.innerHTML = `
                <td>
                    <strong>${solicitacao.titulo}</strong>
                    <br>
                    <small class="text-muted">${solicitacao.cidade}</small>
                </td>
                <td>
                    <span class="badge bg-info">${solicitacao.categoria}</span>
                </td>
                <td>
                    <small>${solicitacao.prazo_desejado}</small>
                </td>
                <td>${orcamento}</td>
                <td>
                    <span class="badge ${getStatusClassSolicitacao(solicitacao.status)}">
                        ${getStatusTextSolicitacao(solicitacao.status)}
                    </span>
                </td>
                <td>
                    <small>${dataFormatada}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="verDetalhes(${solicitacao.id})" 
                                title="Ver detalhes">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                onclick="editarSolicitacao(${solicitacao.id})" 
                                title="Editar">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="excluirSolicitacao(${solicitacao.id})" 
                                title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar solicitações:', error);
        const tableBody = document.getElementById('solicitacoesTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar solicitações: ${error.message}
                </td>
            </tr>
        `;
    }
}

// Funções auxiliares para status das solicitações
function getStatusClassSolicitacao(status) {
    const statusClasses = {
        'pendente': 'bg-warning text-dark',
        'em_andamento': 'bg-primary',
        'concluido': 'bg-success',
        'cancelado': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

function getStatusTextSolicitacao(status) {
    const statusTexts = {
        'pendente': 'Pendente',
        'em_andamento': 'Em Andamento',
        'concluido': 'Concluído',
        'cancelado': 'Cancelado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações das solicitações
function verDetalhes(id) {
    // Implementar modal ou página de detalhes
    alert(`Ver detalhes da solicitação ${id} - Funcionalidade em desenvolvimento`);
}

function editarSolicitacao(id) {
    // Redirecionar para página de edição
    window.location.href = `./servico/altera-solicitacao.html?id=${id}`;
}

async function excluirSolicitacao(id) {
    if (!confirm('Tem certeza que deseja excluir esta solicitação de serviço?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('solicitacao_id', id);

        const response = await fetch('../php/servico/apaga-solicitacao.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            alert('Solicitação excluída com sucesso!');
            carregarSolicitacoes(); // Recarregar a tabela
        } else {
            throw new Error(data.message);
        }

    } catch (error) {
        console.error('Erro ao excluir solicitação:', error);
        alert('Erro ao excluir solicitação: ' + error.message);
    }
}

// Função para carregar contratos do cliente
async function carregarContratos() {
    try {
        const response = await fetch('../php/servico/listar-contratos-cliente.php');
        const data = await response.json();

        const tableBody = document.getElementById('contratosTable');
        tableBody.innerHTML = ''; // Limpar tabela

        if (!data.success || data.contratos.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        <i class="bi bi-inbox"></i> 
                        Nenhum contrato encontrado.
                    </td>
                </tr>
            `;
            return;
        }

        // Adicionar cada contrato à tabela
        data.contratos.forEach(contrato => {
            const row = document.createElement('tr');

            const dataFormatada = new Date(contrato.created_at).toLocaleDateString('pt-BR');
            
            // Definir badge de status
            let statusBadge = '';
            if (contrato.status === 'completed') {
                statusBadge = '<span class="badge bg-success">Concluído</span>';
            } else if (contrato.status === 'active') {
                statusBadge = '<span class="badge bg-primary">Ativo</span>';
            } else if (contrato.status === 'cancelled') {
                statusBadge = '<span class="badge bg-danger">Cancelado</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">Pendente</span>';
            }

            // Gerar coluna de avaliação baseada no status
            let avaliacaoHtml = '';
            if (contrato.status === 'completed' && contrato.ja_avaliado == 0) {
                avaliacaoHtml = `
                    <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
                       class="btn btn-sm btn-warning">
                        <i class="bi bi-star-fill"></i> Avaliar
                    </a>
                `;
            } else if (contrato.ja_avaliado == 1) {
                avaliacaoHtml = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Já avaliado</span>';
            } else {
                avaliacaoHtml = '<span class="badge bg-secondary">Aguardando conclusão</span>';
            }

            row.innerHTML = `
                <td>
                    <strong>${contrato.titulo}</strong>
                </td>
                <td>
                    ${contrato.prestador_nome}
                    <br>
                    <small class="text-muted">${contrato.specialty || 'Sem especialidade'}</small>
                </td>
                <td>
                    <span class="badge bg-info">${contrato.categoria}</span>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <small>${dataFormatada}</small>
                </td>
                <td>${avaliacaoHtml}</td>
            `;

            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Erro ao carregar contratos:', error);
        const tableBody = document.getElementById('contratosTable');
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Erro ao carregar contratos: ${error.message}
                </td>
            </tr>
        `;
    }
}

// Funções auxiliares para status dos serviços publicados
function getStatusText(status) {
    const statusTexts = {
        'ativo': 'Ativo',
        'inativo': 'Inativo',
        'pausado': 'Pausado'
    };
    return statusTexts[status] || 'Desconhecido';
}

// Funções para ações dos serviços publicados (visualização pelos clientes)
function verDetalhesServicoPublicado(id) {
    alert(`Ver detalhes do serviço ${id} - Funcionalidade em desenvolvimento`);
}

function contratarServico(id) {
    alert(`Contratar serviço ${id} - Funcionalidade em desenvolvimento`);
}
```

Excelente! Implementei a funcionalidade completa. Aqui está o resumo do que foi adicionado:

## ✅ Alterações Realizadas

### 1. **HTML (`cliente-dashboard.html`)**
Adicionei uma nova seção "Meus Contratos" entre as solicitações e os serviços disponíveis (linhas 88-118):
- Tabela com colunas: Serviço, Prestador, Categoria, Status, Data e **Avaliação**

### 2. **JavaScript (`cliente-dashboard.js`)**

#### **Linha 24:** Chamada da nova função
```javascript
carregarContratos(); // Adicionada junto com carregarServicos() e carregarSolicitacoes()
```

#### **Linhas 330-418:** Nova função `carregarContratos()`
Esta é a função completa que:
- Busca os contratos do cliente via API
- Para cada contrato, exibe as informações
- **Na coluna "Avaliação" (linhas 370-382)**, aplica a lógica que você perguntou:

```javascript
// Gerar coluna de avaliação baseada no status
let avaliacaoHtml = '';
if (contrato.status === 'completed' && contrato.ja_avaliado == 0) {
    avaliacaoHtml = `
        <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
           class="btn btn-sm btn-warning">
            <i class="bi bi-star-fill"></i> Avaliar
        </a>
    `;
} else if (contrato.ja_avaliado == 1) {
    avaliacaoHtml = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Já avaliado</span>';
} else {
    avaliacaoHtml = '<span class="badge bg-secondary">Aguardando conclusão</span>';
}
```

## 🎯 Como Funciona

1. **Contrato concluído e NÃO avaliado** → Mostra botão amarelo "⭐ Avaliar"
2. **Contrato já avaliado** → Mostra badge verde "✓ Já avaliado"  
3. **Contrato não concluído** → Mostra badge cinza "Aguardando conclusão"

Agora quando o cliente acessar o dashboard, verá a seção de contratos com os botões de avaliação conforme o status! 🚀

```javascript
carregarContratos(); // Adicionada junto com carregarServicos() e carregarSolicitacoes()
```

```javascript
// Gerar coluna de avaliação baseada no status
let avaliacaoHtml = '';
if (contrato.status === 'completed' && contrato.ja_avaliado == 0) {
    avaliacaoHtml = `
        <a href="./servico/avaliar-prestador.html?contract_id=${contrato.contract_id}" 
           class="btn btn-sm btn-warning">
            <i class="bi bi-star-fill"></i> Avaliar
        </a>
    `;
} else if (contrato.ja_avaliado == 1) {
    avaliacaoHtml = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Já avaliado</span>';
} else {
    avaliacaoHtml = '<span class="badge bg-secondary">Aguardando conclusão</span>';
}
```

