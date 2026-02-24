<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'IDGuns', ENT_QUOTES, 'UTF-8') ?> — <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: { 600:'#4f46e5', 700:'#4338ca', 800:'#3730a3' }
            }
          }
        }
      }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    <style>
      .sidebar-link { @apply flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition-colors text-sm font-medium; }
      .sidebar-link.active { @apply bg-indigo-600 text-white; }
    </style>
</head>
<body class="h-full flex">

<!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
<aside class="w-64 bg-gray-900 flex flex-col flex-shrink-0 min-h-screen">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
        <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center">
            <i class="fa-solid fa-shield-halved text-white text-lg"></i>
        </div>
        <div>
            <span class="text-white font-bold text-lg leading-none">IDGuns</span>
            <p class="text-gray-400 text-xs leading-none mt-0.5">Control de Armas</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <?php
        $currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
        if ($scriptDir !== '' && $scriptDir !== '/') {
            $currentUri = substr($currentUri, strlen($scriptDir));
        }
        $currentUri = trim($currentUri ?: '/', '/');

        function navLink(string $href, string $icon, string $label, string $current, string $base): string {
            $isActive = ($current === $base || str_starts_with($current, $base . '/'));
            $cls = $isActive ? 'sidebar-link active' : 'sidebar-link';
            return "<a href=\"" . BASE_URL . "/{$href}\" class=\"{$cls}\">
                      <i class=\"fa-solid {$icon} w-4 text-center\"></i> {$label}
                    </a>";
        }
        ?>
        <?= navLink('dashboard',     'fa-gauge-high',    'Dashboard',       $currentUri, 'dashboard') ?>
        <?= navLink('inventario',    'fa-boxes-stacked', 'Inventario',      $currentUri, 'inventario') ?>
        <?= navLink('almacen',       'fa-warehouse',     'Almacén',         $currentUri, 'almacen') ?>
        <?= navLink('vehiculos',     'fa-car',           'Vehículos',       $currentUri, 'vehiculos') ?>
        <?= navLink('bitacora',      'fa-book-open',     'Bitácora',        $currentUri, 'bitacora') ?>

        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <div class="pt-3 mt-3 border-t border-gray-700">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Administración</p>
            <?= navLink('admin',          'fa-users-gear',  'Administración',  $currentUri, 'admin') ?>
            <?= navLink('configuracion',  'fa-gear',        'Configuración',   $currentUri, 'configuracion') ?>
        </div>
        <?php endif; ?>
    </nav>

    <!-- User profile -->
    <div class="px-4 py-4 border-t border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-white font-medium truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-xs text-gray-400 capitalize"><?= htmlspecialchars($_SESSION['user_role'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/logout" title="Cerrar sesión"
               class="text-gray-400 hover:text-red-400 transition-colors">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</aside>

<!-- ── Main content ──────────────────────────────────────────────────────── -->
<div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-6 py-3.5 flex items-center justify-between">
        <h1 class="text-gray-800 font-semibold text-lg"><?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="flex items-center gap-4 text-sm text-gray-500">
            <span><i class="fa-regular fa-clock mr-1"></i><?= date('d/m/Y H:i') ?></span>
            <a href="<?= BASE_URL ?>/logout"
               class="flex items-center gap-1 text-red-500 hover:text-red-700 font-medium">
               <i class="fa-solid fa-right-from-bracket"></i> Salir
            </a>
        </div>
    </header>

    <!-- Flash messages -->
    <?php if (!empty($flash)): ?>
    <div class="mx-6 mt-4">
        <?php $isSuccess = ($flash['type'] === 'success'); ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium
            <?= $isSuccess ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
            <i class="fa-solid <?= $isSuccess ? 'fa-circle-check text-green-500' : 'fa-circle-xmark text-red-500' ?>"></i>
            <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Page content -->
    <main class="flex-1 p-6 overflow-y-auto">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 px-6 py-3 text-center text-xs text-gray-400">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &mdash; Secretaría de Seguridad Ciudadana de Querétaro
    </footer>
</div>

</body>
</html>
