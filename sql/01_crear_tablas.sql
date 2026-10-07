-- ============================================
-- SISTEMA DE GESTIÓN CLÍNICA - JASHEI
-- ============================================
-- Script de creación de tablas para PHP 7
-- Base de datos: MySQL

-- ============================================
-- 1. TABLA DE PACIENTES
-- ============================================
CREATE TABLE IF NOT EXISTS pacientes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  numero_cedula VARCHAR(20) NOT NULL UNIQUE,
  fecha_nacimiento DATE NOT NULL,
  lugar_nacimiento VARCHAR(150) NULL,
  direccion VARCHAR(255) NULL,
  referencia_domiciliaria VARCHAR(255) NULL,
  telefono VARCHAR(20) NULL,
  sexo ENUM('Masculino','Femenino','Otro') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cedula (numero_cedula),
  INDEX idx_nombres (nombres, apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABLA DE ATENCIONES MÉDICAS (PRINCIPAL)
-- ============================================
CREATE TABLE IF NOT EXISTS atenciones_medicas (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  paciente_id BIGINT UNSIGNED NOT NULL,
  fecha_hora_atencion DATETIME NOT NULL,
  enfermedad_o_problema_actual TEXT NULL,
  examen_fisico TEXT NULL,
  plan_tratamiento TEXT NULL,
  condicion_egreso ENUM('Alta','Observación','Hospitalización','Referido','Otro') NULL,
  conciliacion_medicamentos TEXT NULL,
  estado_atencion ENUM('Abierta','Finalizada','Anulada') DEFAULT 'Abierta',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
  INDEX idx_paciente (paciente_id),
  INDEX idx_fecha (fecha_hora_atencion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABLA DE ANAMNESIS (Información inicial)
-- ============================================
CREATE TABLE IF NOT EXISTS anamnesis (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL UNIQUE,
  condicion_llegada ENUM('Estable','Urgente','Emergencia','Consulta programada','Otro') NOT NULL,
  motivo_consulta VARCHAR(255) NOT NULL,
  descripcion_motivo TEXT NULL,
  alergias TEXT NULL,
  antecedentes_patologicos TEXT NULL,
  antecedentes_familiares TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABLA DE SIGNOS VITALES (PEDIATRICOS)
-- ============================================
CREATE TABLE IF NOT EXISTS signos_vitales (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL,
  temperatura DECIMAL(4,1) NULL,
  temperatura_tipo ENUM('Axilar','Oral','Rectal','Frontal') DEFAULT 'Axilar',
  frecuencia_cardiaca INT NULL,
  frecuencia_respiratoria INT NULL,
  presion_sistolica INT NULL,
  presion_diastolica INT NULL,
  saturacion_o2 DECIMAL(4,1) NULL,
  peso DECIMAL(6,2) NULL,
  talla DECIMAL(5,2) NULL,
  imc DECIMAL(5,2) NULL,
  perimetro_cefalico DECIMAL(5,2) NULL,
  observaciones TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABLA DE DIAGNÓSTICOS (CIE-10)
-- ============================================
CREATE TABLE IF NOT EXISTS diagnosticos_atencion (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL,
  codigo_cie10 VARCHAR(20) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  tipo_diagnostico ENUM('Presuntivo','Definitivo','Confirmado','Otros') DEFAULT 'Definitivo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id),
  INDEX idx_cie10 (codigo_cie10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABLA DE MEDICAMENTOS RECETADOS
-- ============================================
CREATE TABLE IF NOT EXISTS medicamentos_recetados (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL,
  nombre_medicamento VARCHAR(200) NOT NULL,
  dosis VARCHAR(100) NULL,
  frecuencia VARCHAR(100) NULL,
  via_administracion VARCHAR(50) NULL,
  duracion VARCHAR(100) NULL,
  indicaciones TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABLA DE ÓRDENES DE EXÁMENES
-- ============================================
CREATE TABLE IF NOT EXISTS ordenes_examenes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL,
  nombre_examen VARCHAR(255) NOT NULL,
  indicaciones TEXT NULL,
  fecha_orden DATE NULL,
  estado ENUM('Pendiente','Realizado','Cancelado') DEFAULT 'Pendiente',
  resultados TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABLA DE ÓRDENES DE IMÁGENES
-- ============================================
CREATE TABLE IF NOT EXISTS ordenes_imagenes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  atencion_id BIGINT UNSIGNED NOT NULL,
  nombre_estudio VARCHAR(255) NOT NULL,
  tipo_imagen ENUM('Radiografía','Ecografía','TAC','Resonancia','Otro') NOT NULL,
  indicaciones TEXT NULL,
  fecha_orden DATE NULL,
  estado ENUM('Pendiente','Realizado','Cancelado') DEFAULT 'Pendiente',
  hallazgos TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE CASCADE,
  INDEX idx_atencion (atencion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABLA DE HISTORIAL CLÍNICO (AUDITORÍA)
-- ============================================
CREATE TABLE IF NOT EXISTS historial_clinico (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  paciente_id BIGINT UNSIGNED NOT NULL,
  atencion_id BIGINT UNSIGNED NULL,
  tipo_evento VARCHAR(100) NOT NULL,
  descripcion TEXT NOT NULL,
  usuario_responsable VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
  FOREIGN KEY (atencion_id) REFERENCES atenciones_medicas(id) ON DELETE SET NULL,
  INDEX idx_paciente (paciente_id),
  INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Crear índices adicionales para búsquedas
-- ============================================
CREATE INDEX idx_atenciones_estado ON atenciones_medicas(estado_atencion);
CREATE INDEX idx_atenciones_egreso ON atenciones_medicas(condicion_egreso);
CREATE INDEX idx_anamnesis_llegada ON anamnesis(condicion_llegada);
