<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['sucesso' => false, 'mensagem' => 'Método inválido'], 405);
}

$nome = post_string('nome', 180);
$categoria = post_string('categoria', 120);
$status = post_string('status', 30);
$responsavel = post_string('responsavel', 120);
$mercadoAlvo = post_string('mercado_alvo', 180);

if (
    !valid_fixed_value($nome, PRODUTOS_FIXOS) ||
    !valid_fixed_value($categoria, CATEGORIAS_FIXAS) ||
    !valid_fixed_value($responsavel, RESPONSAVEIS_FIXOS, true) ||
    !valid_fixed_value($mercadoAlvo, MERCADOS_FIXOS, true) ||
    !valid_status($status)
) {
    json_response(['sucesso' => false, 'mensagem' => 'Selecione valores validos para produto, categoria e responsavel.'], 422);
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'INSERT INTO produtos
        (usuario_id, nome, categoria, status, prioridade, responsavel, descricao, mercado_alvo, custo_estimado, potencial_receita, data_lancamento_prevista, risco, resultado_teste, data_criacao, data_atualizacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
    );
    $stmt->execute([
        $usuarioId,
        $nome,
        $categoria,
        $status,
        post_string('prioridade', 20) ?: 'media',
        $responsavel ?: null,
        post_string('descricao') ?: null,
        $mercadoAlvo ?: null,
        post_decimal('custo_estimado'),
        post_decimal('potencial_receita'),
        post_string('data_lancamento_prevista', 20) ?: null,
        post_string('risco', 20) ?: 'baixo',
        post_string('resultado_teste', 30) ?: 'nao_testado',
    ]);

    $produtoId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare(
        'INSERT INTO receitas
        (produto_id, usuario_id, versao, ingredientes, modo_preparo, observacoes_teste, resultado_teste, data_criacao)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $produtoId,
        $usuarioId,
        post_string('versao_receita', 30) ?: 'v1',
        post_string('ingredientes') ?: null,
        post_string('modo_preparo') ?: null,
        post_string('observacoes_teste') ?: null,
        post_string('resultado_teste', 30) ?: 'nao_testado',
    ]);

    $pdo->commit();
    json_response(['sucesso' => true, 'id' => $produtoId]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro cadastro produto KAION: ' . $e->getMessage());
    json_response(['sucesso' => false, 'mensagem' => 'Erro ao salvar produto.'], 500);
}
?>
