<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();

try {
    $consulta = $pdo->prepare(
        'SELECT p.*, r.versao AS versao_receita, r.ingredientes, r.modo_preparo, r.observacoes_teste
         FROM produtos p
         LEFT JOIN receitas r ON r.id = (
            SELECT id FROM receitas WHERE produto_id = p.id ORDER BY data_criacao DESC LIMIT 1
         )
         WHERE p.usuario_id = ?
         ORDER BY p.data_atualizacao DESC, p.data_criacao DESC'
    );
    $consulta->execute([$usuarioId]);
    $produtos = $consulta->fetchAll();

    $resumo = ['total' => count($produtos), 'ideia' => 0, 'desenvolvimento' => 0, 'teste' => 0, 'aprovacao' => 0, 'lancado' => 0, 'arquivado' => 0, 'potencial' => 0];

    foreach ($produtos as $produto) {
        if (isset($resumo[$produto['status']])) {
            $resumo[$produto['status']]++;
        }

        $resumo['potencial'] += (float)($produto['potencial_receita'] ?? 0);
    }

    json_response(['produtos' => $produtos, 'stats' => $resumo]);
} catch (Throwable $e) {
    error_log('Erro listar produtos KAION: ' . $e->getMessage());
    json_response(['produtos' => [], 'stats' => ['total' => 0], 'mensagem' => 'Não deu para carregar os produtos.'], 500);
}
?>
