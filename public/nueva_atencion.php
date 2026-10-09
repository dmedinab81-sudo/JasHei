<?php
/**
 * Formulario para crear nueva atención
 * Primero busca el paciente por cédula
 */

require_once dirname(__DIR__) . '/src/config/config.php';
require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/Auth.php';
require_once SRC_PATH . '/models/Paciente.php';
require_once SRC_PATH . '/utils/Validador.php';

// Verificar sesión
$auth = new Auth();
if (!$auth->estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$pacienteModel = new Paciente();
$paciente_encontrado = null;
$error_busqueda = '';
$cedula_buscada = '';

// Paso 1: Buscar paciente por cédula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'buscar_paciente') {
    $cedula_buscada = trim($_POST['cedula_paciente'] ?? '');
    
    if (empty($cedula_buscada)) {
        $error_busqueda = 'Por favor ingrese una cédula';
    } elseif (!Validador::validarCedulaEcuador($cedula_buscada)) {
        $error_busqueda = 'El número de cédula no es válido';
    } else {
        $paciente_encontrado = $pacienteModel->obtenerPorCedula($cedula_buscada);
        
        if (!$paciente_encontrado) {
            $error_busqueda = 'No se encontró paciente con esta cédula. Por favor registre primero el paciente en la sección de Pacientes.';
        }
    }
}

// Paso 2: Crear atención
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_atencion') {
    require_once SRC_PATH . '/models/Atencion.php';
    
    $atencionModel = new Atencion();
    $resultado = $atencionModel->crear($_POST['paciente_id'], $_POST);
    
    if ($resultado['exito']) {
        $_SESSION['mensaje_exito'] = $resultado['mensaje'];
        header('Location: detalle_atencion.php?id=' . $resultado['id']);
        exit;
    } else {
        $error_busqueda = $resultado['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Atención - JasHei</title>
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
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .paciente-info {
            background-color: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
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
                <a href="pacientes.php">
                    <i class="bi bi-person-lines-fill"></i> Pacientes
                </a>
                <a href="atenciones.php" class="active">
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
                    <h2><i class="bi bi-plus-circle"></i> Nueva Atención Médica</h2>
                    <a href="atenciones.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if ($error_busqueda): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($error_busqueda); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- PASO 1: BÚSQUEDA DE PACIENTE -->
                <?php if (!$paciente_encontrado): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-search"></i> Paso 1: Buscar Paciente</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">Ingrese la cédula del paciente para el cual desea registrar una atención médica.</p>
                            
                            <form method="POST">
                                <input type="hidden" name="accion" value="buscar_paciente">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-lg">
                                            <input type="text" class="form-control" id="cedula_paciente" name="cedula_paciente" 
                                                   placeholder="Ej: 1234567890" value="<?php echo htmlspecialchars($cedula_buscada); ?>" 
                                                   required autofocus>
                                            <button class="btn btn-custom" type="submit">
                                                <i class="bi bi-search"></i> Buscar
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Ingrese un número de cédula ecuatoriana válido</small>
                                    </div>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> <strong>¿No encuentra al paciente?</strong> 
                                <a href="nuevo_paciente.php" class="alert-link">Registre un nuevo paciente primero</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- PASO 2: DATOS DE LA ATENCIÓN -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Paciente Seleccionado</h5>
                        </div>
                        <div class="card-body">
                            <div class="paciente-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Nombre:</strong> 
                                            <?php echo htmlspecialchars($paciente_encontrado['nombres'] . ' ' . $paciente_encontrado['apellidos']); ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Cédula:</strong> 
                                            <?php echo htmlspecialchars($paciente_encontrado['numero_cedula']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2">
                                            <strong>Edad:</strong> 
                                            <?php 
                                                $edad = $paciente_encontrado['edad'];
                                                echo $edad['anios'] . ' años, ' . $edad['meses'] . ' meses';
                                            ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Sexo:</strong> 
                                            <?php echo htmlspecialchars($paciente_encontrado['sexo']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Paso 2: Datos de la Atención</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="accion" value="crear_atencion">
                                <input type="hidden" name="paciente_id" value="<?php echo $paciente_encontrado['id']; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha_hora_atencion" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="fecha_hora_atencion" 
                                               name="fecha_hora_atencion" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="enfermedad_o_problema_actual" class="form-label">Enfermedad/Problema Actual</label>
                                        <input type="text" class="form-control" id="enfermedad_o_problema_actual" 
                                               name="enfermedad_o_problema_actual" placeholder="Ej: Fiebre, Dolor de cabeza">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="examen_fisico" class="form-label">Examen Físico</label>
                                        <textarea class="form-control" id="examen_fisico" name="examen_fisico" 
                                                  rows="3" placeholder="Describa los hallazgos del examen físico..."></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="plan_tratamiento" class="form-label">Plan de Tratamiento</label>
                                        <textarea class="form-control" id="plan_tratamiento" name="plan_tratamiento" 
                                                  rows="3" placeholder="Describa el plan de tratamiento..."></textarea>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-custom">
                                            <i class="bi bi-check-circle"></i> Crear Atención
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="buscar_paciente">
                                            <button type="submit" class="btn btn-outline-secondary">
                                                <i class="bi bi-search"></i> Buscar Otro Paciente
                                            </button>
                                        </form>
                                        <a href="atenciones.php" class="btn btn-outline-danger">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
