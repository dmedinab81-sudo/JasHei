<?php
/**
 * Formulario para crear nuevo paciente
 */

require_once dirname(__DIR__) . '/src/config/config.php';
require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/Auth.php';

// Verificar sesión
$auth = new Auth();
if (!$auth->estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$errores = [];
$datos = [];

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once SRC_PATH . '/models/Paciente.php';
    
    $pacienteModel = new Paciente();
    $resultado = $pacienteModel->crear($_POST);
    
    if ($resultado['exito']) {
        $_SESSION['mensaje_exito'] = $resultado['mensaje'];
        header('Location: detalle_paciente.php?id=' . $resultado['id']);
        exit;
    } else {
        $errores = $resultado['errores'] ?? [$resultado['mensaje']];
        $datos = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Paciente - JasHei</title>
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
                    <h2><i class="bi bi-person-plus"></i> Nuevo Paciente</h2>
                    <a href="pacientes.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="bi bi-exclamation-circle"></i> Errores encontrados:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombres" name="nombres" 
                                           value="<?php echo htmlspecialchars($datos['nombres'] ?? ''); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                           value="<?php echo htmlspecialchars($datos['apellidos'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="numero_cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="numero_cedula" name="numero_cedula" 
                                           value="<?php echo htmlspecialchars($datos['numero_cedula'] ?? ''); ?>" 
                                           placeholder="Ej: 1234567890" required>
                                    <small class="form-text text-muted">Ingrese un número de cédula ecuatoriana válido</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                           value="<?php echo htmlspecialchars($datos['fecha_nacimiento'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="sexo" class="form-label">Sexo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="sexo" name="sexo" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Masculino" <?php echo ($datos['sexo'] ?? '') === 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                                        <option value="Femenino" <?php echo ($datos['sexo'] ?? '') === 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                                        <option value="Otro" <?php echo ($datos['sexo'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="lugar_nacimiento" class="form-label">Lugar de Nacimiento</label>
                                    <input type="text" class="form-control" id="lugar_nacimiento" name="lugar_nacimiento" 
                                           value="<?php echo htmlspecialchars($datos['lugar_nacimiento'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($datos['telefono'] ?? ''); ?>" 
                                           placeholder="Ej: 0987654321">
                                    <small class="form-text text-muted">10 dígitos</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" 
                                           value="<?php echo htmlspecialchars($datos['direccion'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="referencia_domiciliaria" class="form-label">Referencia Domiciliaria</label>
                                    <input type="text" class="form-control" id="referencia_domiciliaria" name="referencia_domiciliaria" 
                                           value="<?php echo htmlspecialchars($datos['referencia_domiciliaria'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-custom">
                                        <i class="bi bi-check-circle"></i> Guardar Paciente
                                    </button>
                                    <a href="pacientes.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
