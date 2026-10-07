<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Atencion.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$atencionId = (int) $_GET['id'];
$db = new Database();

$sqlAtencion = "SELECT a.*, p.nombres, p.apellidos, p.numero_cedula 
                FROM atenciones_medicas a
                JOIN pacientes p ON a.paciente_id = p.id
                WHERE a.id = ?";
$stmt = $db->prepare($sqlAtencion);
$stmt->bind_param('i', $atencionId);
$stmt->execute();
$atencion = $stmt->get_result()->fetch_assoc();

if (!$atencion) {
    echo 'Atención no encontrada';
    exit;
}

$sqlAnamnesis = "SELECT * FROM anamnesis WHERE atencion_id = ?";
$stmtA = $db->prepare($sqlAnamnesis);
$stmtA->bind_param('i', $atencionId);
$stmtA->execute();
$anamnesis = $stmtA->get_result()->fetch_assoc();

$sqlMedicamentos = "SELECT * FROM medicamentos_recetados WHERE atencion_id = ?";
$stmtM = $db->prepare($sqlMedicamentos);
$stmtM->bind_param('i', $atencionId);
$stmtM->execute();
$medicamentos = $stmtM->get_result()->fetch_all(MYSQLI_ASSOC);

$sqlDiagnosticos = "SELECT * FROM diagnosticos_atencion WHERE atencion_id = ?";
$stmtD = $db->prepare($sqlDiagnosticos);
$stmtD->bind_param('i', $atencionId);
$stmtD->execute();
$diagnosticos = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar atención</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { display: inline-block; text-decoration: none; background: #0d6efd; color: white; padding: 10px 18px; border-radius: 8px; font-weight: bold; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(250px, 1fr)); gap: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea, button { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; }
        textarea { min-height: 90px; }
        .full { grid-column: 1 / -1; }
        .actions { margin-top: 20px; }
        .btn-submit { background: #198754; border: none; color: white; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="topbar">
        <h1>Editar atención</h1>
        <a class="btn" href="detalle_atencion.php?atencion_id=<?php echo $atencionId; ?>">Volver</a>
    </div>

    <form action="guardar_edicion_atencion.php" method="POST">
        <input type="hidden" name="atencion_id" value="<?php echo (int)$atencionId; ?>">

        <div class="form-grid">
            <div class="full">
                <label>Paciente</label>
                <input type="text" value="<?php echo htmlspecialchars($atencion['nombres'] . ' ' . $atencion['apellidos']); ?>" readonly>
            </div>
            <div>
                <label>Fecha y hora</label>
                <input type="datetime-local" name="fecha_hora_atencion" value="<?php echo str_replace(' ', 'T', $atencion['fecha_hora_atencion']); ?>" required>
            </div>
            <div>
                <label>Estado</label>
                <select name="estado_atencion">
                    <option value="Abierta" <?php echo ($atencion['estado_atencion'] === 'Abierta') ? 'selected' : ''; ?>>Abierta</option>
                    <option value="Finalizada" <?php echo ($atencion['estado_atencion'] === 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    <option value="Anulada" <?php echo ($atencion['estado_atencion'] === 'Anulada') ? 'selected' : ''; ?>>Anulada</option>
                </select>
            </div>
            <div class="full">
                <label>Enfermedad o problema actual</label>
                <textarea name="enfermedad_o_problema_actual"><?php echo htmlspecialchars($atencion['enfermedad_o_problema_actual'] ?? ''); ?></textarea>
            </div>
            <div class="full">
                <label>Examen físico</label>
                <textarea name="examen_fisico"><?php echo htmlspecialchars($atencion['examen_fisico'] ?? ''); ?></textarea>
            </div>
            <div class="full">
                <label>Plan de tratamiento</label>
                <textarea name="plan_tratamiento"><?php echo htmlspecialchars($atencion['plan_tratamiento'] ?? ''); ?></textarea>
            </div>
            <div>
                <label>Condición de egreso</label>
                <select name="condicion_egreso">
                    <option value="Alta" <?php echo ($atencion['condicion_egreso'] === 'Alta') ? 'selected' : ''; ?>>Alta</option>
                    <option value="Observación" <?php echo ($atencion['condicion_egreso'] === 'Observación') ? 'selected' : ''; ?>>Observación</option>
                    <option value="Hospitalización" <?php echo ($atencion['condicion_egreso'] === 'Hospitalización') ? 'selected' : ''; ?>>Hospitalización</option>
                    <option value="Referido" <?php echo ($atencion['condicion_egreso'] === 'Referido') ? 'selected' : ''; ?>>Referido</option>
                    <option value="Otro" <?php echo ($atencion['condicion_egreso'] === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>
            <div>
                <label>Conciliación medicamentos</label>
                <input type="text" name="conciliacion_medicamentos" value="<?php echo htmlspecialchars($atencion['conciliacion_medicamentos'] ?? ''); ?>">
            </div>

            <div class="full">
                <label>Condición de llegada</label>
                <input type="text" name="condicion_llegada" value="<?php echo htmlspecialchars($anamnesis['condicion_llegada'] ?? ''); ?>">
            </div>
            <div>
                <label>Motivo consulta</label>
                <input type="text" name="motivo_consulta" value="<?php echo htmlspecialchars($anamnesis['motivo_consulta'] ?? ''); ?>">
            </div>
            <div>
                <label>Descripción del motivo</label>
                <textarea name="descripcion_motivo"><?php echo htmlspecialchars($anamnesis['descripcion_motivo'] ?? ''); ?></textarea>
            </div>
            <div class="full">
                <label>Alergias</label>
                <textarea name="alergias"><?php echo htmlspecialchars($anamnesis['alergias'] ?? ''); ?></textarea>
            </div>
            <div class="full">
                <label>Antecedentes patológicos</label>
                <textarea name="antecedentes_patologicos"><?php echo htmlspecialchars($anamnesis['antecedentes_patologicos'] ?? ''); ?></textarea>
            </div>
            <div class="full">
                <label>Antecedentes familiares</label>
                <textarea name="antecedentes_familiares"><?php echo htmlspecialchars($anamnesis['antecedentes_familiares'] ?? ''); ?></textarea>
            </div>

            <div class="full">
                <label>Diagnóstico principal</label>
                <input type="text" name="diagnostico_codigo" value="<?php echo htmlspecialchars($diagnosticos[0]['codigo_cie10'] ?? ''); ?>">
            </div>
            <div class="full">
                <label>Descripción diagnóstico</label>
                <textarea name="diagnostico_descripcion"><?php echo htmlspecialchars($diagnosticos[0]['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="full">
                <label>Medicamento recetado</label>
                <input type="text" name="medicamento_nombre" value="<?php echo htmlspecialchars($medicamentos[0]['nombre_medicamento'] ?? ''); ?>">
            </div>
            <div>
                <label>Dosis</label>
                <input type="text" name="medicamento_dosis" value="<?php echo htmlspecialchars($medicamentos[0]['dosis'] ?? ''); ?>">
            </div>
            <div>
                <label>Frecuencia</label>
                <input type="text" name="medicamento_frecuencia" value="<?php echo htmlspecialchars($medicamentos[0]['frecuencia'] ?? ''); ?>">
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-submit">Guardar cambios</button>
        </div>
    </form>
</div>
</body>
</html>
