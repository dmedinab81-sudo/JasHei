<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Paciente.php';
require_once __DIR__ . '/../src/models/Atencion.php';
require_once __DIR__ . '/../src/utils/Validador.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

$db = new Database();
$pacienteModel = new Paciente();
$atencionModel = new Atencion();

$datos = $_POST;

// Validar que venga un paciente o que se cree antes
if (empty($datos['nombres']) || empty($datos['apellidos'])) {
    echo json_encode(['exito' => false, 'mensaje' => 'Debe indicar nombres y apellidos del paciente']);
    exit;
}

// Crear paciente
$resultadoPaciente = $pacienteModel->crear($datos);

if (!$resultadoPaciente['exito']) {
    echo json_encode($resultadoPaciente);
    exit;
}

$pacienteId = $resultadoPaciente['id'];

// Crear atención inicial
$fechaAtencion = $datos['fecha_hora_atencion'] ?? date('Y-m-d H:i:s');

$atencionResultado = $atencionModel->crear($pacienteId, [
    'fecha_hora_atencion' => $fechaAtencion,
    'enfermedad_o_problema_actual' => $datos['enfermedad_o_problema_actual'] ?? null,
    'examen_fisico' => $datos['examen_fisico'] ?? null,
    'plan_tratamiento' => $datos['plan_tratamiento'] ?? null
]);

if (!$atencionResultado['exito']) {
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Paciente creado, pero falló la atención médica',
        'paciente_id' => $pacienteId,
        'detalle' => $atencionResultado
    ]);
    exit;
}

$atencionId = $atencionResultado['id'];

// Guardar anamnesis si viene información
if (!empty($datos['motivo_consulta'])) {
    $atencionModel->guardarAnamnesis($atencionId, [
        'condicion_llegada' => $datos['condicion_llegada'] ?? 'Otro',
        'motivo_consulta' => $datos['motivo_consulta'],
        'descripcion_motivo' => $datos['descripcion_motivo'] ?? null,
        'alergias' => $datos['alergias'] ?? null,
        'antecedentes_patologicos' => $datos['antecedentes_patologicos'] ?? null,
        'antecedentes_familiares' => $datos['antecedentes_familiares'] ?? null
    ]);
}

// Guardar signos vitales si vienen
if (!empty($datos['frecuencia_cardiaca']) || !empty($datos['temperatura'])) {
    $atencionModel->guardarSignosVitales($atencionId, $datos);
}

// Guardar diagnóstico CIE-10
if (!empty($datos['codigo_cie10'])) {
    $atencionModel->guardarDiagnostico($atencionId, [
        'codigo_cie10' => $datos['codigo_cie10'],
        'descripcion' => $datos['descripcion'] ?? '',
        'tipo_diagnostico' => $datos['tipo_diagnostico'] ?? 'Definitivo'
    ]);
}

// Guardar medicamento
if (!empty($datos['nombre_medicamento'])) {
    $atencionModel->guardarMedicamento($atencionId, $datos);
}

// Guardar ordenes
if (!empty($datos['nombre_examen'])) {
    $atencionModel->guardarOrdenExamen($atencionId, $datos);
}

if (!empty($datos['nombre_estudio'])) {
    $atencionModel->guardarOrdenImagen($atencionId, $datos);
}

echo json_encode([
    'exito' => true,
    'mensaje' => 'Paciente y atención registrados correctamente',
    'paciente_id' => $pacienteId,
    'atencion_id' => $atencionId,
    'numero_historia_clinica' => $pacienteId
]);
