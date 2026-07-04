<?php
declare(strict_types=1);
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cadastro.html');
    exit;
}

$nome = trim((string)($_POST['nome'] ?? ''));
$email = filter_var(strtolower(trim((string)($_POST['email'] ?? ''))), FILTER_SANITIZE_EMAIL);
$senha = (string)($_POST['senha'] ?? '');
$confirmar = (string)($_POST['confirmar'] ?? '');

if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../cadastro.html?error=email_invalido');
    exit;
}

if (strlen($senha) < 6) {
    header('Location: ../cadastro.html?error=senha_fraca');
    exit;
}

if ($senha !== $confirmar) {
    header('Location: ../cadastro.html?error=senhas_diferentes');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE LOWER(email) = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../cadastro.html?error=email_existe');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, data_cadastro) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
    header('Location: ../index.html?success=cadastro');
    exit;
} catch (Throwable $e) {
    error_log('Erro cadastro KAION: ' . $e->getMessage());
    header('Location: ../cadastro.html?error=servidor');
    exit;
}
?>
