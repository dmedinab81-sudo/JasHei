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
  <title>Sistema Médico | Panel Principal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .navbar {
      background: rgba(0, 0, 0, 0.2) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: #fff !important;
    }
    .welcome-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 40px;
      margin-top: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    .welcome-card h1 {
      color: #333;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .welcome-card .user-info {
      color: #666;
      font-size: 1.1rem;
      margin-bottom: 30px;
    }
    .user-badge {
      display: inline-block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.9rem;
      margin-top: 10px;
    }
    .modules-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }
    .module-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      text-decoration: none;
      color: inherit;
      border: 2px solid transparent;
    }
    .module-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
      border-color: #667eea;
    }
    .module-icon {
      font-size: 3rem;
      margin-bottom: 15px;
      display: block;
    }
    .module-icon.pacientes {
      color: #667eea;
    }
    .module-icon.atenciones {
      color: #764ba2;
    }
    .module-card h3 {
      color: #333;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .module-card p {
      color: #666;
      font-size: 0.95rem;
      margin-bottom: 0;
    }
    .logout-btn {
      margin-top: 30px;
    }
    .footer-text {
      text-align: center;
      color: rgba(255, 255, 255, 0.8);
      margin-top: 40px;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark">
    <div class="container-fluid">
      <span class="navbar-brand">
        <i class="bi bi-hospital"></i> JasHei
      </span>
      <div class="d-flex align-items-center text-white gap-3">
        <span>
          <i class="bi bi-person-circle"></i>
          <?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">
          <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="welcome-card">
      <h1>
        <i class="bi bi-house-door"></i> Bienvenido al Sistema
      </h1>
      <div class="user-info">
        Has iniciado sesión correctamente
      </div>
      <p>
        <strong>Usuario:</strong> <?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?>
      </p>
      <p>
        <span class="user-badge">
          <i class="bi bi-shield-check"></i>
          <?= htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8') ?>
        </span>
      </p>

      <div class="modules-grid">
        <a href="pacientes.php" class="module-card">
          <i class="bi bi-person-lines-fill module-icon pacientes"></i>
          <h3>Pacientes</h3>
          <p>Gestiona el registro y control de pacientes</p>
        </a>

        <a href="atenciones.php" class="module-card">
          <i class="bi bi-clipboard2-pulse module-icon atenciones"></i>
          <h3>Atenciones</h3>
          <p>Registra y consulta atenciones médicas</p>
        </a>
      </div>

      <div class="text-center logout-btn">
        <a href="logout.php" class="btn btn-outline-danger">
          <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
      </div>
    </div>

    <div class="footer-text">
      <p>Sistema de Historias Clínicas JasHei © 2026</p>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
