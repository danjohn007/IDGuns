<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Notificaciones y Alertas</h2>
            <p class="text-sm text-gray-500 mt-0.5">Historial de notificaciones del sistema</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($notificaciones)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-bell-slash fa-3x mb-3 opacity-30"></i>
            <p class="font-medium text-gray-500">Sin notificaciones</p>
            <p class="text-sm text-gray-400 mt-1">Las alertas del sistema aparecerán aquí</p>
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-100">
            <?php foreach ($notificaciones as $n): ?>
            <li class="flex items-start gap-3 px-5 py-4 hover:bg-gray-50 transition-colors <?= !$n['leido'] ? 'bg-indigo-50/40' : '' ?>">
                <div class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                    <?= !$n['leido'] ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400' ?>">
                    <i class="fa-solid fa-bell text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 <?= !$n['leido'] ? 'font-medium' : '' ?>">
                        <?= htmlspecialchars($n['mensaje'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <?= date('d/m/Y H:i', strtotime($n['created_at'] ?? 'now')) ?>
                        <?php if (!$n['leido']): ?>
                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-indigo-100 text-indigo-700">Nueva</span>
                        <?php endif; ?>
                    </p>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
