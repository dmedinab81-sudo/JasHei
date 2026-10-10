<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/models/Paciente.php';
require_once __DIR__ . '/../src/models/Atencion.php';

$db = new Database();

if (!isset($_GET['atencion_id'])) {
    header('Location: index.php');
    exit;
}

$atencionId = (int) $_GET['atencion_id'];

// Obtener atención
$sqlAtencion = "SELECT a.*, p.nombres, p.apellidos, p.numero_cedula, p.fecha_nacimiento, p.sexo 
                FROM atenciones_medicas a
                JOIN pacientes p ON a.paciente_id = p.id
                WHERE a.id = ?";
$stmtAtencion = $db->prepare($sqlAtencion);
$stmtAtencion->bind_param('i', $atencionId);
$stmtAtencion->execute();
$atencion = $stmtAtencion->get_result()->fetch_assoc();

if (!$atencion) {
    echo 'Atención no encontrada';
    exit;
}

// Obtener anamnesis
$sqlAnamnesis = "SELECT * FROM anamnesis WHERE atencion_id = ?";
$stmtAnamnesis = $db->prepare($sqlAnamnesis);
$stmtAnamnesis->bind_param('i', $atencionId);
$stmtAnamnesis->execute();
$anamnesis = $stmtAnamnesis->get_result()->fetch_assoc();

// Obtener signos vitales
$sqlSignos = "SELECT * FROM signos_vitales WHERE atencion_id = ? ORDER BY created_at DESC";
$stmtSignos = $db->prepare($sqlSignos);
$stmtSignos->bind_param('i', $atencionId);
$stmtSignos->execute();
$signosVitales = $stmtSignos->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener diagnósticos
$sqlDiagnosticos = "SELECT * FROM diagnosticos_atencion WHERE atencion_id = ?";
$stmtDiagnosticos = $db->prepare($sqlDiagnosticos);
$stmtDiagnosticos->bind_param('i', $atencionId);
$stmtDiagnosticos->execute();
$diagnosticos = $stmtDiagnosticos->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener medicamentos
$sqlMedicamentos = "SELECT * FROM medicamentos_recetados WHERE atencion_id = ?";
$stmtMedicamentos = $db->prepare($sqlMedicamentos);
$stmtMedicamentos->bind_param('i', $atencionId);
$stmtMedicamentos->execute();
$medicamentos = $stmtMedicamentos->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener órdenes de exámenes
$sqlExamenes = "SELECT * FROM ordenes_examenes WHERE atencion_id = ?";
$stmtExamenes = $db->prepare($sqlExamenes);
$stmtExamenes->bind_param('i', $atencionId);
$stmtExamenes->execute();
$examenes = $stmtExamenes->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener órdenes de imágenes
$sqlImagenes = "SELECT * FROM ordenes_imagenes WHERE atencion_id = ?";
$stmtImagenes = $db->prepare($sqlImagenes);
$stmtImagenes->bind_param('i', $atencionId);
$stmtImagenes->execute();
$imagenes = $stmtImagenes->get_result()->fetch_all(MYSQLI_ASSOC);

$pacienteId = $atencion['paciente_id'];
$edad = new DateTime($atencion['fecha_nacimiento']);
$hoy = new DateTime();
$diff = $hoy->diff($edad);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Atención</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        .btn-editar {
            background: #ffc107;
            color: black;
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
            grid-template-columns: repeat(3, minmax(200px, 1fr));
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
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 15px 0 10px 0;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
        }
        .muted {
            color: #6b7280;
        }
        .item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }
        .receta {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #eef2ff;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-abierta {
            background: #dcfce7;
            color: #166534;
        }
        .status-finalizada {
            background: #dbeafe;
            color: #1e40af;
        }
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1>Detalle de Atención</h1>
            <div>
                <a class="btn btn-editar" href="editar_atencion.php?id=<?php echo $atencionId; ?>">Editar</a>
                <a class="btn" href="atenciones.php?id=<?php echo $pacienteId; ?>">Volver</a>
            </div>
        </div>

        <!-- Información del paciente -->
        <div class="card">
            <div class="section-title">Información del Paciente</div>
            <div class="grid">
                <div>
                    <span class="label">Paciente</span>
                    <div class="value"><?php echo htmlspecialchars($atencion['nombres'] . ' ' . $atencion['apellidos']); ?></div>
                </div>
                <div>
                    <span class="label">Cédula</span>
                    <div class="value"><?php echo htmlspecialchars($atencion['numero_cedula']); ?></div>
                </div>
                <div>
                    <span class="label">Edad</span>
                    <div class="value"><?php echo $diff->y . ' años, ' . $diff->m . ' meses'; ?></div>
                </div>
                <div>
                    <span class="label">Sexo</span>
                    <div class="value"><?php echo htmlspecialchars($atencion['sexo']); ?></div>
                </div>
                <div>
                    <span class="label">Fecha de atención</span>
                    <div class="value"><?php echo htmlspecialchars($atencion['fecha_hora_atencion']); ?></div>
                </div>
                <div>
                    <span class="label">Estado</span>
                    <div class="value"><span class="status status-<?php echo strtolower($atencion['estado_atencion']); ?>"><?php echo htmlspecialchars($atencion['estado_atencion']); ?></span></div>
                </div>
            </div>
        </div>

        <!-- Anamnesis -->
        <?php if ($anamnesis): ?>
        <div class="card">
            <div class="section-title">Anamnesis</div>
            <div class="grid">
                <div>
                    <span class="label">Condición de llegada</span>
                    <div class="value"><?php echo htmlspecialchars($anamnesis['condicion_llegada'] ?? 'N/A'); ?></div>
                </div>
                <div>
                    <span class="label">Motivo de consulta</span>
                    <div class="value"><?php echo htmlspecialchars($anamnesis['motivo_consulta'] ?? 'N/A'); ?></div>
                </div>
            </div>
            <?php if (!empty($anamnesis['descripcion_motivo'])): ?>
            <div style="margin-top: 10px;">
                <span class="label">Descripción del motivo</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($anamnesis['descripcion_motivo'])); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($anamnesis['alergias'])): ?>
            <div style="margin-top: 10px; background: #fee2e2; padding: 10px; border-radius: 6px; border-left: 4px solid #dc2626;">
                <span class="label">Alergias</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($anamnesis['alergias'])); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($anamnesis['antecedentes_patologicos'])): ?>
            <div style="margin-top: 10px;">
                <span class="label">Antecedentes patológicos</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($anamnesis['antecedentes_patologicos'])); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($anamnesis['antecedentes_familiares'])): ?>
            <div style="margin-top: 10px;">
                <span class="label">Antecedentes familiares</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($anamnesis['antecedentes_familiares'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Signos vitales -->
        <?php if (!empty($signosVitales)): ?>
        <div class="card">
            <div class="section-title">Signos Vitales</div>
            <?php foreach ($signosVitales as $signo): ?>
            <div class="item">
                <table>
                    <tr>
                        <td><strong>Temperatura:</strong> <?php echo $signo['temperatura'] ?? 'N/A'; ?> °C (<?php echo $signo['temperatura_tipo'] ?? 'N/A'; ?>)</td>
                        <td><strong>FC:</strong> <?php echo $signo['frecuencia_cardiaca'] ?? 'N/A'; ?> lpm</td>
                        <td><strong>FR:</strong> <?php echo $signo['frecuencia_respiratoria'] ?? 'N/A'; ?> rpm</td>
                    </tr>
                    <tr>
                        <td><strong>PA:</strong> <?php echo ($signo['presion_sistolica'] ?? 'N/A') . '/' . ($signo['presion_diastolica'] ?? 'N/A'); ?> mmHg</td>
                        <td><strong>O2:</strong> <?php echo $signo['saturacion_o2'] ?? 'N/A'; ?> %</td>
                        <td><strong>Peso:</strong> <?php echo $signo['peso'] ?? 'N/A'; ?> kg</td>
                    </tr>
                    <tr>
                        <td><strong>Talla:</strong> <?php echo $signo['talla'] ?? 'N/A'; ?> m</td>
                        <td><strong>IMC:</strong> <?php echo $signo['imc'] ?? 'N/A'; ?></td>
                        <td><strong>Perímetro cefálico:</strong> <?php echo $signo['perimetro_cefalico'] ?? 'N/A'; ?> cm</td>
                    </tr>
                </table>
                <?php if (!empty($signo['observaciones'])): ?>
                <div style="margin-top: 8px; color: #6b7280;">
                    <strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($signo['observaciones'])); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Examen físico -->
        <?php if (!empty($atencion['examen_fisico'])): ?>
        <div class="card">
            <div class="section-title">Examen Físico</div>
            <div class="value"><?php echo nl2br(htmlspecialchars($atencion['examen_fisico'])); ?></div>
        </div>
        <?php endif; ?>

        <!-- Diagnósticos -->
        <?php if (!empty($diagnosticos)): ?>
        <div class="card">
            <div class="section-title">Diagnósticos (CIE-10)</div>
            <?php foreach ($diagnosticos as $diag): ?>
            <div class="item">
                <strong><?php echo htmlspecialchars($diag['codigo_cie10']); ?></strong> - <?php echo htmlspecialchars($diag['descripcion']); ?>
                <br>
                <span class="muted">Tipo: <?php echo htmlspecialchars($diag['tipo_diagnostico']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Medicamentos -->
        <?php if (!empty($medicamentos)): ?>
        <div class="card">
            <div class="section-title">Receta Médica</div>
            <div style="background: #fffbeb; padding: 15px; border: 2px solid #fbbf24; border-radius: 8px;">
                <h3 style="margin: 0 0 15px 0;">Prescripción de medicamentos</h3>
                <?php foreach ($medicamentos as $med): ?>
                <div class="receta">
                    <strong><?php echo htmlspecialchars($med['nombre_medicamento']); ?></strong><br>
                    <span class="label">Dosis:</span> <?php echo htmlspecialchars($med['dosis'] ?? 'N/A'); ?><br>
                    <span class="label">Frecuencia:</span> <?php echo htmlspecialchars($med['frecuencia'] ?? 'N/A'); ?><br>
                    <span class="label">Vía de administración:</span> <?php echo htmlspecialchars($med['via_administracion'] ?? 'N/A'); ?><br>
                    <span class="label">Duración:</span> <?php echo htmlspecialchars($med['duracion'] ?? 'N/A'); ?><br>
                    <?php if (!empty($med['indicaciones'])): ?>
                    <span class="label">Indicaciones:</span> <?php echo nl2br(htmlspecialchars($med['indicaciones'])); ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Órdenes de exámenes -->
        <?php if (!empty($examenes)): ?>
        <div class="card">
            <div class="section-title">Órdenes de Exámenes</div>
            <table>
                <thead>
                    <tr>
                        <th>Examen</th>
                        <th>Indicaciones</th>
                        <th>Fecha orden</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($examenes as $exam): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($exam['nombre_examen']); ?></td>
                        <td><?php echo htmlspecialchars($exam['indicaciones'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($exam['fecha_orden'] ?? 'N/A'); ?></td>
                        <td><span class="status"><?php echo htmlspecialchars($exam['estado']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Órdenes de imágenes -->
        <?php if (!empty($imagenes)): ?>
        <div class="card">
            <div class="section-title">Órdenes de Imágenes</div>
            <table>
                <thead>
                    <tr>
                        <th>Estudio</th>
                        <th>Tipo</th>
                        <th>Indicaciones</th>
                        <th>Fecha orden</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($imagenes as $img): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($img['nombre_estudio']); ?></td>
                        <td><?php echo htmlspecialchars($img['tipo_imagen']); ?></td>
                        <td><?php echo htmlspecialchars($img['indicaciones'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($img['fecha_orden'] ?? 'N/A'); ?></td>
                        <td><span class="status"><?php echo htmlspecialchars($img['estado']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Plan de tratamiento y egreso -->
        <div class="card">
            <div class="section-title">Plan de Tratamiento y Egreso</div>
            <?php if (!empty($atencion['plan_tratamiento'])): ?>
            <div>
                <span class="label">Plan de tratamiento</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($atencion['plan_tratamiento'])); ?></div>
            </div>
            <?php else: ?>
            <div class="muted">No hay plan de tratamiento registrado</div>
            <?php endif; ?>
            <?php if (!empty($atencion['condicion_egreso'])): ?>
            <div style="margin-top: 10px;">
                <span class="label">Condición de egreso</span>
                <div class="value"><?php echo htmlspecialchars($atencion['condicion_egreso']); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($atencion['conciliacion_medicamentos'])): ?>
            <div style="margin-top: 10px;">
                <span class="label">Conciliación de medicamentos</span>
                <div class="value"><?php echo nl2br(htmlspecialchars($atencion['conciliacion_medicamentos'])); ?></div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>
