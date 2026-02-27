<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Alertas y Notificaciones</h2>
            <p class="text-sm text-gray-500 mt-0.5">Define reglas para notificar eventos del sistema</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <button onclick="toggleCreateForm()"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Nueva Regla
        </button>
        <?php endif; ?>
    </div>

    <!-- Create form -->
    <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
    <div id="create-alerta-form" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">
            <i class="fa-solid fa-bell mr-1 text-indigo-500"></i> Nueva Regla de Alerta
        </h3>
        <form method="POST" action="<?= BASE_URL ?>/alertas/guardar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la Regla *</label>
                    <input type="text" name="nombre" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Alerta salida zona norte">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de Evento</label>
                    <select name="tipo" id="alerta-tipo" onchange="toggleGeozonaField()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="geofenceExit">Salida de Geozona</option>
                        <option value="geofenceEnter">Entrada a Geozona</option>
                        <option value="speeding">Exceso de Velocidad</option>
                        <option value="deviceOffline">Dispositivo sin señal</option>
                        <option value="deviceOnline">Dispositivo en línea</option>
                        <option value="ignitionOn">Encendido de Motor</option>
                        <option value="ignitionOff">Apagado de Motor</option>
                        <option value="alarm">Alarma</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Activo (dejar vacío = todos)</label>
                    <select name="activo_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Todos los activos —</option>
                        <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= htmlspecialchars($a['codigo'] . ' — ' . $a['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="geozona-field">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Geozona asociada</label>
                    <select name="geozona_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Sin geozona —</option>
                        <?php foreach ($geozonas as $g): ?>
                        <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($geozonas)): ?>
                    <p class="text-xs text-gray-400 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Crea geozonas en el módulo <a href="<?= BASE_URL ?>/geozonas" class="text-indigo-500 underline">Geozonas</a>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Canales de Notificación</label>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="notificar_email" value="1" checked
                                   class="rounded text-indigo-600 focus:ring-indigo-500">
                            <i class="fa-solid fa-envelope text-gray-400"></i> Email
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="notificar_whatsapp" value="1"
                                   class="rounded text-green-600 focus:ring-green-500">
                            <i class="fa-brands fa-whatsapp text-green-500"></i> WhatsApp
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar Regla
                </button>
                <button type="button" onclick="toggleCreateForm()"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Rules list -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($reglas)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-bell-slash fa-3x mb-3 opacity-30"></i>
            <p class="font-medium text-gray-500">No hay reglas de alerta configuradas</p>
            <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
            <button onclick="toggleCreateForm()"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-plus"></i> Crear primera regla
            </button>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Tipo</th>
                        <th class="px-4 py-3 text-left">Activo</th>
                        <th class="px-4 py-3 text-left">Geozona</th>
                        <th class="px-4 py-3 text-left">Notificaciones</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
                        <th class="px-4 py-3 text-right">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php
                    $tipoLabels = [
                        'geofenceExit'   => ['Salida de Geozona',      'text-orange-700 bg-orange-50'],
                        'geofenceEnter'  => ['Entrada a Geozona',      'text-blue-700 bg-blue-50'],
                        'speeding'       => ['Exceso de Velocidad',     'text-red-700 bg-red-50'],
                        'deviceOffline'  => ['Dispositivo sin señal',   'text-gray-700 bg-gray-100'],
                        'deviceOnline'   => ['Dispositivo en línea',    'text-green-700 bg-green-50'],
                        'ignitionOn'     => ['Encendido de Motor',      'text-yellow-700 bg-yellow-50'],
                        'ignitionOff'    => ['Apagado de Motor',        'text-yellow-700 bg-yellow-50'],
                        'alarm'          => ['Alarma',                  'text-red-700 bg-red-50'],
                        'custom'         => ['Personalizado',           'text-indigo-700 bg-indigo-50'],
                    ];
                    foreach ($reglas as $r):
                        [$tipoLabel, $tipoCls] = $tipoLabels[$r['tipo']] ?? [$r['tipo'], 'text-gray-700 bg-gray-100'];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <?= htmlspecialchars($r['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= $tipoCls ?>">
                                <?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <?php if ($r['activo_nombre']): ?>
                            <span class="font-mono text-xs text-gray-400"><?= htmlspecialchars($r['activo_codigo'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?= htmlspecialchars($r['activo_nombre'], ENT_QUOTES, 'UTF-8') ?>
                            <?php else: ?>
                            <span class="text-gray-300">Todos</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <?= $r['geozona_nombre'] ? htmlspecialchars($r['geozona_nombre'], ENT_QUOTES, 'UTF-8') : '<span class="text-gray-300">—</span>' ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <?php if ($r['notificar_email']): ?>
                                <span class="inline-flex items-center gap-1 text-xs text-blue-600">
                                    <i class="fa-solid fa-envelope"></i> Email
                                </span>
                                <?php endif; ?>
                                <?php if ($r['notificar_whatsapp']): ?>
                                <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?= BASE_URL ?>/alertas/toggle/<?= $r['id'] ?>"
                               class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium
                                      <?= $r['activo'] ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                                <i class="fa-solid <?= $r['activo'] ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
                                <?= $r['activo'] ? 'Activa' : 'Inactiva' ?>
                            </a>
                        </td>
                        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/alertas/editar/<?= $r['id'] ?>"
                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors mr-1">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="<?= BASE_URL ?>/alertas/eliminar/<?= $r['id'] ?>"
                               onclick="return confirm('¿Eliminar esta regla de alerta?')"
                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleCreateForm() {
    document.getElementById('create-alerta-form').classList.toggle('hidden');
}

function toggleGeozonaField() {
    const tipo = document.getElementById('alerta-tipo').value;
    const gf   = document.getElementById('geozona-field');
    const show = ['geofenceExit', 'geofenceEnter'].includes(tipo);
    gf.style.display = show ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', toggleGeozonaField);
</script>
