<?php
declare(strict_types=1);
require_once 'config.php';

$usuarioId = require_auth();
$dataInicio = trim((string)($_GET['dataInicio'] ?? ''));
$dataFim = trim((string)($_GET['dataFim'] ?? ''));
$status = trim((string)($_GET['status'] ?? 'todos'));

$sql = 'SELECT nome, categoria, status, prioridade, responsavel, mercado_alvo, custo_estimado, potencial_receita, resultado_teste, data_criacao FROM produtos WHERE usuario_id = ?';
$params = [$usuarioId];

if ($dataInicio !== '' && $dataFim !== '') {
    $sql .= ' AND DATE(data_criacao) BETWEEN ? AND ?';
    array_push($params, $dataInicio, $dataFim);
}

if ($status !== '' && $status !== 'todos' && valid_status($status)) {
    $sql .= ' AND status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY data_criacao DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio-kaion.xls');
echo "\xEF\xBB\xBF";
echo "<table border='1'>";
echo '<tr><th>Produto</th><th>Categoria</th><th>Status</th><th>Prioridade</th><th>Responsável</th><th>Mercado alvo</th><th>Custo estimado</th><th>Potencial receita</th><th>Resultado teste</th><th>Data</th></tr>';

foreach ($stmt->fetchAll() as $produto) {
    echo '<tr>';
    foreach (['nome', 'categoria', 'status', 'prioridade', 'responsavel', 'mercado_alvo', 'custo_estimado', 'potencial_receita', 'resultado_teste'] as $campo) {
        $valor = (string)($produto[$campo] ?? '');
        if (preg_match('/^[=+\-@]/', $valor)) {
            $valor = "'" . $valor;
        }
        echo '<td>' . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . '</td>';
    }
    echo '<td>' . date('d/m/Y', strtotime((string)$produto['data_criacao'])) . '</td>';
    echo '</tr>';
}

echo '</table>';
?>
