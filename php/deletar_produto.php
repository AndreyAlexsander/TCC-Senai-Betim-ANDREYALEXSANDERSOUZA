<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    json_response(['sucesso' => false, 'mensagem' => 'Produto inválido.'], 422);
}

try {
    $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$id, $usuarioId]);
    json_response(['sucesso' => true]);
} catch (Throwable $e) {
    error_log('Erro deletar KAION: ' . $e->getMessage());
    json_response(['sucesso' => false, 'mensagem' => 'Erro ao excluir produto.'], 500);
}
?>
