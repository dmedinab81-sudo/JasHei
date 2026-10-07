<?php
/**
 * Modelo Atencion
 * Gestiona las operaciones de atenciones médicas
 */

require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/utils/Validador.php';

class Atencion {
    
    private $db;
    private $tabla = 'atenciones_medicas';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crear nueva atención médica
     * @param int $paciente_id
     * @param array $datos - Datos de la atención
     * @return array
     */
    public function crear($paciente_id, $datos) {
        // Validar que el paciente exista
        $sql_check = "SELECT id FROM pacientes WHERE id = ?";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param('i', $paciente_id);
        $stmt_check->execute();
        $resultado = $stmt_check->get_result();
        
        if ($resultado->num_rows === 0) {
            return [
                'exito' => false,
                'mensaje' => 'El paciente no existe'
            ];
        }
        
        // Validar datos básicos
        if (!isset($datos['fecha_hora_atencion']) || empty($datos['fecha_hora_atencion'])) {
            return [
                'exito' => false,
                'mensaje' => 'La fecha y hora de atención es requerida'
            ];
        }
        
        $fecha_hora = $datos['fecha_hora_atencion'];
        $enfermedad_problema = isset($datos['enfermedad_o_problema_actual']) ? $datos['enfermedad_o_problema_actual'] : null;
        $examen_fisico = isset($datos['examen_fisico']) ? $datos['examen_fisico'] : null;
        $plan_tratamiento = isset($datos['plan_tratamiento']) ? $datos['plan_tratamiento'] : null;
        
        // Insertar atención
        $sql = "INSERT INTO {$this->tabla} 
                (paciente_id, fecha_hora_atencion, enfermedad_o_problema_actual, 
                 examen_fisico, plan_tratamiento, estado_atencion) 
                VALUES (?, ?, ?, ?, ?, 'Abierta')";
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return [
                'exito' => false,
                'mensaje' => 'Error en la consulta: ' . $this->db->error()
            ];
        }
        
        $stmt->bind_param(
            'issss',
            $paciente_id,
            $fecha_hora,
            $enfermedad_problema,
            $examen_fisico,
            $plan_tratamiento
        );
        
        if ($stmt->execute()) {
            $atencion_id = $this->db->lastInsertId();
            
            return [
                'exito' => true,
                'mensaje' => 'Atención creada exitosamente',
                'id' => $atencion id
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al crear la atención: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener atención por ID con todos sus datos relacionados
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id) {
        $sql = "SELECT a.*, p.nombres, p.apellidos, p.numero_cedula 
                FROM {$this->tabla} a
                LEFT JOIN pacientes p ON a.paciente_id = p.id
                WHERE a.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $atencion = $resultado->fetch_assoc();
            
            if ($atencion) {
                // Cargar datos relacionados
                $atencion['anamnesis'] = $this->obtenerAnamnesis($id);
                $atencion['signos_vitales'] = $this->obtenerSignosVitales($id);
                $atencion['diagnosticos'] = $this->obtenerDiagnosticos($id);
                $atencion['medicamentos'] = $this->obtenerMedicamentos($id);
                $atencion['ordenes_examenes'] = $this->obtenerOrdenesExamenes($id);
                $atencion['ordenes_imagenes'] = $this->obtenerOrdenesImagenes($id);
            }
            
            return $atencion;
        }
        
        return null;
    }
    
    /**
     * Listar atenciones por paciente
     * @param int $paciente_id
     * @return array
     */
    public function listarPorPaciente($paciente_id) {
        $sql = "SELECT * FROM {$this->tabla} 
                WHERE paciente_id = ?
                ORDER BY fecha_hora_atencion DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $paciente_id);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $atenciones = [];
            
            while ($row = $resultado->fetch_assoc()) {
                $atenciones[] = $row;
            }
            
            return $atenciones;
        }
        
        return [];
    }
    
    /**
     * Crear/Actualizar anamnesis
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarAnamnesis($atencion_id, $datos) {
        // Verificar si ya existe
        $sql_check = "SELECT id FROM anamnesis WHERE atencion_id = ?";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param('i', $atencion_id);
        $stmt_check->execute();
        $existe = $stmt_check->get_result()->num_rows > 0;
        
        $condicion_llegada = $datos['condicion_llegada'] ?? 'Otro';
        $motivo = $datos['motivo_consulta'] ?? '';
        $descripcion = $datos['descripcion_motivo'] ?? null;
        $alergias = $datos['alergias'] ?? null;
        $antecedentes_patologicos = $datos['antecedentes_patologicos'] ?? null;
        $antecedentes_familiares = $datos['antecedentes_familiares'] ?? null;
        
        if ($existe) {
            // Actualizar
            $sql = "UPDATE anamnesis 
                    SET condicion_llegada = ?, motivo_consulta = ?, descripcion_motivo = ?,
                        alergias = ?, antecedentes_patologicos = ?, antecedentes_familiares = ?
                    WHERE atencion_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'ssssssi',
                $condicion_llegada,
                $motivo,
                $descripcion,
                $alergias,
                $antecedentes_patologicos,
                $antecedentes_familiares,
                $atencion_id
            );
        } else {
            // Insertar
            $sql = "INSERT INTO anamnesis 
                    (atencion_id, condicion_llegada, motivo_consulta, descripcion_motivo,
                     alergias, antecedentes_patologicos, antecedentes_familiares)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param(
                'issssss',
                $atencion_id,
                $condicion_llegada,
                $motivo,
                $descripcion,
                $alergias,
                $antecedentes_patologicos,
                $antecedentes_familiares
            );
        }
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Anamnesis guardada exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar la anamnesis: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener anamnesis
     * @param int $atencion_id
     * @return array|null
     */
    public function obtenerAnamnesis($atencion_id) {
        $sql = "SELECT * FROM anamnesis WHERE atencion_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado->fetch_assoc();
    }
    
    /**
     * Guardar signos vitales
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarSignosVitales($atencion_id, $datos) {
        $sql = "INSERT INTO signos_vitales 
                (atencion_id, temperatura, temperatura_tipo, frecuencia_cardiaca, 
                 frecuencia_respiratoria, presion_sistolica, presion_diastolica,
                 saturacion_o2, peso, talla, imc, perimetro_cefalico, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        $temperatura = $datos['temperatura'] ?? null;
        $temp_tipo = $datos['temperatura_tipo'] ?? 'Axilar';
        $fc = $datos['frecuencia_cardiaca'] ?? null;
        $fr = $datos['frecuencia_respiratoria'] ?? null;
        $ps = $datos['presion_sistolica'] ?? null;
        $pd = $datos['presion_diastolica'] ?? null;
        $o2 = $datos['saturacion_o2'] ?? null;
        $peso = $datos['peso'] ?? null;
        $talla = $datos['talla'] ?? null;
        $imc = $datos['imc'] ?? null;
        $cefalico = $datos['perimetro_cefalico'] ?? null;
        $obs = $datos['observaciones'] ?? null;
        
        $stmt->bind_param(
            'idsddddddddds',
            $atencion_id,
            $temperatura,
            $temp_tipo,
            $fc,
            $fr,
            $ps,
            $pd,
            $o2,
            $peso,
            $talla,
            $imc,
            $cefalico,
            $obs
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Signos vitales guardados exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar signos vitales: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener signos vitales
     * @param int $atencion_id
     * @return array
     */
    public function obtenerSignosVitales($atencion_id) {
        $sql = "SELECT * FROM signos_vitales WHERE atencion_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $signos = [];
        while ($row = $resultado->fetch_assoc()) {
            $signos[] = $row;
        }
        
        return $signos;
    }
    
    /**
     * Guardar diagnóstico (CIE-10)
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarDiagnostico($atencion_id, $datos) {
        $codigo = $datos['codigo_cie10'] ?? '';
        $descripcion = $datos['descripcion'] ?? '';
        $tipo = $datos['tipo_diagnostico'] ?? 'Definitivo';
        
        if (empty($codigo) || empty($descripcion)) {
            return [
                'exito' => false,
                'mensaje' => 'Código y descripción del diagnóstico son requeridos'
            ];
        }
        
        $sql = "INSERT INTO diagnosticos_atencion 
                (atencion_id, codigo_cie10, descripcion, tipo_diagnostico)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'isss',
            $atencion_id,
            $codigo,
            $descripcion,
            $tipo
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Diagnóstico guardado exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar diagnóstico: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener diagnósticos
     * @param int $atencion_id
     * @return array
     */
    public function obtenerDiagnosticos($atencion_id) {
        $sql = "SELECT * FROM diagnosticos_atencion WHERE atencion_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $diagnosticos = [];
        while ($row = $resultado->fetch_assoc()) {
            $diagnosticos[] = $row;
        }
        
        return $diagnosticos;
    }
    
    /**
     * Guardar medicamento recetado
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarMedicamento($atencion_id, $datos) {
        $nombre = $datos['nombre_medicamento'] ?? '';
        
        if (empty($nombre)) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del medicamento es requerido'
            ];
        }
        
        $dosis = $datos['dosis'] ?? null;
        $frecuencia = $datos['frecuencia'] ?? null;
        $via = $datos['via_administracion'] ?? null;
        $duracion = $datos['duracion'] ?? null;
        $indicaciones = $datos['indicaciones'] ?? null;
        
        $sql = "INSERT INTO medicamentos_recetados 
                (atencion_id, nombre_medicamento, dosis, frecuencia, 
                 via_administracion, duracion, indicaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'isssss',
            $atencion_id,
            $nombre,
            $dosis,
            $frecuencia,
            $via,
            $duracion,
            $indicaciones
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Medicamento guardado exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar medicamento: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener medicamentos
     * @param int $atencion_id
     * @return array
     */
    public function obtenerMedicamentos($atencion_id) {
        $sql = "SELECT * FROM medicamentos_recetados WHERE atencion_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $medicamentos = [];
        while ($row = $resultado->fetch_assoc()) {
            $medicamentos[] = $row;
        }
        
        return $medicamentos;
    }
    
    /**
     * Guardar orden de examen
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarOrdenExamen($atencion_id, $datos) {
        $nombre = $datos['nombre_examen'] ?? '';
        
        if (empty($nombre)) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del examen es requerido'
            ];
        }
        
        $indicaciones = $datos['indicaciones'] ?? null;
        $fecha = $datos['fecha_orden'] ?? date('Y-m-d');
        
        $sql = "INSERT INTO ordenes_examenes 
                (atencion_id, nombre_examen, indicaciones, fecha_orden)
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'isss',
            $atencion_id,
            $nombre,
            $indicaciones,
            $fecha
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Orden de examen guardada exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar orden de examen: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener órdenes de exámenes
     * @param int $atencion_id
     * @return array
     */
    public function obtenerOrdenesExamenes($atencion_id) {
        $sql = "SELECT * FROM ordenes_examenes WHERE atencion_id = ? ORDER BY fecha_orden DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $ordenes = [];
        while ($row = $resultado->fetch_assoc()) {
            $ordenes[] = $row;
        }
        
        return $ordenes;
    }
    
    /**
     * Guardar orden de imagen
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function guardarOrdenImagen($atencion_id, $datos) {
        $nombre = $datos['nombre_estudio'] ?? '';
        $tipo = $datos['tipo_imagen'] ?? 'Otro';
        
        if (empty($nombre)) {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del estudio es requerido'
            ];
        }
        
        $indicaciones = $datos['indicaciones'] ?? null;
        $fecha = $datos['fecha_orden'] ?? date('Y-m-d');
        
        $sql = "INSERT INTO ordenes_imagenes 
                (atencion_id, nombre_estudio, tipo_imagen, indicaciones, fecha_orden)
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'issss',
            $atencion_id,
            $nombre,
            $tipo,
            $indicaciones,
            $fecha
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Orden de imagen guardada exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al guardar orden de imagen: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener órdenes de imágenes
     * @param int $atencion_id
     * @return array
     */
    public function obtenerOrdenesImagenes($atencion_id) {
        $sql = "SELECT * FROM ordenes_imagenes WHERE atencion_id = ? ORDER BY fecha_orden DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $atencion_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $ordenes = [];
        while ($row = $resultado->fetch_assoc()) {
            $ordenes[] = $row;
        }
        
        return $ordenes;
    }
    
    /**
     * Finalizar atención
     * @param int $atencion_id
     * @param array $datos
     * @return array
     */
    public function finalizarAtencion($atencion_id, $datos) {
        $condicion_egreso = $datos['condicion_egreso'] ?? 'Alta';
        $conciliacion = $datos['conciliacion_medicamentos'] ?? null;
        
        $sql = "UPDATE {$this->tabla} 
                SET estado_atencion = 'Finalizada',
                    condicion_egreso = ?,
                    conciliacion_medicamentos = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            'ssi',
            $condicion_egreso,
            $conciliacion,
            $atencion_id
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Atención finalizada exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al finalizar la atención: ' . $this->db->error()
            ];
        }
    }
}
