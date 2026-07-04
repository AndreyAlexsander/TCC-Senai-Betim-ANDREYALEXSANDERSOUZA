<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();
$dataInicio = trim((string)($_GET['dataInicio'] ?? ''));
$dataFim = trim((string)($_GET['dataFim'] ?? ''));
$status = trim((string)($_GET['status'] ?? 'todos'));

$sql = 'SELECT id, nome, categoria, status, prioridade, responsavel, potencial_receita, data_criacao, data_atualizacao FROM produtos WHERE usuario_id = ?';
$params = [$usuarioId];

if ($dataInicio !== '' && $dataFim !== '') {
    $sql .= ' AND DATE(data_criacao) BETWEEN ? AND ?';
    array_push($params, $dataInicio, $dataFim);
}

if ($status !== '' && $status !== 'todos' && valid_status($status)) {
    $sql .= ' AND status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY data_atualizacao DESC, data_criacao DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
    error_log('Erro relatorios KAION: ' . $e->getMessage());
    json_response(['produtos' => [], 'stats' => ['total' => 0], 'mensagem' => 'Erro ao gerar relatorio.'], 500);
}
?>
