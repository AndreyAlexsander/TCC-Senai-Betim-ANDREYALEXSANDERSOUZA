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

INSERT INTO usuarios (nome, email, senha)
VALUES ('KAION Salgados e Bebidas', 'empresa@kaion.local', 'kaion123')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

SET @usuario_empresa_id = (SELECT id FROM usuarios WHERE email = 'empresa@kaion.local' LIMIT 1);

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Coxinha de frango', 'Salgado frito', 'teste', 'alta', 'Andrey', 'Produto de exemplo para gestao interna da empresa.', 'Cantina escolar', 2.50, 1200.00, 'baixo', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Coxinha de frango');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Empada de palmito', 'Salgado assado', 'desenvolvimento', 'media', 'Nicolly', 'Produto de exemplo para gestao interna da empresa.', 'Encomendas para festas', 3.10, 900.00, 'baixo', 'ajustar'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Empada de palmito');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Pastel assado de carne', 'Salgado assado', 'ideia', 'media', 'Talyssa', 'Produto de exemplo para gestao interna da empresa.', 'Eventos corporativos', 2.80, 1100.00, 'baixo', 'nao_testado'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Pastel assado de carne');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Enroladinho de salsicha', 'Salgado assado', 'aprovacao', 'alta', 'Matheus', 'Produto de exemplo para gestao interna da empresa.', 'Venda no balcao', 2.20, 1000.00, 'baixo', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Enroladinho de salsicha');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Kibe recheado', 'Salgado frito', 'lancado', 'alta', 'Pedro', 'Produto de exemplo para gestao interna da empresa.', 'Eventos corporativos', 3.40, 1500.00, 'baixo', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Kibe recheado');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Suco natural de laranja', 'Bebida natural', 'teste', 'media', 'Nicolly', 'Produto de exemplo para gestao interna da empresa.', 'Cantina escolar', 2.00, 800.00, 'baixo', 'ajustar'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Suco natural de laranja');

INSERT INTO produtos (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste)
SELECT @usuario_empresa_id, 'Refrigerante lata', 'Bebida industrializada', 'lancado', 'baixa', 'Andrey', 'Produto de exemplo para gestao interna da empresa.', 'Venda no balcao', 3.00, 700.00, 'baixo', 'aprovado'
WHERE NOT EXISTS (SELECT 1 FROM produtos WHERE usuario_id = @usuario_empresa_id AND nome = 'Refrigerante lata');
