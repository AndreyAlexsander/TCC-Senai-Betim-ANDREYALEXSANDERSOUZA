<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();

try {
    $stmt = $pdo->prepare(
        'SELECT p.*, r.versao AS versao_receita, r.ingredientes, r.modo_preparo, r.observacoes_teste
         FROM produtos p
         LEFT JOIN receitas r ON r.id = (
            SELECT id FROM receitas WHERE produto_id = p.id ORDER BY data_criacao DESC LIMIT 1
         )
         WHERE p.usuario_id = ?
         ORDER BY p.data_atualizacao DESC, p.data_criacao DESC'
    );
    $stmt->execute([$usuarioId]);
    $produtos = $stmt->fetchAll();

    $stats = ['total' => count($produtos), 'ideia' => 0, 'desenvolvimento' => 0, 'teste' => 0, 'aprovacao' => 0, 'lancado' => 0, 'arquivado' => 0, 'potencial' => 0];
    foreach ($produtos as $produto) {
        if (isset($stats[$produto['status']])) {
            $stats[$produto['status']]++;
        }
        $stats['potencial'] += (float)($produto['potencial_receita'] ?? 0);
    }

    json_response(['produtos' => $produtos, 'stats' => $stats]);
} catch (Throwable $e) {
    error_log('Erro listar produtos KAION: ' . $e->getMessage());
    json_response(['produtos' => [], 'stats' => ['total' => 0], 'mensagem' => 'Erro ao carregar produtos.'], 500);
}
?>
