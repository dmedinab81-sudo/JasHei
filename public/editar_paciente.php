<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Paciente.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$pacienteId = (int) $_GET['id'];
$db = new Database();
$pacienteModel = new Paciente();
$paciente = $pacienteModel->obtenerPorId($pacienteId);

if (!$paciente) {
    echo 'Paciente no encontrado';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar paciente</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { display: inline-block; text-decoration: none; background: #0d6efd; color: white; padding: 10px 18px; border-radius: 8px; font-weight: bold; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(250px, 1fr)); gap: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        textarea { min-height: 80px; }
        .full { grid-column: 1 / -1; }
        .actions { margin-top: 20px; }
        .btn-submit { background: #198754; border: none; color: white; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Editar paciente</h1>
        <a class="btn" href="detalle_paciente.php?id=<?php echo $pacienteId; ?>">Volver</a>
    </div>

    <form action="guardar_edicion_paciente.php" method="POST">
        <input type="hidden" name="id" value="<?php echo (int)$paciente['id']; ?>">

        <div class="form-grid">
            <div>
                <label>Nombres</label>
                <input type="text" name="nombres" value="<?php echo htmlspecialchars($paciente['nombres']); ?>" required>
            </div>
            <div>
                <label>Apellidos</label>
                <input type="text" name="apellidos" value="<?php echo htmlspecialchars($paciente['apellidos']); ?>" required>
            </div>
            <div>
                <label>Cédula</label>
                <input type="text" name="numero_cedula" value="<?php echo htmlspecialchars($paciente['numero_cedula']); ?>" readonly>
            </div>
            <div>
                <label>Fecha nacimiento</label>
                <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?>" required>
            </div>
            <div>
                <label>Lugar de nacimiento</label>
                <input type="text" name="lugar_nacimiento" value="<?php echo htmlspecialchars($paciente['lugar_nacimiento'] ?? ''); ?>">
            </div>
            <div>
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($paciente['telefono'] ?? ''); ?>">
            </div>
            <div>
                <label>Sexo</label>
                <select name="sexo" required>
                    <option value="Masculino" <?php echo ($paciente['sexo'] === 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="Femenino" <?php echo ($paciente['sexo'] === 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="Otro" <?php echo ($paciente['sexo'] === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
            <div class="full">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?php echo htmlspecialchars($paciente['direccion'] ?? ''); ?>">
            </div>
            <div class="full">
                <label>Referencia domiciliaria</label>
                <input type="text" name="referencia_domiciliaria" value="<?php echo htmlspecialchars($paciente['referencia_domiciliaria'] ?? ''); ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-submit">Guardar cambios</button>
        </div>
    </form>
</div>
</body>
</html>
