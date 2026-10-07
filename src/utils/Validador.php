<?php
/**
 * Clase Validador - Validaciones comunes
 * Incluye validación de cédula ecuatoriana
 */

class Validador {
    
    /**
     * Validar cédula ecuatoriana
     * @param string $cedula - Número de cédula
     * @return bool - true si es válida, false si no
     */
    public static function validarCedulaEcuador($cedula) {
        // Remover espacios y guiones
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        
        // Debe tener exactamente 10 dígitos
        if (strlen($cedula) !== 10) {
            return false;
        }
        
        // Verificar que sea numérico
        if (!is_numeric($cedula)) {
            return false;
        }
        
        // Verificar provincia (primeros 2 dígitos entre 01 y 24)
        $provincia = intval(substr($cedula, 0, 2));
        if ($provincia < 1 || $provincia > 24) {
            return false;
        }
        
        // Verificar que el tercer dígito no sea mayor a 6
        $tercerDigito = intval(substr($cedula, 2, 1));
        if ($tercerDigito > 6) {
            return false;
        }
        
        // Validar dígito verificador
        $digitos = str_split(substr($cedula, 0, 9));
        $coeficientes = [2, 3, 4, 5, 6, 7, 8, 9, 2];
        $suma = 0;
        
        foreach ($digitos as $index => $digito) {
            $valor = intval($digito) * $coeficientes[$index];
            
            if ($valor >= 10) {
                $valor = intval($valor / 10) + ($valor % 10);
            }
            
            $suma += $valor;
        }
        
        $digito_verificador = (10 - ($suma % 10)) % 10;
        
        return intval(substr($cedula, 9, 1)) === $digito_verificador;
    }
    
    /**
     * Validar email
     * @param string $email
     * @return bool
     */
    public static function validarEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar teléfono Ecuador (10 dígitos)
     * @param string $telefono
     * @return bool
     */
    public static function validarTelefonoEcuador($telefono) {
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        return strlen($telefono) === 10 && is_numeric($telefono);
    }
    
    /**
     * Validar fecha en formato YYYY-MM-DD
     * @param string $fecha
     * @return bool
     */
    public static function validarFecha($fecha) {
        $formato = 'Y-m-d';
        $d = \DateTime::createFromFormat($formato, $fecha);
        return $d && $d->format($formato) === $fecha;
    }
    
    /**
     * Validar que la fecha sea válida y no sea en el futuro
     * @param string $fecha - Formato YYYY-MM-DD
     * @return bool
     */
    public static function validarFechaNacimiento($fecha) {
        if (!self::validarFecha($fecha)) {
            return false;
        }
        
        $fechaObj = new DateTime($fecha);
        $hoy = new DateTime();
        
        return $fechaObj < $hoy;
    }
    
    /**
     * Limpiar y validar texto básico
     * @param string $texto
     * @return string
     */
    public static function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Validar que no esté vacío
     * @param string $valor
     * @return bool
     */
    public static function noEstaVacio($valor) {
        return !empty(trim($valor));
    }
    
    /**
     * Calcular edad en años, meses y días
     * @param string $fechaNacimiento - Formato YYYY-MM-DD
     * @return array - ['anios' => int, 'meses' => int, 'dias' => int]
     */
    public static function calcularEdad($fechaNacimiento) {
        $fechaNac = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        
        $intervalo = $hoy->diff($fechaNac);
        
        return [
            'anios' => $intervalo->y,
            'meses' => $intervalo->m,
            'dias' => $intervalo->d
        ];
    }
}
