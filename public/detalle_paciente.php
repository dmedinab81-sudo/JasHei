<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Paciente.php';
require_once __DIR__ . '/../src/models/Atencion.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$pacienteId = (int) $_GET['id'];
$pacienteModel = new Paciente();
$atencionModel = new Atencion();

$paciente = $pacienteModel->obtenerPorId($pacienteId);
if (!$paciente) {
    echo 'Paciente no encontrado';
    exit;
}

$atenciones = $atencionModel->listarPorPaciente($pacienteId);
$edad = new DateTime($paciente['fecha_nacimiento']);
$hoy = new DateTime();
$diff = $hoy->diff($edad);
$edadTexto = $diff->y . ' años, ' . $diff->m . ' meses, ' . $diff->d . ' días';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del paciente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            background: #0d6efd;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(220px, 1fr));
            gap: 15px;
        }
        .label {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .value {
            font-size: 15px;
            color: #0f172a;
        }
        .atencion {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 12px;
            background: #fff;
        }
        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1>Detalle del paciente</h1>
            <a class="btn" href="index.php">Volver</a>
        </div>

        <div class="card">
            <h2><?php echo htmlspecialchars($paciente['nombres'] . ' ' . $paciente['apellidos']); ?></h2>
            <div class="grid">
                <div>
                    <span class="label">Historia clínica</span>
                    <div class="value"><?php echo (int)$paciente['id']; ?></div>
                </div>
                <div>
                    <span class="label">Cédula</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['numero_cedula']); ?></div>
                </div>
                <div>
                    <span class="label">Fecha nacimiento</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?></div>
                </div>
                <div>
                    <span class="label">Edad</span>
                    <div class="value"><?php echo htmlspecialchars($edadTexto); ?></div>
                </div>
                <div>
                    <span class="label">Sexo</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['sexo']); ?></div>
                </div>
                <div>
                    <span class="label">Teléfono</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['telefono'] ?? 'Sin dato'); ?></div>
                </div>
                <div>
                    <span class="label">Lugar de nacimiento</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['lugar_nacimiento'] ?? 'Sin dato'); ?></div>
                </div>
                <div>
                    <span class="label">Dirección</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['direccion'] ?? 'Sin dato'); ?></div>
                </div>
                <div>
                    <span class="label">Referencia domiciliaria</span>
                    <div class="value"><?php echo htmlspecialchars($paciente['referencia_domiciliaria'] ?? 'Sin dato'); ?></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3>Atenciones médicas</h3>
            <?php if (empty($atenciones)): ?>
                <p class="muted">No tiene atenciones registradas.</p>
            <?php else: ?>
                <?php foreach ($atenciones as $atencion): ?>
                    <div class="atencion">
                        <strong>Fecha:</strong> <?php echo htmlspecialchars($atencion['fecha_hora_atencion']); ?>
                        <br>
                        <strong>Estado:</strong> <?php echo htmlspecialchars($atencion['estado_atencion']); ?>
                        <br>
                        <strong>Enfermedad/problema actual:</strong>
                        <?php echo !empty($atencion['enfermedad_o_problema_actual']) ? htmlspecialchars($atencion['enfermedad_o_problema_actual']) : 'Sin dato'; ?>
                        <br>
                        <strong>Examen físico:</strong>
                        <?php echo !empty($atencion['examen_fisico']) ? htmlspecialchars($atencion['examen_fisico']) : 'Sin dato'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
