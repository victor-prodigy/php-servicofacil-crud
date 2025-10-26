-- Tabela de pagamentos
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servico_id INT NOT NULL,
    cliente_id INT NOT NULL,
    prestador_id INT NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    forma_pagamento ENUM('cartao', 'pix', 'transferencia') NOT NULL,
    status ENUM('pendente', 'aprovado', 'aguardando_pix', 'aguardando_confirmacao', 'recusado', 'estornado') NOT NULL,
    data_pagamento DATETIME NOT NULL,
    comprovante VARCHAR(255) NULL,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (prestador_id) REFERENCES prestadores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de notificações
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('pagamento', 'servico', 'sistema') NOT NULL,
    mensagem TEXT NOT NULL,
    data_criacao DATETIME NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    INDEX (usuario_id, lida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;