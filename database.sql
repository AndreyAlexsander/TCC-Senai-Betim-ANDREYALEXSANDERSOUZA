CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(140) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(180) NOT NULL,
    categoria VARCHAR(120) NOT NULL,
    status ENUM('ideia','desenvolvimento','teste','aprovacao','lancado','arquivado') NOT NULL DEFAULT 'ideia',
    prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',
    responsavel VARCHAR(120) NULL,
    descricao TEXT NULL,
    mercado_alvo VARCHAR(180) NULL,
    custo_estimado DECIMAL(12,2) NULL,
    potencial_receita DECIMAL(12,2) NULL,
    data_lancamento_prevista DATE NULL,
    risco ENUM('baixo','medio','alto') NOT NULL DEFAULT 'baixo',
    resultado_teste ENUM('nao_testado','aprovado','ajustar','reprovado') NOT NULL DEFAULT 'nao_testado',
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_produtos_usuario_status (usuario_id, status),
    INDEX idx_produtos_datas (data_criacao, data_atualizacao),
    CONSTRAINT fk_produtos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    versao VARCHAR(30) NOT NULL DEFAULT 'v1',
    ingredientes TEXT NULL,
    modo_preparo TEXT NULL,
    observacoes_teste TEXT NULL,
    resultado_teste ENUM('nao_testado','aprovado','ajustar','reprovado') NOT NULL DEFAULT 'nao_testado',
    data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_receitas_produto (produto_id),
    CONSTRAINT fk_receitas_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    CONSTRAINT fk_receitas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
