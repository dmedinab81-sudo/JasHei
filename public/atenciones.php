<?php
/**
 * Gestión de Atenciones
 * Página principal para listar y buscar atenciones
 */

require_once dirname(__DIR__) . '/src/config/config.php';
require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/Auth.php';
require_once SRC_PATH . '/models/Atencion.php';
require_once SRC_PATH . '/models/Paciente.php';

// Verificar sesión
$auth = new Auth();
if (!$auth->estaAutenticado()) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$pacienteModel = new Paciente();
$atencionModel = new Atencion();

// Obtener todas las atenciones ordenadas por fecha reciente
$sql = "SELECT a.*, p.nombres, p.apellidos, p.numero_cedula 
        FROM atenciones_medicas a
        LEFT JOIN pacientes p ON a.paciente_id = p.id
        ORDER BY a.fecha_hora_atencion DESC
        LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();
$atenciones = [];

while ($row = $resultado->fetch_assoc()) {
    $atenciones[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Atenciones - JasHei</title>
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
        .badge-abierta {
            background-color: #ffc107;
            color: #000;
        }
        .badge-finalizada {
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
                    <h2><i class="bi bi-clipboard2-pulse"></i> Gestión de Atenciones</h2>
                    <a href="nueva_atencion.php" class="btn btn-custom">
                        <i class="bi bi-plus-circle"></i> Nueva Atención
                    </a>
                </div>

                <!-- Tabla de atenciones -->
                <?php if (!empty($atenciones)): ?>
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Atenciones Registradas (<?php echo count($atenciones); ?>)</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Paciente</th>
                                        <th>Cédula</th>
                                        <th>Problema/Enfermedad</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($atenciones as $atencion): ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?php 
                                                        $fecha = new DateTime($atencion['fecha_hora_atencion']);
                                                        echo $fecha->format('d/m/Y H:i');
                                                    ?>
                                                </strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($atencion['nombres'] . ' ' . $atencion['apellidos']); ?></td>
                                            <td><?php echo htmlspecialchars($atencion['numero_cedula']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($atencion['enfermedad_o_problema_actual'] ?? 'N/A', 0, 50)); ?></td>
                                            <td>
                                                <span class="badge <?php echo $atencion['estado_atencion'] === 'Abierta' ? 'badge-abierta' : 'badge-finalizada'; ?>">
                                                    <?php echo htmlspecialchars($atencion['estado_atencion']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="detalle_atencion.php?atencion_id=<?php echo $atencion['id']; ?>"
                                                   class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-eye"></i> Ver
                                                </a>
                                                <a href="editar_atencion.php?id=<?php echo $atencion['id']; ?>"
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
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i> No hay atenciones registradas. 
                        <a href="nueva_atencion.php" class="alert-link">Crea una nueva</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
