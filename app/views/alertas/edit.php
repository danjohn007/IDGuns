<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/alertas" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Editar Regla de Alerta</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="<?= BASE_URL ?>/alertas/actualizar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$regla['id'] ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre de la Regla *</label>
                    <input type="text" name="nombre" required
                           value="<?= htmlspecialchars($regla['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipo de Evento</label>
                    <select name="tipo" id="alerta-tipo-edit" onchange="toggleGeozonaFieldEdit()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php
                        $tipos = [
                            'geofenceExit'  => 'Salida de Geozona',
                            'geofenceEnter' => 'Entrada a Geozona',
                            'speeding'      => 'Exceso de Velocidad',
                            'deviceOffline' => 'Dispositivo sin señal',
                            'deviceOnline'  => 'Dispositivo en línea',
                            'ignitionOn'    => 'Encendido de Motor',
                            'ignitionOff'   => 'Apagado de Motor',
                            'alarm'         => 'Alarma',
                            'custom'        => 'Personalizado',
                        ];
                        foreach ($tipos as $val => $label):
                        ?>
                        <option value="<?= $val ?>" <?= ($regla['tipo'] === $val) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Activo (dejar vacío = todos)</label>
                    <select name="activo_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Todos los activos —</option>
                        <?php foreach ($assets as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ((int)$regla['activo_id'] === (int)$a['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['codigo'] . ' — ' . $a['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="geozona-field-edit">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Geozona asociada</label>
                    <select name="geozona_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Sin geozona —</option>
                        <?php foreach ($geozonas as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ((int)$regla['geozona_id'] === (int)$g['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-2">Canales de Notificación</label>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="notificar_email" value="1"
                                   <?= $regla['notificar_email'] ? 'checked' : '' ?>
                                   class="rounded text-indigo-600 focus:ring-indigo-500">
                            <i class="fa-solid fa-envelope text-gray-400"></i> Email
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="notificar_whatsapp" value="1"
                                   <?= $regla['notificar_whatsapp'] ? 'checked' : '' ?>
                                   class="rounded text-green-600 focus:ring-green-500">
                            <i class="fa-brands fa-whatsapp text-green-500"></i> WhatsApp
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-5">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Cambios
                </button>
                <a href="<?= BASE_URL ?>/alertas"
                   class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleGeozonaFieldEdit() {
    const tipo = document.getElementById('alerta-tipo-edit').value;
    const gf   = document.getElementById('geozona-field-edit');
    gf.style.display = ['geofenceExit', 'geofenceEnter'].includes(tipo) ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', toggleGeozonaFieldEdit);
</script>
