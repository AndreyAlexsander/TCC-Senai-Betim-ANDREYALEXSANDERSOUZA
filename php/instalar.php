<?php
declare(strict_types=1);
require_once 'config.php';

$token = 'kaion-instalar-2026';
if (($_GET['token'] ?? '') !== $token) {
    http_response_code(403);
    echo 'Token invalido. Use php/instalar.php?token=kaion-instalar-2026';
    exit;
}

function has_column(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function add_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!has_column($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
    }
}

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(140) NOT NULL,
        email VARCHAR(180) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        data_cadastro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nome VARCHAR(180) NOT NULL,
        categoria VARCHAR(120) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'ideia',
        responsavel VARCHAR(120) NULL,
        descricao TEXT NULL,
        data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_produtos_usuario_status (usuario_id, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

add_column($pdo, 'produtos', 'prioridade', "ENUM('baixa','media','alta') NOT NULL DEFAULT 'media'");
add_column($pdo, 'produtos', 'mercado_alvo', 'VARCHAR(180) NULL');
add_column($pdo, 'produtos', 'custo_estimado', 'DECIMAL(12,2) NULL');
add_column($pdo, 'produtos', 'potencial_receita', 'DECIMAL(12,2) NULL');
add_column($pdo, 'produtos', 'data_lancamento_prevista', 'DATE NULL');
add_column($pdo, 'produtos', 'risco', "ENUM('baixo','medio','alto') NOT NULL DEFAULT 'baixo'");
add_column($pdo, 'produtos', 'resultado_teste', "ENUM('nao_testado','aprovado','ajustar','reprovado') NOT NULL DEFAULT 'nao_testado'");
add_column($pdo, 'produtos', 'data_atualizacao', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

$pdo->exec("ALTER TABLE produtos MODIFY status ENUM('ideia','desenvolvimento','teste','aprovacao','lancado','arquivado') NOT NULL DEFAULT 'ideia'");

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS receitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        produto_id INT NOT NULL,
        usuario_id INT NOT NULL,
        versao VARCHAR(30) NOT NULL DEFAULT 'v1',
        ingredientes TEXT NULL,
        modo_preparo TEXT NULL,
        observacoes_teste TEXT NULL,
        resultado_teste ENUM('nao_testado','aprovado','ajustar','reprovado') NOT NULL DEFAULT 'nao_testado',
        data_criacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_receitas_produto (produto_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$emailEmpresa = 'empresa@kaion.local';
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$emailEmpresa]);
$usuarioEmpresaId = (int)($stmt->fetchColumn() ?: 0);

if ($usuarioEmpresaId === 0) {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())');
    $stmt->execute(['KAION Salgados e Bebidas', $emailEmpresa, password_hash('kaion123', PASSWORD_DEFAULT)]);
    $usuarioEmpresaId = (int)$pdo->lastInsertId();
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM produtos WHERE usuario_id = ?');
$stmt->execute([$usuarioEmpresaId]);

if ((int)$stmt->fetchColumn() === 0) {
    $produtosExemplo = [
        ['Coxinha de frango', 'Salgado frito', 'teste', 'alta', 'Andrey', 'Cantina escolar', 2.50, 1200.00, 'aprovado'],
        ['Empada de palmito', 'Salgado assado', 'desenvolvimento', 'media', 'Nicolly', 'Encomendas para festas', 3.10, 900.00, 'ajustar'],
        ['Pastel assado de carne', 'Salgado assado', 'ideia', 'media', 'Talyssa', 'Eventos corporativos', 2.80, 1100.00, 'nao_testado'],
        ['Enroladinho de salsicha', 'Salgado assado', 'aprovacao', 'alta', 'Matheus', 'Venda no balcao', 2.20, 1000.00, 'aprovado'],
        ['Kibe recheado', 'Salgado frito', 'lancado', 'alta', 'Pedro', 'Eventos corporativos', 3.40, 1500.00, 'aprovado'],
        ['Suco natural de laranja', 'Bebida natural', 'teste', 'media', 'Nicolly', 'Cantina escolar', 2.00, 800.00, 'ajustar'],
        ['Refrigerante lata', 'Bebida industrializada', 'lancado', 'baixa', 'Andrey', 'Venda no balcao', 3.00, 700.00, 'aprovado'],
    ];

    $stmtProduto = $pdo->prepare(
        'INSERT INTO produtos
        (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, risco, resultado_teste, data_criacao, data_atualizacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
    );
    $stmtReceita = $pdo->prepare(
        'INSERT INTO receitas (produto_id, usuario_id, versao, ingredientes, modo_preparo, observacoes_teste, resultado_teste, data_criacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );

    foreach ($produtosExemplo as $produto) {
        [$nome, $categoria, $status, $prioridade, $responsavel, $mercado, $custo, $potencial, $resultado] = $produto;
        $stmtProduto->execute([
            $usuarioEmpresaId,
            $nome,
            $categoria,
            $status,
            $prioridade,
            $responsavel,
            'Produto de exemplo para gestao interna da empresa.',
            $mercado,
            $custo,
            $potencial,
            'baixo',
            $resultado,
        ]);

        $stmtReceita->execute([
            (int)$pdo->lastInsertId(),
            $usuarioEmpresaId,
            'v1',
            'Ingredientes definidos pela producao.',
            'Preparar, testar qualidade e registrar observacoes.',
            'Exemplo usado para apresentacao do TCC.',
            $resultado,
        ]);
    }
}

echo 'KAION instalado/atualizado com sucesso. Apague ou renomeie php/instalar.php depois da instalacao.';
?>
