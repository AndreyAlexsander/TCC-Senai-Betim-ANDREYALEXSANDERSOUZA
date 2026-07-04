<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();
$busca = trim((string)($_GET['busca'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));

$sql = 'SELECT id, nome, categoria, status, prioridade, responsavel, data_criacao, data_atualizacao FROM produtos WHERE usuario_id = ?';
$params = [$usuarioId];

if ($busca !== '') {
    $sql .= ' AND (nome LIKE ? OR categoria LIKE ? OR responsavel LIKE ?)';
    $like = '%' . $busca . '%';
    array_push($params, $like, $like, $like);
}

if ($status !== '' && valid_status($status)) {
    $sql .= ' AND status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY data_atualizacao DESC, data_criacao DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('Erro funil KAION: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo '[]';
}
?>
