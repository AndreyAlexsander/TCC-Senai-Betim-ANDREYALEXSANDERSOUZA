<?php
declare(strict_types=1);

ini_set('display_errors', '0');
date_default_timezone_set('America/Sao_Paulo');

$host = 'sql210.infinityfree.com';
$dbname = 'if0_42336177_kaion';
$username = 'if0_42336177';
$password = 'projetotcc0410';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erro interno de conexão.';
    exit;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_auth(): int
{
    if (empty($_SESSION['logado']) || empty($_SESSION['usuario_id'])) {
        json_response(['sucesso' => false, 'mensagem' => 'Não autorizado'], 401);
    }
    return (int) $_SESSION['usuario_id'];
}

function post_string(string $key, int $max = 5000): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max, 'UTF-8') : substr($value, 0, $max);
}

function post_decimal(string $key): ?float
{
    $value = str_replace(',', '.', trim((string)($_POST[$key] ?? '')));
    return $value === '' ? null : (float) $value;
}

function valid_status(string $status): bool
{
    return in_array($status, ['ideia', 'desenvolvimento', 'teste', 'aprovacao', 'lancado', 'arquivado'], true);
}
?>
