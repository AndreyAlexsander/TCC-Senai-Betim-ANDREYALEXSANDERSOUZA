<?php
declare(strict_types=1);
require_once 'config.php';

json_response([
    'logado' => !empty($_SESSION['logado']),
    'nome' => $_SESSION['usuario_nome'] ?? '',
]);
?>
