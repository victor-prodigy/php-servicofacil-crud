<?php
/**
 * Script para executar o arquivo seed.sql e popular o banco de dados
 * Este script deve ser executado após o setup-database.php
 */

// Configurações de conexão com o banco de dados
require_once __DIR__ . '/../config/database.php';

// Função para executar o arquivo SQL
function executeSeedSQL($conn) {
    try {
        // Lê o conteúdo do arquivo seed.sql
        $seedSQL = file_get_contents(__DIR__ . '/../lib/seed.sql');
        
        // Divide o conteúdo em comandos SQL individuais
        $commands = explode(';', $seedSQL);
        
        // Contador de comandos executados com sucesso
        $successCount = 0;
        
        // Inicia uma transação
        $conn->beginTransaction();
        
        // Executa cada comando SQL
        foreach ($commands as $command) {
            $command = trim($command);
            if (!empty($command)) {
                $conn->exec($command);
                $successCount++;
            }
        }
        
        // Confirma a transação
        $conn->commit();
        
        return [
            'success' => true,
            'message' => "Dados de exemplo inseridos com sucesso! ($successCount comandos executados)",
            'commands_executed' => $successCount
        ];
    } catch (PDOException $e) {
        // Em caso de erro, reverte a transação
        $conn->rollBack();
        
        return [
            'success' => false,
            'message' => "Erro ao inserir dados de exemplo: " . $e->getMessage(),
            'error' => $e->getMessage()
        ];
    }
}

// Tenta conectar ao banco de dados
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Executa o arquivo seed.sql
    $result = executeSeedSQL($conn);
    
    // Exibe o resultado
    if ($result['success']) {
        echo "<h2>✅ " . $result['message'] . "</h2>";
        
        // Consulta as tabelas para mostrar os dados inseridos
        $tables = ['user', 'cliente', 'service_provider', 'service_request', 'proposal', 'contract', 'payment', 'review', 'chat'];
        
        echo "<h3>Resumo dos dados inseridos:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Tabela</th><th>Registros</th></tr>";
        
        foreach ($tables as $table) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "<tr><td>$table</td><td>$count</td></tr>";
        }
        
        echo "</table>";
    } else {
        echo "<h2>❌ " . $result['message'] . "</h2>";
        echo "<pre>" . $result['error'] . "</pre>";
    }
} catch (PDOException $e) {
    echo "<h2>❌ Erro de conexão com o banco de dados</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>