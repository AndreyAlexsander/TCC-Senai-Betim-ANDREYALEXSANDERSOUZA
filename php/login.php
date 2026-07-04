<?php
declare(strict_types=1);
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.html');
    exit;
}

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$senha = (string)($_POST['senha'] ?? '');

try {
    $stmt = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE LOWER(email) = ? LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: ../dashboard.html');
        exit;
    }

    header('Location: ../index.html?error=credenciais');
    exit;
} catch (Throwable $e) {
    error_log('Erro login KAION: ' . $e->getMessage());
    header('Location: ../index.html?error=servidor');
    exit;
}
?>
