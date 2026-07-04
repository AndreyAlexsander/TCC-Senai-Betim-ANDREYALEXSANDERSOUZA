<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();
$id = (int)($_POST['id'] ?? 0);
$status = post_string('status', 30);

if ($id <= 0 || !valid_status($status)) {
    json_response(['sucesso' => false, 'mensagem' => 'Dados inválidos.'], 422);
}

try {
    $stmt = $pdo->prepare('UPDATE produtos SET status = ?, data_atualizacao = NOW() WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$status, $id, $usuarioId]);
    json_response(['sucesso' => $stmt->rowCount() > 0]);
} catch (Throwable $e) {
    error_log('Erro status KAION: ' . $e->getMessage());
    json_response(['sucesso' => false, 'mensagem' => 'Erro ao atualizar status.'], 500);
}
?>
