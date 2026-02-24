<?php
// Diagnostic tool — restrict to localhost / local network only.
// Remove or restrict this file after verifying your installation.
$allowedIps = ['127.0.0.1', '::1', 'localhost'];
$remoteIp   = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteIp, $allowedIps, true) && strpos($remoteIp, '192.168.') !== 0 && strpos($remoteIp, '10.') !== 0) {
    http_response_code(403);
    exit('403 Forbidden — Este archivo solo es accesible desde la red local.');
}

require_once __DIR__ . '/config/config.php';

$checks = [];

// PHP version
$checks['php_version'] = [
    'label' => 'PHP Version',
    'value' => PHP_VERSION,
    'ok'    => version_compare(PHP_VERSION, '7.4.0', '>='),
];

// PDO extension
$checks['pdo'] = [
    'label' => 'Extensión PDO',
    'value' => extension_loaded('pdo') ? 'Cargada' : 'No disponible',
    'ok'    => extension_loaded('pdo'),
];

// PDO MySQL driver
$checks['pdo_mysql'] = [
    'label' => 'Driver PDO MySQL',
    'value' => extension_loaded('pdo_mysql') ? 'Disponible' : 'No disponible',
    'ok'    => extension_loaded('pdo_mysql'),
];

// mbstring
$checks['mbstring'] = [
    'label' => 'Extensión mbstring',
    'value' => extension_loaded('mbstring') ? 'Cargada' : 'No disponible',
    'ok'    => extension_loaded('mbstring'),
];

// Session
$checks['session'] = [
    'label' => 'Sesiones PHP',
    'value' => session_status() !== PHP_SESSION_NONE ? 'Activa' : 'No iniciada',
    'ok'    => session_status() !== PHP_SESSION_NONE,
];

// BASE_URL
$checks['base_url'] = [
    'label' => 'BASE_URL detectada',
    'value' => BASE_URL,
    'ok'    => !empty(BASE_URL),
];

// Database connection
$dbOk     = false;
$dbMsg    = '';
$mysqlVer = '';
try {
    require_once __DIR__ . '/config/database.php';
    $pdo      = Database::getInstance();
    $mysqlVer = $pdo->query('SELECT VERSION()')->fetchColumn();
    $dbOk     = true;
    $dbMsg    = "MySQL {$mysqlVer} — Base de datos: " . DB_NAME;
} catch (\PDOException $e) {
    $dbMsg = 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

$checks['database'] = [
    'label' => 'Conexión a MySQL',
    'value' => $dbMsg,
    'ok'    => $dbOk,
];

// Check tables exist
$tables = [];
if ($dbOk) {
    $expected = ['users','oficiales','activos','armas','vehiculos','suministros',
                 'movimientos_almacen','mantenimientos','combustible','bitacora',
                 'configuraciones','dispositivos_iot','errores_sistema'];
    $existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($expected as $tbl) {
        $tables[$tbl] = in_array($tbl, $existing);
    }
}

$allOk = !in_array(false, array_column($checks, 'ok'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión — IDGuns</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">
<div class="max-w-2xl mx-auto space-y-4">

    <!-- Header -->
    <div class="bg-gray-900 text-white rounded-xl p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-shield-halved text-2xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold">IDGuns — Test de Conexión</h1>
            <p class="text-gray-400 text-sm">Diagnóstico del entorno del sistema</p>
        </div>
        <div class="ml-auto">
            <span class="<?= $allOk ? 'bg-green-500' : 'bg-red-500' ?> text-white text-xs font-bold px-3 py-1 rounded-full">
                <?= $allOk ? '✓ Todo OK' : '✗ Hay problemas' ?>
            </span>
        </div>
    </div>

    <!-- Checks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Verificaciones del Entorno</h2>
        </div>
        <ul class="divide-y divide-gray-100">
        <?php foreach ($checks as $check): ?>
        <li class="flex items-center gap-4 px-5 py-3.5">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                <?= $check['ok'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                <i class="fa-solid <?= $check['ok'] ? 'fa-circle-check' : 'fa-circle-xmark' ?> text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($check['label'], ENT_QUOTES,'UTF-8') ?></p>
                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($check['value'], ENT_QUOTES,'UTF-8') ?></p>
            </div>
            <span class="text-xs font-semibold <?= $check['ok'] ? 'text-green-600' : 'text-red-600' ?>">
                <?= $check['ok'] ? 'OK' : 'FALLO' ?>
            </span>
        </li>
        <?php endforeach; ?>
        </ul>
    </div>

    <!-- Tables -->
    <?php if ($dbOk && !empty($tables)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Tablas de la Base de Datos</h2>
        </div>
        <div class="grid grid-cols-2 gap-px bg-gray-100">
        <?php foreach ($tables as $tbl=>$exists): ?>
        <div class="bg-white px-4 py-2.5 flex items-center gap-2">
            <i class="fa-solid <?= $exists ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-red-500' ?> text-xs"></i>
            <span class="text-sm font-mono <?= $exists ? 'text-gray-700' : 'text-red-500' ?>"><?= htmlspecialchars($tbl, ENT_QUOTES,'UTF-8') ?></span>
        </div>
        <?php endforeach; ?>
        </div>
        <?php if (in_array(false, $tables)): ?>
        <div class="px-5 py-3 bg-yellow-50 text-yellow-800 text-xs border-t border-yellow-200">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            Algunas tablas no existen. Ejecute: <code class="font-mono bg-yellow-100 px-1 rounded">mysql -u root idguns &lt; database/schema.sql</code>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Info block -->
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-4 text-sm text-indigo-800 space-y-1">
        <p><strong>BASE_URL:</strong> <code class="font-mono bg-indigo-100 px-1 rounded text-xs"><?= htmlspecialchars(BASE_URL, ENT_QUOTES,'UTF-8') ?></code></p>
        <p><strong>APP_NAME:</strong> <?= htmlspecialchars(APP_NAME, ENT_QUOTES,'UTF-8') ?></p>
        <p><strong>DB Host:</strong> <?= htmlspecialchars(DB_HOST, ENT_QUOTES,'UTF-8') ?> &nbsp;|&nbsp; <strong>DB Name:</strong> <?= htmlspecialchars(DB_NAME, ENT_QUOTES,'UTF-8') ?></p>
        <p><strong>Timezone:</strong> <?= htmlspecialchars(date_default_timezone_get(), ENT_QUOTES,'UTF-8') ?> &nbsp;|&nbsp; <strong>Fecha/Hora:</strong> <?= date('Y-m-d H:i:s') ?></p>
    </div>

    <div class="text-center text-xs text-gray-400 pb-4">
        <a href="<?= BASE_URL ?>/login" class="text-indigo-600 hover:underline font-medium">→ Ir al Sistema IDGuns</a>
    </div>
</div>
</body>
</html>
