<?php
// Auto-detect BASE_URL from server environment
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');
$baseUrl   = $protocol . '://' . $host . ($scriptDir !== '' ? $scriptDir : '');

define('BASE_URL',    rtrim($baseUrl, '/'));
define('APP_NAME',    'IDGuns - Control de Armas');
define('APP_VERSION', '1.0.0');
define('ROOT_PATH',   dirname(__DIR__));

// Database credentials
define('DB_HOST',    'localhost');
define('DB_NAME',    'idguns_sistema');
define('DB_USER',    'idguns_sistema');
define('DB_PASS',    'JWX];qE-WRXH');
define('DB_CHARSET', 'utf8mb4');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Timezone
date_default_timezone_set('America/Mexico_City');

// Error reporting — set APP_DEBUG=true in environment for development
$isDebug = (getenv('APP_DEBUG') === 'true');
error_reporting(E_ALL);
ini_set('display_errors', $isDebug ? 1 : 0);
ini_set('log_errors', 1);
