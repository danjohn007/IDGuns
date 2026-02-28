<?php
$today = date('Y-m-d');
?>
<!-- Toolbar / Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-5">
    <form method="GET" action="<?= BASE_URL ?>/reportes-gps" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha Desde</label>
            <input type="date" name="fecha_desde" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>" max="<?= $today ?>"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>" max="<?= $today ?>"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                <i class="fa-solid fa-gas-pump text-orange-500 mr-1"></i>km / Litro de Gasolina
            </label>
            <input type="number" name="km_por_litro" min="0" step="0.1"
                   value="<?= $kmPorLitro > 0 ? htmlspecialchars($kmPorLitro, ENT_QUOTES,'UTF-8') : '' ?>"
                   placeholder="Ej. 12.5"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-36">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">
                <i class="fa-solid fa-dollar-sign text-green-600 mr-1"></i>Precio Gasolina (MXN / Litro)
            </label>
            <input type="number" name="precio_litro" min="0" step="0.01"
                   value="<?= htmlspecialchars($precioPorLitro, ENT_QUOTES,'UTF-8') ?>"
                   placeholder="Ej. 22.50"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-36">
        </div>
        <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-chart-bar mr-1"></i>Generar Reporte
        </button>
        <?php if (!empty($traccarUrl)): ?>
        <a href="<?= BASE_URL ?>/configuracion?tab=gps"
           class="border border-gray-300 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            <i class="fa-solid fa-gear mr-1"></i>Configurar Traccar
        </a>
        <?php endif; ?>
    </form>
    <p class="text-xs text-gray-400 mt-3">
        <i class="fa-solid fa-circle-info mr-1"></i>
        El rango por default es el mes en curso. Ingrese km/litro para estimar el consumo de combustible
        comparado con el precio promedio de gasolina en México.
    </p>
</div>

<?php if (empty($traccarUrl)): ?>
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 mb-5 text-sm text-yellow-800">
    <i class="fa-solid fa-triangle-exclamation mr-2 text-yellow-500"></i>
    Servidor Traccar no configurado. Los datos de ruta no están disponibles.
    <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
    <a href="<?= BASE_URL ?>/configuracion?tab=gps" class="underline font-semibold ml-1">Configurar ahora</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Period summary banner -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <?php
    $totalKm     = 0;
    $totalLitros = 0;
    $totalCosto  = 0;
    foreach ($reports as $r) {
        $totalKm     += $r['km_total'] ?? 0;
        $totalLitros += $r['litros_estimados'] ?? 0;
        $totalCosto  += $r['costo_estimado'] ?? 0;
    }
    ?>
    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
        <p class="text-xs text-indigo-500 font-medium uppercase tracking-wide">Total km recorridos</p>
        <p class="text-2xl font-bold text-indigo-700 mt-1"><?= number_format($totalKm, 1) ?> km</p>
        <p class="text-xs text-indigo-400 mt-0.5"><?= htmlspecialchars($dateFrom) ?> → <?= htmlspecialchars($dateTo) ?></p>
    </div>
    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
        <p class="text-xs text-orange-500 font-medium uppercase tracking-wide">Litros estimados</p>
        <p class="text-2xl font-bold text-orange-700 mt-1">
            <?= $kmPorLitro > 0 ? number_format($totalLitros, 1) . ' L' : '—' ?>
        </p>
        <p class="text-xs text-orange-400 mt-0.5">
            <?= $kmPorLitro > 0 ? htmlspecialchars($kmPorLitro) . ' km/L' : 'Ingrese km/L para calcular' ?>
        </p>
    </div>
    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <p class="text-xs text-green-600 font-medium uppercase tracking-wide">Costo estimado</p>
        <p class="text-2xl font-bold text-green-700 mt-1">
            <?= $kmPorLitro > 0 ? '$' . number_format($totalCosto, 2) : '—' ?>
        </p>
        <p class="text-xs text-green-500 mt-0.5">
            <?= $kmPorLitro > 0 ? '@ $' . number_format($precioPorLitro, 2) . '/L' : 'Ingrese precio por litro' ?>
        </p>
    </div>
</div>

<!-- Device reports table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-700 text-sm">
            <i class="fa-solid fa-satellite-dish mr-2 text-indigo-500"></i>
            Detalle por Dispositivo GPS
            <span class="ml-2 bg-indigo-100 text-indigo-600 text-xs px-2 py-0.5 rounded-full"><?= count($reports) ?></span>
        </h3>
        <button onclick="window.print()"
                class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
    </div>

    <?php if (empty($reports)): ?>
    <div class="py-16 text-center text-gray-400">
        <i class="fa-solid fa-satellite-dish text-4xl block mb-3"></i>
        No hay dispositivos GPS registrados.
        <br>
        <a href="<?= BASE_URL ?>/inventario/crear" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">
            Registrar activo con GPS
        </a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Dispositivo / Activo</th>
                    <th class="text-left px-4 py-3">Unique ID (IMEI)</th>
                    <th class="text-right px-4 py-3">Distancia (km)</th>
                    <th class="text-right px-4 py-3">Duración</th>
                    <th class="text-right px-4 py-3">Vel. Máx (km/h)</th>
                    <th class="text-right px-4 py-3">Litros estimados</th>
                    <th class="text-right px-4 py-3">Costo estimado</th>
                    <th class="text-center px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($reports as $r):
                $d   = $r['device'];
                $s   = $r['summary'];
                $km  = $r['km_total'];
                $lit = $r['litros_estimados'];
                $cos = $r['costo_estimado'];
                $maxSpeed = isset($s['maxSpeed']) ? round($s['maxSpeed'] * 1.852, 1) : null;
                $duration = isset($s['engineHours']) ? gmdate('H:i', (int)($s['engineHours'] / 1000)) : null;
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-gray-400 font-mono mt-0.5">
                        <?= htmlspecialchars($d['activo_codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($d['activo_nombre'])): ?>
                        — <?= htmlspecialchars($d['activo_nombre'], ENT_QUOTES, 'UTF-8') ?>
                        <?php endif; ?>
                    </p>
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($d['unique_id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td class="px-4 py-3 text-right font-semibold <?= $km !== null && $km > 0 ? 'text-indigo-700' : 'text-gray-400' ?>">
                    <?= $km !== null ? number_format($km, 2) : ($traccarUrl ? '<span class="text-gray-300">—</span>' : '<span class="text-gray-300 text-xs">Sin Traccar</span>') ?>
                </td>
                <td class="px-4 py-3 text-right text-gray-600 text-xs">
                    <?= $duration ?? '<span class="text-gray-300">—</span>' ?>
                </td>
                <td class="px-4 py-3 text-right text-gray-600 text-xs">
                    <?= $maxSpeed !== null ? $maxSpeed : '<span class="text-gray-300">—</span>' ?>
                </td>
                <td class="px-4 py-3 text-right text-orange-600 font-medium">
                    <?= $lit !== null ? number_format($lit, 2) . ' L' : '<span class="text-gray-300">—</span>' ?>
                </td>
                <td class="px-4 py-3 text-right text-green-700 font-semibold">
                    <?= $cos !== null ? '$' . number_format($cos, 2) : '<span class="text-gray-300">—</span>' ?>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex flex-col gap-1.5 items-center">
                        <?php if ($traccarUrl && !empty($d['traccar_device_id'])): ?>
                        <a href="<?= BASE_URL ?>/geolocalizacion"
                           title="Ver en mapa"
                           class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium px-2 py-1 rounded hover:bg-indigo-50 transition-colors">
                            <i class="fa-solid fa-map-location-dot"></i> Mapa
                        </a>
                        <?php else: ?>
                        <span class="text-xs text-gray-300">Sin ID Traccar</span>
                        <?php endif; ?>
                        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
                        <div class="flex items-center gap-1 mt-0.5"
                             title="km / Litro individual (sobreescribe el valor global)">
                            <input type="number" min="0" step="0.1"
                                   value="<?= htmlspecialchars($d['km_por_litro'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="km/L"
                                   data-device-id="<?= (int)$d['id'] ?>"
                                   class="kml-input border border-gray-200 rounded px-1.5 py-0.5 text-xs w-16 focus:ring-orange-400 focus:border-orange-400"
                                   aria-label="km/Litro individual">
                            <button type="button"
                                    onclick="saveKml(this)"
                                    class="text-xs bg-orange-100 text-orange-700 hover:bg-orange-200 px-1.5 py-0.5 rounded transition-colors"
                                    title="Guardar km/L">
                                <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Per-device km/L configuration hint -->
    <?php if ($kmPorLitro <= 0): ?>
    <div class="px-5 py-4 bg-amber-50 border-t border-amber-100 text-xs text-amber-700">
        <i class="fa-solid fa-circle-info mr-1"></i>
        Ingrese un valor de <strong>km/litro</strong> en el formulario superior para obtener la estimación de consumo de gasolina.
        El precio promedio de Magna en México es aproximadamente <strong>$22.50 MXN/litro</strong> (IEPS incluido).
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
async function saveKml(btn) {
    const wrap     = btn.closest('div');
    const input    = wrap.querySelector('.kml-input');
    const deviceId = input ? input.dataset.deviceId : null;
    if (!deviceId) return;

    const formData = new FormData();
    formData.append('device_id',   deviceId);
    formData.append('km_por_litro', input.value);

    btn.disabled = true;
    try {
        const res  = await fetch('<?= BASE_URL ?>/reportes-gps/guardar-km', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.ok) {
            input.classList.add('border-green-400');
            setTimeout(() => input.classList.remove('border-green-400'), 1500);
        } else {
            alert('Error: ' + (data.error || 'No se pudo guardar'));
        }
    } catch (e) {
        alert('Error de conexión al guardar km/L: ' + e.message);
    } finally {
        btn.disabled = false;
    }
}
</script>
