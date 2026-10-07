<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Paciente.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pacienteId = (int) $_POST['id'];
$datos = $_POST;

$pacienteModel = new Paciente();
$resultado = $pacienteModel->actualizar($pacienteId, $datos);

if ($resultado['exito']) {
    header('Location: detalle_paciente.php?id=' . $pacienteId);
    exit;
}

echo '<pre>' . htmlspecialchars($resultado['mensaje']) . '</pre>';
exit;
