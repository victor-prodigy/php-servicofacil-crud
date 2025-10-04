<?php
session_start();

echo "=== TESTANDO SERVICO-LISTAR ATUALIZADO ===\n\n";

// Teste 1: Cliente visualizando serviços
echo "🧪 Teste 1 - Cliente visualizando serviços:\n";
$_SESSION['usuario_id'] = 2;
$_SESSION['usuario_tipo'] = 'cliente';

ob_start();
include 'php/servico/servico-listar.php';
$output1 = ob_get_clean();
echo "📤 Resposta: " . $output1 . "\n\n";

// Reset session
session_destroy();
session_start();

// Teste 2: Prestador gerenciando seus serviços
echo "🧪 Teste 2 - Prestador gerenciando serviços:\n";

// Primeiro, encontrar o user_id do prestador
require_once 'php/conexao.php';
$result = $conn->query("SELECT user_id FROM user WHERE email = 'prestador@exemplo.com'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $prestador_user_id = $row['user_id'];
    
    $_SESSION['usuario_id'] = $prestador_user_id;
    $_SESSION['usuario_tipo'] = 'prestador';
    
    ob_start();
    include 'php/servico/servico-listar.php';
    $output2 = ob_get_clean();
    echo "📤 Resposta: " . $output2 . "\n\n";
} else {
    echo "❌ Prestador não encontrado\n\n";
}

echo "✅ Testes concluídos!\n";
?>