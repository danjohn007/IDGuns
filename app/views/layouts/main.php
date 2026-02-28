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
      /* Dynamic appearance colors from DB */
      :root {
        --color-primary: <?php
          try {
              $__db = Database::getInstance();
              $__cp = $__db->query("SELECT valor FROM configuraciones WHERE clave='color_primario' LIMIT 1")->fetch();
              $__cs = $__db->query("SELECT valor FROM configuraciones WHERE clave='color_secundario' LIMIT 1")->fetch();
              echo htmlspecialchars(($__cp && !empty($__cp['valor'])) ? $__cp['valor'] : '#4f46e5', ENT_QUOTES, 'UTF-8');
          } catch (\Throwable $__e) { echo '#4f46e5'; }
        ?>;
        --color-secondary: <?php
          try {
              echo htmlspecialchars(($__cs && !empty($__cs['valor'])) ? $__cs['valor'] : '#111827', ENT_QUOTES, 'UTF-8');
          } catch (\Throwable $__e) { echo '#111827'; }
        ?>;
      }
      /* Sidebar link base */
      .sidebar-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        color: #cbd5e1;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background-color 0.15s, color 0.15s;
        text-decoration: none;
      }
      .sidebar-link:hover {
        background-color: rgba(255,255,255,0.08);
        color: #fff;
      }
      .sidebar-link.active {
        background: linear-gradient(90deg, var(--color-primary), #6366f1);
        color: #fff;
        box-shadow: 0 2px 8px rgba(79,70,229,0.4);
      }
      .sidebar-link .icon-wrap {
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        background: rgba(255,255,255,0.05);
        flex-shrink: 0;
        font-size: 0.875rem;
      }
      .sidebar-link.active .icon-wrap {
        background: rgba(255,255,255,0.2);
      }

      /* Sidebar transition for mobile */
      #sidebar {
        transition: transform 0.3s ease;
      }
      #overlay {
        transition: opacity 0.3s ease;
      }
    </style>
</head>
<body class="h-full flex overflow-hidden">

<!-- ── Mobile overlay ────────────────────────────────────────────────────── -->
<div id="overlay"
     class="fixed inset-0 bg-black/60 z-20 hidden opacity-0 md:hidden"
     onclick="closeSidebar()"></div>

<!-- ── Sidebar ──────────────────────────────────────────────────────────── -->
<aside id="sidebar"
       class="fixed md:static inset-y-0 left-0 z-30 w-64 flex flex-col flex-shrink-0 h-full md:h-screen -translate-x-full md:translate-x-0 shadow-xl"
       style="background-color: var(--color-secondary, #111827);">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-shield-halved text-white text-lg"></i>
        </div>
        <div>
            <span class="text-white font-bold text-lg leading-none">IDGuns</span>
            <p class="text-indigo-300 text-xs leading-none mt-0.5">Control de Armas</p>
        </div>
        <!-- Close button (mobile only) -->
        <button onclick="closeSidebar()"
                class="ml-auto md:hidden text-gray-400 hover:text-white p-1 rounded transition-colors">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
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
            return "<a href=\"" . BASE_URL . "/{$href}\" class=\"{$cls}\" onclick=\"closeSidebar()\">
                      <span class=\"icon-wrap\"><i class=\"fa-solid {$icon}\"></i></span>
                      <span>{$label}</span>
                    </a>";
        }
        ?>

        <p class="px-3 pt-1 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">Principal</p>
        <?= navLink('dashboard',        'fa-gauge-high',    'Dashboard',        $currentUri, 'dashboard') ?>
        <?= navLink('inventario',       'fa-boxes-stacked', 'Inventario',       $currentUri, 'inventario') ?>
        <?= navLink('almacen',          'fa-warehouse',     'Almacén',          $currentUri, 'almacen') ?>
        <?= navLink('vehiculos',        'fa-car',           'Vehículos',        $currentUri, 'vehiculos') ?>
        <?= navLink('personal',         'fa-id-badge',      'Personal',         $currentUri, 'personal') ?>
        <?= navLink('bitacora',         'fa-book-open',     'Bitácora',         $currentUri, 'bitacora') ?>
        <?= navLink('geolocalizacion',  'fa-map-location-dot', 'Geolocalización', $currentUri, 'geolocalizacion') ?>
        <?= navLink('reportes-gps',     'fa-chart-simple',    'Reportes GPS',    $currentUri, 'reportes-gps') ?>
        <?= navLink('geozonas',         'fa-draw-polygon',  'Geozonas',         $currentUri, 'geozonas') ?>
        <?= navLink('alertas',          'fa-bell',          'Alertas',          $currentUri, 'alertas') ?>
        <?= navLink('analytics',        'fa-chart-line',    'Análisis de Datos', $currentUri, 'analytics') ?>

        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <div class="pt-4 mt-2">
            <p class="px-3 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">Administración</p>
            <?= navLink('admin',          'fa-users-gear',  'Administración',  $currentUri, 'admin') ?>
            <?= navLink('configuracion',  'fa-gear',        'Configuración',   $currentUri, 'configuracion') ?>
        </div>
        <?php endif; ?>
    </nav>

    <!-- User profile -->
    <div class="px-4 py-4 border-t border-white/10 bg-black/20">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/perfil" title="Mi Perfil"
               class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow hover:opacity-80 transition-opacity">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </a>
            <div class="flex-1 min-w-0">
                <a href="<?= BASE_URL ?>/perfil" class="text-sm text-white font-medium truncate hover:text-indigo-300 transition-colors block">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </a>
                <p class="text-xs text-indigo-300 capitalize"><?= htmlspecialchars($_SESSION['user_role'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="<?= BASE_URL ?>/logout" title="Cerrar sesión"
               class="text-gray-400 hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-white/5">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </a>
        </div>
    </div>
</aside>

<!-- ── Main content ──────────────────────────────────────────────────────── -->
<div class="flex-1 flex flex-col min-h-screen overflow-x-hidden overflow-y-auto">

    <!-- Top bar -->
    <header class="bg-white border-b border-gray-200 px-4 md:px-6 py-3.5 flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-3">
            <!-- Hamburger (mobile only) -->
            <button id="hamburger-btn"
                    onclick="openSidebar()"
                    class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                    aria-label="Abrir menú">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <h1 class="text-gray-800 font-semibold text-lg"><?= htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-500">
            <span class="hidden sm:inline"><i class="fa-regular fa-clock mr-1"></i><?= date('d/m/Y H:i') ?></span>
            <!-- Notification Bell -->
            <?php
            $__notifCount = 0;
            try {
                $__ndb   = Database::getInstance();
                $__nStmt = $__ndb->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = :uid AND leido = 0");
                $__nStmt->execute([':uid' => $_SESSION['user_id'] ?? 0]);
                $__notifCount = (int) $__nStmt->fetchColumn();
            } catch (\Throwable $__ne) { /* table may not exist */ }
            ?>
            <a href="<?= BASE_URL ?>/notificaciones" class="relative text-gray-500 hover:text-indigo-600 transition-colors" title="Notificaciones">
                <i class="fa-solid fa-bell text-lg"></i>
                <?php if ($__notifCount > 0): ?>
                <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                    <?= min($__notifCount, 9) ?>
                </span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/logout"
               class="flex items-center gap-1 text-red-500 hover:text-red-700 font-medium">
               <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Salir</span>
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
    <main class="flex-1 p-4 md:p-6">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 px-6 py-3 text-center text-xs text-gray-400">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &mdash; Secretaría de Seguridad Ciudadana de Querétaro
    </footer>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('overlay');

  function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden', 'opacity-0');
    overlay.classList.add('opacity-100');
    document.body.classList.add('overflow-hidden');
    sidebar.querySelector('a, button').focus();
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.remove('opacity-100');
    overlay.classList.add('opacity-0');
    setTimeout(() => overlay.classList.add('hidden'), 300);
    document.body.classList.remove('overflow-hidden');
    const hamburger = document.getElementById('hamburger-btn');
    if (hamburger) hamburger.focus();
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeSidebar();
  });
</script>

</body>
</html>
