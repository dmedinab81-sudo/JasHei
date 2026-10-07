<?php
/**
 * Gestión de Pacientes
 * Página principal para listar, buscar y crear pacientes
 */

require_once dirname(__DIR__) . '/config/Config.php';
require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/Auth.php';
require_once SRC_PATH . '/models/Paciente.php';

// Verificar sesión
$auth = new Auth();
if (!$auth->estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$pacienteModel = new Paciente();
$filtros = [
    'nombre' => $_GET['nombre'] ?? '',
    'cedula' => $_GET['cedula'] ?? '',
    'pagina' => $_GET['pagina'] ?? 1
];

$resultado = $pacienteModel->listar($filtros);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pacientes - JasHei</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sidebar {
            background-color: #2c3e50;
            min-height: 100vh;
            padding: 20px 0;
        }
        .sidebar a {
            color: #ecf0f1;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 30px;
        }
        .sidebar a.active {
            background-color: #667eea;
            border-left: 4px solid #fff;
        }
        .content {
            padding: 30px;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            margin-bottom: 20px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f9f9f9;
        }
        .badge-nuevo {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-hospital"></i> JasHei - Sistema de Historias Clínicas
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($auth->obtenerUsuario()['nombre']); ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="mb-4">
                    <h6 class="text-light ps-3">MENÚ PRINCIPAL</h6>
                </div>
                <a href="dashboard.php">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <a href="pacientes.php" class="active">
                    <i class="bi bi-person-lines-fill"></i> Pacientes
                </a>
                <a href="atenciones.php">
                    <i class="bi bi-clipboard2-pulse"></i> Atenciones
                </a>
                <hr class="bg-secondary">
                <a href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-10 content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-person-lines-fill"></i> Gestión de Pacientes</h2>
                    <a href="nuevo_paciente.php" class="btn btn-custom">
                        <i class="bi bi-plus-circle"></i> Nuevo Paciente
                    </a>
                </div>

                <!-- Filtros -->
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="nombre" class="form-label">Nombre o Apellido</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($filtros['nombre']); ?>" 
                                       placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-4">
                                <label for="cedula" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula" 
                                       value="<?php echo htmlspecialchars($filtros['cedula']); ?>" 
                                       placeholder="Ej: 1234567890">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-custom w-100">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de pacientes -->
                <?php if (!empty($resultado['pacientes'])): ?>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Pacientes encontrados (<?php echo $resultado['total']; ?>)</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Edad</th>
                                        <th>Teléfono</th>
                                        <th>Sexo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultado['pacientes'] as $paciente): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($paciente['numero_cedula']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></td>
                                            <td>
                                                <?php 
                                                    $edad = $paciente['edad'];
                                                    echo $edad['anios'] . ' años, ' . $edad['meses'] . ' meses';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($paciente['telefono'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($paciente['sexo']); ?></td>
                                            <td>
                                                <a href="detalle_paciente.php?id=<?php echo $paciente['id']; ?>" 
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <a href="editar_paciente.php?id=<?php echo $paciente['id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <?php if ($resultado['total_paginas'] > 1): ?>
                        <nav aria-label="Paginación">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $resultado['total_paginas']; $i++): ?>
                                    <li class="page-item <?php echo ($i === (int)$filtros['pagina']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?nombre=<?php echo urlencode($filtros['nombre']); ?>&cedula=<?php echo urlencode($filtros['cedula']); ?>&pagina=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> No se encontraron pacientes. 
                        <a href="nuevo_paciente.php" class="alert-link">Crea uno nuevo</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
