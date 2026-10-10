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
    // Conservar únicamente los dígitos
    $cedula = preg_replace('/\D/', '', (string) $cedula);

    // La cédula debe tener exactamente 10 dígitos
    if (strlen($cedula) !== 10) {
        return false;
    }

    // Validar el código de provincia: 01 a 24
    $provincia = (int) substr($cedula, 0, 2);

    if ($provincia < 1 || $provincia > 24) {
        return false;
    }

    // Para personas naturales, el tercer dígito debe estar entre 0 y 5
    $tercerDigito = (int) $cedula[2];

    if ($tercerDigito > 5) {
        return false;
    }

    // Calcular el dígito verificador con los primeros 9 dígitos
    $suma = 0;

    for ($i = 0; $i < 9; $i++) {
        $digito = (int) $cedula[$i];

        // Posiciones 1, 3, 5, 7 y 9: multiplicar por 2
        if ($i % 2 === 0) {
            $valor = $digito * 2;

            // Si el resultado es mayor que 9, restar 9
            if ($valor > 9) {
                $valor -= 9;
            }
        } else {
            // Posiciones 2, 4, 6 y 8: multiplicar por 1
            $valor = $digito;
        }

        $suma += $valor;
    }

    $residuo = $suma % 10;
    $digitoVerificador = ($residuo === 0) ? 0 : 10 - $residuo;

    return (int) $cedula[9] === $digitoVerificador;
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
