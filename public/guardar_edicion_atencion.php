<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Atencion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$db = new Database();
$atencionId = (int) $_POST['atencion_id'];

$sql = "UPDATE atenciones_medicas SET 
        fecha_hora_atencion = ?,
        enfermedad_o_problema_actual = ?,
        examen_fisico = ?,
        plan_tratamiento = ?,
        condicion_egreso = ?,
        conciliacion_medicamentos = ?,
        estado_atencion = ?
        WHERE id = ?";

$stmt = $db->prepare($sql);
$stmt->bind_param(
    'sssssssi',
    str_replace('T', ' ', $_POST['fecha_hora_atencion']),
    $_POST['enfermedad_o_problema_actual'] ?? null,
    $_POST['examen_fisico'] ?? null,
    $_POST['plan_tratamiento'] ?? null,
    $_POST['condicion_egreso'] ?? null,
    $_POST['conciliacion_medicamentos'] ?? null,
    $_POST['estado_atencion'] ?? 'Abierta',
    $atencionId
);
$stmt->execute();

$sqlAnam = "SELECT id FROM anamnesis WHERE atencion_id = ?";
$stmtA = $db->prepare($sqlAnam);
$stmtA->bind_param('i', $atencionId);
$stmtA->execute();
$existeAnam = $stmtA->get_result()->fetch_assoc();

if ($existeAnam) {
    $sqlUpdateAnam = "UPDATE anamnesis SET
        condicion_llegada = ?,
        motivo_consulta = ?,
        descripcion_motivo = ?,
        alergias = ?,
        antecedentes_patologicos = ?,
        antecedentes_familiares = ?
        WHERE atencion_id = ?";
    $stmtUA = $db->prepare($sqlUpdateAnam);
    $stmtUA->bind_param('ssssssi', $_POST['condicion_llegada'] ?? 'Otro', $_POST['motivo_consulta'] ?? '', $_POST['descripcion_motivo'] ?? null, $_POST['alergias'] ?? null, $_POST['antecedentes_patologicos'] ?? null, $_POST['antecedentes_familiares'] ?? null, $atencionId);
    $stmtUA->execute();
} else {
    $sqlInsertAnam = "INSERT INTO anamnesis (atencion_id, condicion_llegada, motivo_consulta, descripcion_motivo, alergias, antecedentes_patologicos, antecedentes_familiares) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtIA = $db->prepare($sqlInsertAnam);
    $stmtIA->bind_param('issssss', $atencionId, $_POST['condicion_llegada'] ?? 'Otro', $_POST['motivo_consulta'] ?? '', $_POST['descripcion_motivo'] ?? null, $_POST['alergias'] ?? null, $_POST['antecedentes_patologicos'] ?? null, $_POST['antecedentes_familiares'] ?? null);
    $stmtIA->execute();
}

if (!empty($_POST['diagnostico_codigo'])) {
    $sqlDiag = "SELECT id FROM diagnosticos_atencion WHERE atencion_id = ? LIMIT 1";
    $stmtD = $db->prepare($sqlDiag);
    $stmtD->bind_param('i', $atencionId);
    $stmtD->execute();
    $diag = $stmtD->get_result()->fetch_assoc();

    if ($diag) {
        $sqlUpdateDiag = "UPDATE diagnosticos_atencion SET codigo_cie10 = ?, descripcion = ?, tipo_diagnostico = ? WHERE id = ?";
        $stmtUD = $db->prepare($sqlUpdateDiag);
        $stmtUD->bind_param('sssi', $_POST['diagnostico_codigo'], $_POST['diagnostico_descripcion'] ?? '', $_POST['tipo_diagnostico'] ?? 'Definitivo', $diag['id']);
        $stmtUD->execute();
    } else {
        $sqlInsertDiag = "INSERT INTO diagnosticos_atencion (atencion_id, codigo_cie10, descripcion, tipo_diagnostico) VALUES (?, ?, ?, ?)";
        $stmtID = $db->prepare($sqlInsertDiag);
        $stmtID->bind_param('isss', $atencionId, $_POST['diagnostico_codigo'], $_POST['diagnostico_descripcion'] ?? '', $_POST['tipo_diagnostico'] ?? 'Definitivo');
        $stmtID->execute();
    }
}

if (!empty($_POST['medicamento_nombre'])) {
    $sqlMed = "SELECT id FROM medicamentos_recetados WHERE atencion_id = ? LIMIT 1";
    $stmtM = $db->prepare($sqlMed);
    $stmtM->bind_param('i', $atencionId);
    $stmtM->execute();
    $med = $stmtM->get_result()->fetch_assoc();

    if ($med) {
        $sqlUpdateMed = "UPDATE medicamentos_recetados SET nombre_medicamento = ?, dosis = ?, frecuencia = ?, via_administracion = ?, duracion = ?, indicaciones = ? WHERE id = ?";
        $stmtUM = $db->prepare($sqlUpdateMed);
        $stmtUM->bind_param('ssssssi', $_POST['medicamento_nombre'], $_POST['medicamento_dosis'] ?? null, $_POST['medicamento_frecuencia'] ?? null, $_POST['via_administracion'] ?? null, $_POST['medicamento_duracion'] ?? null, $_POST['indicaciones'] ?? null, $med['id']);
        $stmtUM->execute();
    } else {
        $sqlInsertMed = "INSERT INTO medicamentos_recetados (atencion_id, nombre_medicamento, dosis, frecuencia, via_administracion, duracion, indicaciones) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtIM = $db->prepare($sqlInsertMed);
        $stmtIM->bind_param('issssss', $atencionId, $_POST['medicamento_nombre'], $_POST['medicamento_dosis'] ?? null, $_POST['medicamento_frecuencia'] ?? null, $_POST['via_administracion'] ?? null, $_POST['medicamento_duracion'] ?? null, $_POST['indicaciones'] ?? null);
        $stmtIM->execute();
    }
}

header('Location: detalle_atencion.php?atencion_id=' . $atencionId);
exit;
