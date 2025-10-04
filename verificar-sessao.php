<?php
session_start();

echo "=== VERIFICANDO SESSÃO ATIVA ===\n\n";

if (empty($_SESSION)) {
    echo "❌ Nenhuma sessão ativa encontrada!\n";
    echo "🔗 Faça login primeiro em: http://localhost/php-servicofacil-crud/client/login/index.html\n\n";
} else {
    echo "✅ Sessão ativa encontrada!\n\n";
    echo "📋 Dados da sessão:\n";
    foreach ($_SESSION as $key => $value) {
        echo "- $key: $value\n";
    }
    
    echo "\n🔍 Verificações específicas:\n";
    
    // Verificar usuario_id
    if (isset($_SESSION['usuario_id'])) {
        echo "✅ usuario_id: {$_SESSION['usuario_id']}\n";
    } else {
        echo "❌ usuario_id não encontrado\n";
    }
    
    // Verificar usuario_tipo
    if (isset($_SESSION['usuario_tipo'])) {
        echo "✅ usuario_tipo: {$_SESSION['usuario_tipo']}\n";
    } else {
        echo "❌ usuario_tipo não encontrado\n";
    }
    
    // Verificar se é cliente
    if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'cliente') {
        echo "✅ Usuário é um cliente válido\n";
    } else {
        echo "❌ Usuário não é um cliente válido\n";
    }
}

echo "\n🧪 Simulando cadastro de solicitação...\n";
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_tipo'] === 'cliente') {
    echo "✅ Pré-requisitos atendidos para cadastrar solicitação\n";
} else {
    echo "❌ Pré-requisitos NÃO atendidos\n";
    echo "💡 Solução: Faça login como cliente primeiro\n";
}
?>