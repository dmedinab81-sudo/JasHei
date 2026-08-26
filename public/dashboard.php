<?php

declare(strict_types=1);

session_start();
require __DIR__ . '/../src/Auth.php';

Auth::requireLogin();
$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema Médico | Panel</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f5f9; margin: 0; }
    .wrap { max-width: 720px; margin: 60px auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .tag { display: inline-block; background: #e6f0ff; color: #0b5ed7; border-radius: 12px; padding: 4px 10px; font-size: 13px; }
    a { color: #0b5ed7; }
  </style>
</head>
<body>
  <main class="wrap">
    <h1>Bienvenido, <?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Has iniciado sesión correctamente en el módulo administrativo.</p>
    <p>Correo: <strong><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></strong></p>
    <p>Rol: <span class="tag"><?= htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8') ?></span></p>
    <p><a href="/logout.php">Cerrar sesión</a></p>
  </main>
</body>
</html>
