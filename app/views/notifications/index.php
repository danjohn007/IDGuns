<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Notificaciones y Alertas</h2>
            <p class="text-sm text-gray-500 mt-0.5">Eventos de entrada y salida de geozonas</p>
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
                        <?php if ($tipo === 'geofenceEnter'): ?>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-blue-700 bg-blue-50 mr-1">Entrada</span>
                        <?php elseif ($tipo === 'geofenceExit'): ?>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold text-orange-700 bg-orange-50 mr-1">Salida</span>
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
