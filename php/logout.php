<?php
session_start();
// Destruir todas as variáveis de sessão
session_unset();
// Destruir a sessão
session_destroy();

// Retornar resposta de sucesso
echo json_encode(['success' => true]);
