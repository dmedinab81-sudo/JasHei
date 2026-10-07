<?php
/**
 * Configuración Global - JasHei
 */

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Zona horaria
date_default_timezone_set('America/Guayaquil');

// Definir rutas base
define('BASE_PATH', dirname(dirname(__DIR__)));
define('SRC_PATH', BASE_PATH . '/src');
define('CONFIG_PATH', SRC_PATH . '/config');
define('MODELS_PATH', SRC_PATH . '/models');
define('CONTROLLERS_PATH', SRC_PATH . '/controllers');
define('VIEWS_PATH', SRC_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Incluir archivo de conexión
require_once CONFIG_PATH . '/Database.php';

// Instancia de base de datos global
$db = new Database();
