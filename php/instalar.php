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

echo 'KAION instalado/atualizado com sucesso. Apague ou renomeie php/instalar.php depois da instalacao.';
?>
