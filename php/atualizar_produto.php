<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['sucesso' => false, 'mensagem' => 'Esta página só aceita atualização pelo formulário.'], 405);
}

$id = (int)($_POST['id'] ?? 0);
$nome = post_string('nome', 180);
$categoria = post_string('categoria', 120);
$status = post_string('status', 30);
$responsavel = post_string('responsavel', 120);
$mercadoAlvo = post_string('mercado_alvo', 180);

if (
    $id <= 0 ||
    !valid_fixed_value($nome, PRODUTOS_FIXOS) ||
    !valid_fixed_value($categoria, CATEGORIAS_FIXAS) ||
    !valid_fixed_value($responsavel, RESPONSAVEIS_FIXOS, true) ||
    !valid_fixed_value($mercadoAlvo, MERCADOS_FIXOS, true) ||
    !valid_status($status)
) {
    json_response(['sucesso' => false, 'mensagem' => 'Confira os dados do produto antes de salvar.'], 422);
}

try {
    $buscaProduto = $pdo->prepare('SELECT id FROM produtos WHERE id = ? AND usuario_id = ? LIMIT 1');
    $buscaProduto->execute([$id, $usuarioId]);

    if (!$buscaProduto->fetch()) {
        json_response(['sucesso' => false, 'mensagem' => 'Produto não encontrado.'], 404);
    }

    $pdo->beginTransaction();

    $atualizarProduto = $pdo->prepare(
        'UPDATE produtos SET nome=?, categoria=?, status=?, prioridade=?, responsavel=?, descricao=?,
         mercado_alvo=?, custo_estimado=?, potencial_receita=?, data_lancamento_prevista=?,
         risco=?, resultado_teste=?, data_atualizacao=NOW()
         WHERE id=? AND usuario_id=?'
    );
    $atualizarProduto->execute([
        $nome, $categoria, $status,
        post_string('prioridade', 20) ?: 'media',
        $responsavel ?: null,
        post_string('descricao') ?: null,
        $mercadoAlvo ?: null,
        post_decimal('custo_estimado'),
        post_decimal('potencial_receita'),
        post_string('data_lancamento_prevista', 20) ?: null,
        post_string('risco', 20) ?: 'baixo',
        post_string('resultado_teste', 30) ?: 'nao_testado',
        $id, $usuarioId,
    ]);

    $novaReceita = $pdo->prepare(
        'INSERT INTO receitas (produto_id, usuario_id, versao, ingredientes, modo_preparo,
         observacoes_teste, resultado_teste, data_criacao) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $novaReceita->execute([
        $id, $usuarioId,
        post_string('versao_receita', 30) ?: 'v1',
        post_string('ingredientes') ?: null,
        post_string('modo_preparo') ?: null,
        post_string('observacoes_teste') ?: null,
        post_string('resultado_teste', 30) ?: 'nao_testado',
    ]);

    $pdo->commit();
    json_response(['sucesso' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erro atualizar produto KAION: ' . $e->getMessage());
    json_response(['sucesso' => false, 'mensagem' => 'Não deu para atualizar o produto agora.'], 500);
}
?>
