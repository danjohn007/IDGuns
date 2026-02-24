<!-- Stats grid -->
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <?php
    $stats = [
        ['label'=>'Usuarios',      'value'=>$totalUsers,       'icon'=>'fa-users',         'color'=>'indigo'],
        ['label'=>'Activos',        'value'=>$totalActivos,     'icon'=>'fa-boxes-stacked', 'color'=>'blue'],
        ['label'=>'Armas',          'value'=>$totalArmas,       'icon'=>'fa-gun',           'color'=>'red'],
        ['label'=>'Vehículos',      'value'=>$totalVehiculos,   'icon'=>'fa-car',           'color'=>'green'],
        ['label'=>'Suministros',    'value'=>$totalSuministros, 'icon'=>'fa-warehouse',     'color'=>'yellow'],
    ];
    foreach ($stats as $s): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-<?= $s['color'] ?>-50 flex items-center justify-center">
            <i class="fa-solid <?= $s['icon'] ?> text-<?= $s['color'] ?>-600"></i>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800"><?= $s['value'] ?></p>
            <p class="text-xs text-gray-500"><?= $s['label'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick links -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
    <a href="<?= BASE_URL ?>/admin/usuarios"
       class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow flex items-center gap-4">
        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-users-gear text-indigo-600"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-800 text-sm">Gestión de Usuarios</p>
            <p class="text-xs text-gray-400">Crear, editar, asignar roles</p>
        </div>
    </a>
    <a href="<?= BASE_URL ?>/admin/reportes"
       class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-chart-column text-blue-600"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-800 text-sm">Reportes</p>
            <p class="text-xs text-gray-400">Resúmenes y estadísticas</p>
        </div>
    </a>
    <a href="<?= BASE_URL ?>/configuracion"
       class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow flex items-center gap-4">
        <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-gear text-gray-600"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-800 text-sm">Configuración</p>
            <p class="text-xs text-gray-400">Sistema, IoT, integraciones</p>
        </div>
    </a>
    <?php endif; ?>
</div>

<!-- Recent activity and errors -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Recent activity -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-500"></i>Actividad Reciente</h3>
        </div>
        <?php if (empty($activity)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Sin actividad reciente</p>
        <?php else: ?>
        <ul class="divide-y divide-gray-50">
        <?php foreach (array_slice($activity, 0, 8) as $a):
            $tipoColors=['entrada'=>'green','salida'=>'blue','asignacion'=>'indigo','devolucion'=>'yellow','incidencia'=>'red'];
            $tc=$tipoColors[$a['tipo']]??'gray';
        ?>
        <li class="px-5 py-3 flex items-start gap-3">
            <span class="mt-0.5 inline-block w-2 h-2 rounded-full bg-<?= $tc ?>-500 flex-shrink-0"></span>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-800 truncate">
                    <?= ucfirst($a['tipo']) ?>: <?= htmlspecialchars($a['activo_nombre']??'—', ENT_QUOTES,'UTF-8') ?>
                </p>
                <p class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($a['fecha'])) ?> — <?= htmlspecialchars($a['responsable_nombre']??'', ENT_QUOTES,'UTF-8') ?></p>
            </div>
        </li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>

    <!-- System errors -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-bug mr-2 text-red-500"></i>Errores del Sistema</h3>
        </div>
        <?php if (empty($errors)): ?>
        <div class="py-8 text-center text-sm text-green-600">
            <i class="fa-solid fa-circle-check text-2xl mb-2 block"></i>Sin errores registrados
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-50 text-xs">
        <?php foreach (array_slice($errors, 0, 6) as $e): ?>
        <li class="px-5 py-3">
            <div class="flex items-start justify-between">
                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium"><?= htmlspecialchars($e['tipo']??'Error', ENT_QUOTES,'UTF-8') ?></span>
                <span class="text-gray-400"><?= date('d/m H:i', strtotime($e['created_at']??'now')) ?></span>
            </div>
            <p class="text-gray-700 mt-1 truncate"><?= htmlspecialchars($e['mensaje']??'', ENT_QUOTES,'UTF-8') ?></p>
            <p class="text-gray-400"><?= htmlspecialchars($e['archivo']??'', ENT_QUOTES,'UTF-8') ?><?= !empty($e['linea'])?' línea '.$e['linea']:'' ?></p>
        </li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
