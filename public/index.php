<?php

declare(strict_types=1);

session_start();

$config = require __DIR__ . '/../config/database.php';
require __DIR__ . '/../src/Auth.php';

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['host'], $config['port'], $config['database'], $config['charset']);
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$auth = new Auth($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Debes ingresar correo y contraseña.';
    } elseif (!$auth->login($email, $password)) {
        $error = 'Credenciales inválidas o usuario inactivo.';
    } else {
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema Médico | Inicio de sesión</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f5f9; margin: 0; }
    .wrap { max-width: 420px; margin: 80px auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    h1 { margin-top: 0; font-size: 22px; }
    label { display: block; margin-top: 12px; font-weight: bold; }
    input { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
    button { margin-top: 16px; width: 100%; padding: 12px; border: 0; border-radius: 8px; background: #0b5ed7; color: #fff; font-weight: bold; cursor: pointer; }
    .error { color: #b00020; margin-top: 12px; }
    .hint { color: #555; font-size: 14px; margin-top: 12px; }
  </style>
</head>
<body>
  <main class="wrap">
    <h1>Ingreso al Sistema Médico</h1>
    <form method="post" action="/index.php">
      <label for="email">Correo</label>
      <input id="email" type="email" name="email" required>

      <label for="password">Contraseña</label>
      <input id="password" type="password" name="password" required>

      <button type="submit">Iniciar sesión</button>
    </form>

    <?php if ($error !== ''): ?>
      <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <p class="hint">Usuario inicial: <strong>admin@jashei.local</strong> / <strong>Admin123*</strong></p>
  </main>
</body>
</html>
