<?php
/**
 * Modelo Paciente
 * Gestiona las operaciones de pacientes
 */

require_once CONFIG_PATH . '/Database.php';
require_once SRC_PATH . '/utils/Validador.php';

class Paciente {
    
    private $db;
    private $tabla = 'pacientes';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Crear nuevo paciente
     * @param array $datos - Datos del paciente
     * @return array - ['exito' => bool, 'mensaje' => string, 'id' => int]
     */
    public function crear($datos) {
        // Validaciones
        $errores = $this->validarDatos($datos);
        
        if (!empty($errores)) {
            return [
                'exito' => false,
                'mensaje' => implode(', ', $errores),
                'errores' => $errores
            ];
        }
        
        // Preparar datos
        $nombres = Validador::limpiarTexto($datos['nombres']);
        $apellidos = Validador::limpiarTexto($datos['apellidos']);
        $cedula = preg_replace('/[^0-9]/', '', $datos['numero_cedula']);
        $fecha_nacimiento = $datos['fecha_nacimiento'];
        $lugar_nacimiento = isset($datos['lugar_nacimiento']) ? Validador::limpiarTexto($datos['lugar_nacimiento']) : null;
        $direccion = isset($datos['direccion']) ? Validador::limpiarTexto($datos['direccion']) : null;
        $referencia = isset($datos['referencia_domiciliaria']) ? Validador::limpiarTexto($datos['referencia_domiciliaria']) : null;
        $telefono = isset($datos['telefono']) ? Validador::limpiarTexto($datos['telefono']) : null;
        $sexo = $datos['sexo'];
        
        // Insertar paciente
        $sql = "INSERT INTO {$this->tabla} 
                (nombres, apellidos, numero_cedula, fecha_nacimiento, lugar_nacimiento, 
                 direccion, referencia_domiciliaria, telefono, sexo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return [
                'exito' => false,
                'mensaje' => 'Error en la consulta: ' . $this->db->error()
            ];
        }
        
        $stmt->bind_param(
            'sssssssss',
            $nombres,
            $apellidos,
            $cedula,
            $fecha_nacimiento,
            $lugar_nacimiento,
            $direccion,
            $referencia,
            $telefono,
            $sexo
        );
        
        if ($stmt->execute()) {
            $paciente_id = $this->db->lastInsertId();
            
            return [
                'exito' => true,
                'mensaje' => 'Paciente creado exitosamente',
                'id' => $paciente_id,
                'numero_historia_clinica' => $paciente_id
            ];
        } else {
            // Verificar si es error de cédula duplicada
            if (strpos($this->db->error(), 'numero_cedula') !== false) {
                return [
                    'exito' => false,
                    'mensaje' => 'Ya existe un paciente con este número de cédula'
                ];
            }
            
            return [
                'exito' => false,
                'mensaje' => 'Error al crear el paciente: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Obtener paciente por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM {$this->tabla} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $paciente = $resultado->fetch_assoc();
            
            if ($paciente) {
                // Calcular edad
                $edad = Validador::calcularEdad($paciente['fecha_nacimiento']);
                $paciente['edad'] = $edad;
            }
            
            return $paciente;
        }
        
        return null;
    }
    
    /**
     * Obtener paciente por número de cédula
     * @param string $cedula
     * @return array|null
     */
    public function obtenerPorCedula($cedula) {
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        $sql = "SELECT * FROM {$this->tabla} WHERE numero_cedula = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $cedula);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $paciente = $resultado->fetch_assoc();
            
            if ($paciente) {
                $edad = Validador::calcularEdad($paciente['fecha_nacimiento']);
                $paciente['edad'] = $edad;
            }
            
            return $paciente;
        }
        
        return null;
    }
    
    /**
     * Actualizar datos del paciente
     * @param int $id
     * @param array $datos
     * @return array
     */
    public function actualizar($id, $datos) {
        $errores = $this->validarDatos($datos, true);
        
        if (!empty($errores)) {
            return [
                'exito' => false,
                'mensaje' => implode(', ', $errores),
                'errores' => $errores
            ];
        }
        
        $nombres = Validador::limpiarTexto($datos['nombres']);
        $apellidos = Validador::limpiarTexto($datos['apellidos']);
        $lugar_nacimiento = isset($datos['lugar_nacimiento']) ? Validador::limpiarTexto($datos['lugar_nacimiento']) : null;
        $direccion = isset($datos['direccion']) ? Validador::limpiarTexto($datos['direccion']) : null;
        $referencia = isset($datos['referencia_domiciliaria']) ? Validador::limpiarTexto($datos['referencia_domiciliaria']) : null;
        $telefono = isset($datos['telefono']) ? Validador::limpiarTexto($datos['telefono']) : null;
        $sexo = $datos['sexo'];
        
        $sql = "UPDATE {$this->tabla} 
                SET nombres = ?, apellidos = ?, lugar_nacimiento = ?, 
                    direccion = ?, referencia_domiciliaria = ?, telefono = ?, sexo = ?
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return [
                'exito' => false,
                'mensaje' => 'Error en la consulta: ' . $this->db->error()
            ];
        }
        
        $stmt->bind_param(
            'sssssssi',
            $nombres,
            $apellidos,
            $lugar_nacimiento,
            $direccion,
            $referencia,
            $telefono,
            $sexo,
            $id
        );
        
        if ($stmt->execute()) {
            return [
                'exito' => true,
                'mensaje' => 'Paciente actualizado exitosamente'
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Error al actualizar: ' . $this->db->error()
            ];
        }
    }
    
    /**
     * Listar pacientes con filtros
     * @param array $filtros - ['nombre' => '', 'cedula' => '', 'limite' => 20, 'pagina' => 1]
     * @return array
     */
    public function listar($filtros = []) {
        $nombre = isset($filtros['nombre']) ? $filtros['nombre'] : '';
        $cedula = isset($filtros['cedula']) ? $filtros['cedula'] : '';
        $limite = isset($filtros['limite']) ? intval($filtros['limite']) : 20;
        $pagina = isset($filtros['pagina']) ? intval($filtros['pagina']) : 1;
        $offset = ($pagina - 1) * $limite;
        
        $where = "WHERE 1=1";
        $parametros = [];
        $tipos = '';
        
        if (!empty($nombre)) {
            $nombre_busqueda = "%{$nombre}%";
            $where .= " AND (nombres LIKE ? OR apellidos LIKE ?)";
            $parametros[] = $nombre_busqueda;
            $parametros[] = $nombre_busqueda;
            $tipos .= 'ss';
        }
        
        if (!empty($cedula)) {
            $cedula = preg_replace('/[^0-9]/', '', $cedula);
            $where .= " AND numero_cedula LIKE ?";
            $parametros[] = "%{$cedula}%";
            $tipos .= 's';
        }
        
        // Contar total
        $sql_count = "SELECT COUNT(*) as total FROM {$this->tabla} {$where}";
        $stmt_count = $this->db->prepare($sql_count);
        
        if (!empty($parametros)) {
            $stmt_count->bind_param($tipos, ...$parametros);
        }
        
        $stmt_count->execute();
        $resultado_count = $stmt_count->get_result();
        $total = $resultado_count->fetch_assoc()['total'];
        
        // Obtener registros
        $sql = "SELECT * FROM {$this->tabla} {$where} 
                ORDER BY apellidos, nombres ASC 
                LIMIT ? OFFSET ?";
        
        $tipos .= 'ii';
        $parametros[] = $limite;
        $parametros[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($tipos, ...$parametros);
        
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            $pacientes = [];
            
            while ($row = $resultado->fetch_assoc()) {
                $row['edad'] = Validador::calcularEdad($row['fecha_nacimiento']);
                $pacientes[] = $row;
            }
            
            return [
                'pacientes' => $pacientes,
                'total' => $total,
                'pagina' => $pagina,
                'limite' => $limite,
                'total_paginas' => ceil($total / $limite)
            ];
        }
        
        return [
            'pacientes' => [],
            'total' => 0,
            'pagina' => 1,
            'limite' => $limite,
            'total_paginas' => 0
        ];
    }
    
    /**
     * Validar datos del paciente
     * @param array $datos
     * @param bool $actualizacion - Si es actualización, algunos campos son opcionales
     * @return array - Array de errores
     */
    private function validarDatos($datos, $actualizacion = false) {
        $errores = [];
        
        // Nombres
        if (!isset($datos['nombres']) || !Validador::noEstaVacio($datos['nombres'])) {
            $errores[] = 'El campo nombres es requerido';
        }
        
        // Apellidos
        if (!isset($datos['apellidos']) || !Validador::noEstaVacio($datos['apellidos'])) {
            $errores[] = 'El campo apellidos es requerido';
        }
        
        // Cédula (solo si no es actualización o si se proporciona)
        if (!$actualizacion || isset($datos['numero_cedula'])) {
            if (!isset($datos['numero_cedula']) || !Validador::noEstaVacio($datos['numero_cedula'])) {
                $errores[] = 'El número de cédula es requerido';
            } elseif (!Validador::validarCedulaEcuador($datos['numero_cedula'])) {
                $errores[] = 'El número de cédula no es válido. Debe ser un número de cédula ecuatoriana válido';
            }
        }
        
        // Fecha de nacimiento
        if (!isset($datos['fecha_nacimiento']) || !Validador::noEstaVacio($datos['fecha_nacimiento'])) {
            $errores[] = 'La fecha de nacimiento es requerida';
        } elseif (!Validador::validarFechaNacimiento($datos['fecha_nacimiento'])) {
            $errores[] = 'La fecha de nacimiento no es válida o está en el futuro';
        }
        
        // Sexo
        if (!isset($datos['sexo']) || !Validador::noEstaVacio($datos['sexo'])) {
            $errores[] = 'El sexo es requerido';
        } else {
            $sexos_validos = ['Masculino', 'Femenino', 'Otro'];
            if (!in_array($datos['sexo'], $sexos_validos)) {
                $errores[] = 'El sexo no es válido';
            }
        }
        
        // Teléfono (opcional pero validado si se proporciona)
        if (isset($datos['telefono']) && !empty($datos['telefono'])) {
            if (!Validador::validarTelefonoEcuador($datos['telefono'])) {
                $errores[] = 'El teléfono no es válido. Debe tener 10 dígitos';
            }
        }
        
        return $errores;
    }
}
