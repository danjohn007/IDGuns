<?php
// ─── Bootstrap ─────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// ─── Auto-load models & controllers ────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    foreach ([
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/models/'      . $class . '.php',
    ] as $file) {
        if (file_exists($file)) { require_once $file; return; }
    }
});

// Load BaseController first (all controllers extend it)
require_once __DIR__ . '/app/controllers/BaseController.php';
require_once __DIR__ . '/app/models/BaseModel.php';

// ─── URL parsing ───────────────────────────────────────────────────────────
$scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri        = $requestUri;

if ($scriptDir !== '' && $scriptDir !== '/') {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = trim($uri ?: '/', '/');

// ─── Static route table ────────────────────────────────────────────────────
$routes = [
    ''                           => ['DashboardController', 'index'],
    'dashboard'                  => ['DashboardController', 'index'],
    'dashboard/buscar'           => ['DashboardController', 'search'],
    'login'                      => ['AuthController',      'login'],
    'logout'                     => ['AuthController',      'logout'],
    'recuperar-contrasena'       => ['AuthController',      'forgotPassword'],

    'inventario'                 => ['InventoryController', 'index'],
    'inventario/crear'           => ['InventoryController', 'create'],
    'inventario/guardar'         => ['InventoryController', 'store'],
    'inventario/actualizar'      => ['InventoryController', 'update'],
    'inventario/exportar'        => ['InventoryController', 'export'],

    'almacen'                    => ['WarehouseController', 'index'],
    'almacen/crear'              => ['WarehouseController', 'create'],
    'almacen/guardar'            => ['WarehouseController', 'store'],
    'almacen/movimiento'         => ['WarehouseController', 'movement'],
    'almacen/actualizar'         => ['WarehouseController', 'update'],

    'vehiculos'                  => ['VehicleController',   'index'],
    'vehiculos/crear'            => ['VehicleController',   'create'],
    'vehiculos/guardar'          => ['VehicleController',   'store'],
    'vehiculos/actualizar'       => ['VehicleController',   'update'],
    'vehiculos/exportar'         => ['VehicleController',   'export'],

    'bitacora'                   => ['LogbookController',   'index'],
    'bitacora/crear'             => ['LogbookController',   'create'],
    'bitacora/guardar'           => ['LogbookController',   'store'],

    'analytics'                  => ['AnalyticsController', 'index'],
    'analytics/pyspark'          => ['AnalyticsController', 'pysparkQuery'],

    'admin'                      => ['AdminController',     'index'],
    'admin/usuarios'             => ['AdminController',     'users'],
    'admin/crear-usuario'        => ['AdminController',     'createUser'],
    'admin/guardar-usuario'      => ['AdminController',     'storeUser'],
    'admin/actualizar-usuario'   => ['AdminController',     'updateUser'],
    'admin/reportes'             => ['AdminController',     'reports'],

    'configuracion'              => ['ConfigController',    'index'],
    'configuracion/guardar'      => ['ConfigController',    'save'],
    'configuracion/iot/guardar'  => ['ConfigController',    'saveIot'],
    'configuracion/catalogo/guardar' => ['ConfigController', 'saveCatalog'],

    'geolocalizacion'               => ['GeoController', 'index'],
    'geolocalizacion/posiciones'    => ['GeoController', 'positions'],
    'geolocalizacion/ruta'          => ['GeoController', 'route'],
    'geolocalizacion/resumen'       => ['GeoController', 'summary'],
    'geolocalizacion/dispositivos'  => ['GeoController', 'apiDevices'],

    'reportes-gps'                  => ['GpsReportController', 'index'],
    'reportes-gps/guardar-km'       => ['GpsReportController', 'saveKmPorLitro'],

    'geozonas'                      => ['GeozonaController',  'index'],
    'geozonas/listar'               => ['GeozonaController',  'list'],
    'geozonas/guardar'              => ['GeozonaController',  'store'],
    'geozonas/actualizar'           => ['GeozonaController',  'update'],

    'personal'                      => ['PersonalController', 'index'],
    'personal/crear'                => ['PersonalController', 'create'],
    'personal/guardar'              => ['PersonalController', 'store'],
    'personal/actualizar'           => ['PersonalController', 'update'],
    'personal/importar'             => ['PersonalController', 'import'],
    'personal/procesar-importar'    => ['PersonalController', 'processImport'],
    'personal/buscar'               => ['PersonalController', 'search'],

    'alertas'                       => ['AlertasController',  'index'],
    'alertas/guardar'               => ['AlertasController',  'store'],
    'alertas/actualizar'            => ['AlertasController',  'updateRule'],

    'perfil'                        => ['ProfileController',  'index'],
    'perfil/actualizar'             => ['ProfileController',  'update'],
    'perfil/cambiar-contrasena'     => ['ProfileController',  'changePassword'],

    'notificaciones'                => ['NotificationsController', 'index'],
];

// ─── Dynamic routes with numeric ID segments ───────────────────────────────
// e.g.  inventario/editar/42  → InventoryController::edit  with $_GET['id']=42
$dynamicRoutes = [
    'inventario/editar'        => ['InventoryController', 'edit'],
    'inventario/eliminar'      => ['InventoryController', 'delete'],
    'almacen/editar'           => ['WarehouseController', 'edit'],
    'almacen/eliminar'         => ['WarehouseController', 'delete'],
    'vehiculos/editar'         => ['VehicleController',   'edit'],
    'vehiculos/eliminar'       => ['VehicleController',   'delete'],
    'admin/editar-usuario'     => ['AdminController',     'editUser'],
    'admin/eliminar-usuario'   => ['AdminController',     'deleteUser'],
    'configuracion/iot/eliminar'      => ['ConfigController',  'deleteIot'],
    'configuracion/catalogo/eliminar' => ['ConfigController',  'deleteCatalog'],
    'personal/editar'          => ['PersonalController',  'edit'],
    'personal/eliminar'        => ['PersonalController',  'delete'],
    'geozonas/eliminar'        => ['GeozonaController',   'delete'],
    'alertas/eliminar'         => ['AlertasController',   'delete'],
    'alertas/toggle'           => ['AlertasController',   'toggle'],
    'alertas/editar'           => ['AlertasController',   'edit'],
];

// ─── Resolve route ─────────────────────────────────────────────────────────
$controller = null;
$action     = null;

if (isset($routes[$uri])) {
    [$controller, $action] = $routes[$uri];
} else {
    // Try matching dynamic routes: base/action/id
    $segments = explode('/', $uri);
    if (count($segments) >= 2) {
        $id      = array_pop($segments);
        $base    = implode('/', $segments);
        if (isset($dynamicRoutes[$base]) && ctype_digit((string)$id)) {
            $_GET['id']          = (int) $id;
            [$controller, $action] = $dynamicRoutes[$base];
        }
    }
}

if (!$controller) {
    // 404 – redirect home
    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

// ─── Auth guard ────────────────────────────────────────────────────────────
$publicRoutes = ['login', 'logout', 'recuperar-contrasena'];
if (!in_array($uri, $publicRoutes) && !isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// ─── Dispatch ──────────────────────────────────────────────────────────────
$obj = new $controller();
$obj->$action();
