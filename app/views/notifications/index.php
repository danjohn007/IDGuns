<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Notificaciones y Alertas</h2>
            <p class="text-sm text-gray-500 mt-0.5">Eventos del sistema: personal, vehículos, inventario, almacén, bitácora y geozonas</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($notificaciones)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-bell-slash fa-3x mb-3 opacity-30"></i>
            <p class="font-medium text-gray-500">Sin notificaciones</p>
            <p class="text-sm text-gray-400 mt-1">Cuando un dispositivo entre o salga de una geozona, la notificación aparecerá aquí</p>
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-100 max-h-[700px] overflow-y-auto">
            <?php
            $tipoIcons = [
                'geofenceEnter' => ['fa-right-to-bracket',    'bg-blue-100 text-blue-600'],
                'geofenceExit'  => ['fa-right-from-bracket',  'bg-orange-100 text-orange-600'],
                'personal'      => ['fa-user',                'bg-emerald-100 text-emerald-600'],
                'vehiculo'      => ['fa-car',                 'bg-sky-100 text-sky-600'],
                'inventario'    => ['fa-boxes-stacked',       'bg-violet-100 text-violet-600'],
                'almacen'       => ['fa-warehouse',           'bg-amber-100 text-amber-600'],
                'bitacora'      => ['fa-book',                'bg-rose-100 text-rose-600'],
                'geozona'       => ['fa-draw-polygon',        'bg-teal-100 text-teal-600'],
                'sistema'       => ['fa-gear',                'bg-gray-100 text-gray-600'],
            ];
            foreach ($notificaciones as $n):
                $tipo = $n['tipo'] ?? 'sistema';
                [$icon, $iconCls] = $tipoIcons[$tipo] ?? ['fa-bell', (!$n['leido'] ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400')];
            ?>
            <li class="flex items-start gap-3 px-5 py-4 hover:bg-gray-50 transition-colors <?= !$n['leido'] ? 'bg-indigo-50/40' : '' ?>">
                <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 <?= $iconCls ?>">
                    <i class="fa-solid <?= $icon ?> text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 <?= !$n['leido'] ? 'font-medium' : '' ?>">
                        <?php
                        $badges = [
                            'geofenceEnter' => ['Entrada',    'text-blue-700 bg-blue-50'],
                            'geofenceExit'  => ['Salida',     'text-orange-700 bg-orange-50'],
                            'personal'      => ['Personal',   'text-emerald-700 bg-emerald-50'],
                            'vehiculo'      => ['Vehículo',   'text-sky-700 bg-sky-50'],
                            'inventario'    => ['Inventario', 'text-violet-700 bg-violet-50'],
                            'almacen'       => ['Almacén',    'text-amber-700 bg-amber-50'],
                            'bitacora'      => ['Bitácora',   'text-rose-700 bg-rose-50'],
                            'geozona'       => ['Geozona',    'text-teal-700 bg-teal-50'],
                        ];
                        if (isset($badges[$tipo])):
                            [$badgeLabel, $badgeCls] = $badges[$tipo];
                        ?>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold <?= $badgeCls ?> mr-1"><?= $badgeLabel ?></span>
                        <?php endif; ?>
                        <?= htmlspecialchars($n['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i class="fa-regular fa-clock mr-1"></i>
                        <?= date('d/m/Y H:i', strtotime($n['created_at'] ?? 'now')) ?>
                        <?php if (!$n['leido']): ?>
                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-indigo-100 text-indigo-700">Nueva</span>
                        <?php endif; ?>
                    </p>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="px-5 py-2 bg-gray-50 border-t border-gray-100 text-xs text-gray-400 text-right">
            <?= count($notificaciones) ?> notificación<?= count($notificaciones) !== 1 ? 'es' : '' ?>
        </div>
        <?php endif; ?>
    </div>
</div>
