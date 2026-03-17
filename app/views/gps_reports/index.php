<?php
$today = date('Y-m-d');
$devicesJson = json_encode(array_map(function($d) {
    return [
        'id'                => (int)$d['id'],
        'traccar_device_id' => (int)($d['traccar_device_id'] ?? 0),
        'unique_id'         => (string)($d['unique_id'] ?? ''),
        'km_por_litro'      => $d['km_por_litro'] !== null && $d['km_por_litro'] !== '' ? (float)$d['km_por_litro'] : null,
    ];
}, $devices), JSON_HEX_TAG | JSON_HEX_AMP);
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

<!-- Period summary banner (updated dynamically by JS) -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    <div class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
        <p class="text-xs text-indigo-500 font-medium uppercase tracking-wide">Total km recorridos</p>
        <p id="total-km" class="text-2xl font-bold text-indigo-700 mt-1">
            <i class="fa-solid fa-spinner fa-spin text-sm text-indigo-300"></i>
        </p>
        <p class="text-xs text-indigo-400 mt-0.5"><?= htmlspecialchars($dateFrom) ?> → <?= htmlspecialchars($dateTo) ?></p>
    </div>
    <div class="bg-orange-50 rounded-xl p-4 border border-orange-100">
        <p class="text-xs text-orange-500 font-medium uppercase tracking-wide">Litros estimados</p>
        <p id="total-litros" class="text-2xl font-bold text-orange-700 mt-1">
            <i class="fa-solid fa-spinner fa-spin text-sm text-orange-300"></i>
        </p>
        <p class="text-xs text-orange-400 mt-0.5">
            <?php if ($kmPorLitro > 0): ?>
                <?= htmlspecialchars($kmPorLitro) ?> km/L (global)
            <?php else: ?>
                Usando km/L individual por dispositivo
            <?php endif; ?>
        </p>
    </div>
    <div class="bg-green-50 rounded-xl p-4 border border-green-100">
        <p class="text-xs text-green-600 font-medium uppercase tracking-wide">Costo estimado</p>
        <p id="total-costo" class="text-2xl font-bold text-green-700 mt-1">
            <i class="fa-solid fa-spinner fa-spin text-sm text-green-300"></i>
        </p>
        <p class="text-xs text-green-500 mt-0.5">@ $<?= number_format($precioPorLitro, 2) ?>/L</p>
    </div>
</div>

<!-- Device reports table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-700 text-sm">
            <i class="fa-solid fa-satellite-dish mr-2 text-indigo-500"></i>
            Detalle por Dispositivo GPS
            <span class="ml-2 bg-indigo-100 text-indigo-600 text-xs px-2 py-0.5 rounded-full"><?= count($devices) ?></span>
        </h3>
        <button onclick="window.print()"
                class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1 border border-gray-200 px-3 py-1.5 rounded-lg hover:bg-gray-50">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
    </div>

    <?php if (empty($devices)): ?>
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
            <?php foreach ($devices as $idx => $d): ?>
            <tr class="hover:bg-gray-50" id="row-<?= $idx ?>">
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
                <!-- These cells are filled by JavaScript -->
                <td class="px-4 py-3 text-right font-semibold text-gray-400" id="km-<?= $idx ?>">
                    <?php if (!empty($traccarUrl) && !empty($d['traccar_device_id'])): ?>
                    <i class="fa-solid fa-spinner fa-spin text-xs text-gray-300"></i>
                    <?php else: ?>
                    <span class="text-gray-300 text-xs"><?= empty($traccarUrl) ? 'Sin Traccar' : 'Sin ID Traccar' ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right text-gray-600 text-xs" id="dur-<?= $idx ?>">
                    <?php if (!empty($traccarUrl) && !empty($d['traccar_device_id'])): ?>
                    <i class="fa-solid fa-spinner fa-spin text-xs text-gray-300"></i>
                    <?php else: ?>
                    <span class="text-gray-300">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right text-gray-600 text-xs" id="spd-<?= $idx ?>">
                    <?php if (!empty($traccarUrl) && !empty($d['traccar_device_id'])): ?>
                    <i class="fa-solid fa-spinner fa-spin text-xs text-gray-300"></i>
                    <?php else: ?>
                    <span class="text-gray-300">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right text-orange-600 font-medium" id="lit-<?= $idx ?>">
                    <span class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-right text-green-700 font-semibold" id="cos-<?= $idx ?>">
                    <span class="text-gray-300">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex flex-col gap-1.5 items-center">
                        <?php if ($traccarUrl && !empty($d['traccar_device_id'])): ?>
                        <a id="mapa-link-<?= $idx ?>"
                           href="<?= BASE_URL ?>/geolocalizacion?deviceId=<?= urlencode($d['traccar_device_id']) ?>&from=<?= urlencode($dateFrom) ?>&to=<?= urlencode($dateTo) ?>&name=<?= urlencode($d['nombre']) ?>"
                           title="Ver ruta en mapa para el periodo seleccionado"
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
                                   data-row-idx="<?= $idx ?>"
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
(function() {
    const BASE      = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
    const devices   = <?= $devicesJson ?>;
    const dateFrom  = <?= json_encode($dateFrom) ?>;
    const dateTo    = <?= json_encode($dateTo) ?>;
    const globalKmL = <?= json_encode($kmPorLitro) ?>;
    const precioL   = <?= json_encode($precioPorLitro) ?>;

    const TZ = <?= json_encode($timezone) ?>;
    const kmData = {};

    // Timezone offset (same as Geolocalización) so both views query the exact same GPS points
    function getTzOffsetStr(tz) {
        const now    = new Date();
        const utcMs  = new Date(now.toLocaleString('en-US', { timeZone: 'UTC' })).getTime();
        const tzMs   = new Date(now.toLocaleString('en-US', { timeZone: tz })).getTime();
        const offMin = (utcMs - tzMs) / 60000;
        const sign   = offMin >= 0 ? '-' : '+';
        const abs    = Math.abs(offMin);
        const hh     = String(Math.floor(abs / 60)).padStart(2, '0');
        const mm     = String(abs % 60).padStart(2, '0');
        return sign + hh + ':' + mm;
    }
    const tzOffset = getTzOffsetStr(TZ);

    // Auto-fix incorrect traccar_device_id in DB (fire-and-forget)
    function fixTraccarIdInDb(localDeviceId, realTraccarId) {
        const fd = new FormData();
        fd.append('device_id', localDeviceId);
        fd.append('real_traccar_id', realTraccarId);
        fetch(BASE + '/reportes-gps/fix-traccar-id', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => { if (d.ok) console.log('DB corregida para device', localDeviceId, '→', realTraccarId); })
            .catch(() => {});
    }

    function fmtDuration(ms) {
        if (!ms || ms <= 0) return '—';
        const totalSec = Math.floor(ms / 1000);
        const h = Math.floor(totalSec / 3600);
        const m = Math.floor((totalSec % 3600) / 60);
        return (h > 0 ? h + 'h ' : '') + m + 'min';
    }

    function updateTotals() {
        let totalKm = 0, totalLit = 0, totalCos = 0;
        for (const idx in kmData) {
            const d   = kmData[idx];
            totalKm  += d.km || 0;
            totalLit += d.litros || 0;
            totalCos += d.costo || 0;
        }
        document.getElementById('total-km').textContent     = totalKm.toFixed(1) + ' km';
        document.getElementById('total-litros').textContent  = totalLit > 0 ? totalLit.toFixed(1) + ' L' : '—';
        document.getElementById('total-costo').textContent   = totalCos > 0 ? '$' + totalCos.toFixed(2) : '—';
    }

    function calcFuel(idx) {
        const d = kmData[idx];
        if (!d || d.km === null) return;

        const deviceInfo = devices[idx];
        const kml = deviceInfo.km_por_litro || globalKmL;

        if (kml > 0 && d.km > 0) {
            d.litros = +(d.km / kml).toFixed(2);
            d.costo  = +(d.litros * precioL).toFixed(2);
        } else {
            d.litros = 0;
            d.costo  = 0;
        }

        const litCell = document.getElementById('lit-' + idx);
        const cosCell = document.getElementById('cos-' + idx);
        if (litCell) litCell.innerHTML = d.litros > 0
            ? '<span class="text-orange-600 font-medium">' + d.litros.toFixed(2) + ' L</span>'
            : '<span class="text-gray-300">—</span>';
        if (cosCell) cosCell.innerHTML = d.costo > 0
            ? '<span class="text-green-700 font-semibold">$' + d.costo.toFixed(2) + '</span>'
            : '<span class="text-gray-300">—</span>';

        updateTotals();
    }

    // Calculate distance from GPS points (Haversine R=6371000 — identical to Leaflet map.distance)
    function calcDistanceFromPositions(positions) {
        var d = 0, rad = Math.PI / 180;
        for (var i = 1; i < positions.length; i++) {
            var lat1 = positions[i-1].latitude, lon1 = positions[i-1].longitude;
            var lat2 = positions[i].latitude,   lon2 = positions[i].longitude;
            if (!lat1 || !lon1 || !lat2 || !lon2) continue;
            var sinDLat = Math.sin((lat2 - lat1) * rad / 2);
            var sinDLon = Math.sin((lon2 - lon1) * rad / 2);
            var a = sinDLat * sinDLat + Math.cos(lat1 * rad) * Math.cos(lat2 * rad) * sinDLon * sinDLon;
            d += 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }
        return d / 1000; // km
    }

    // Fetch route positions and calculate distance + max speed from raw data
    async function fetchRouteDistance(traccarId) {
        const from = dateFrom + 'T00:00:00' + tzOffset;
        const to   = dateTo   + 'T23:59:59' + tzOffset;
        const url  = BASE + '/geolocalizacion/ruta?deviceId=' + traccarId
                     + '&from=' + encodeURIComponent(from)
                     + '&to='   + encodeURIComponent(to);
        try {
            const res  = await fetch(url);
            const data = await res.json();
            console.log('Route response deviceId=' + traccarId, Array.isArray(data) ? data.length + ' puntos' : data);
            if (!Array.isArray(data) || data.length === 0) return null;
            if (data.error) { console.error('Route error:', data.error); return null; }
            const km = +calcDistanceFromPositions(data).toFixed(2);
            let maxSpeedKnots = 0;
            data.forEach(function(p) { if (p.speed > maxSpeedKnots) maxSpeedKnots = p.speed; });
            return { km: km, maxSpeed: +(maxSpeedKnots * 1.852).toFixed(1), points: data.length };
        } catch (e) {
            console.error('Error fetching route for device', traccarId, e);
            return null;
        }
    }

    let lastSeenInfo = {}; // traccar device id → device info (for last-seen dates)

    function delay(ms) { return new Promise(function(r) { setTimeout(r, ms); }); }

    async function fetchDeviceData(idx, traccarId) {
        const kmCell  = document.getElementById('km-'  + idx);
        const durCell = document.getElementById('dur-' + idx);
        const spdCell = document.getElementById('spd-' + idx);

        const from = dateFrom + 'T00:00:00' + tzOffset;
        const to   = dateTo   + 'T23:59:59' + tzOffset;

        try {
            if (kmCell) kmCell.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs text-indigo-300"></i> <span class="text-xs text-gray-400">cargando ruta...</span>';

            // Fetch route (primary source for km — same Haversine as Geolocalización map)
            const routeData = await fetchRouteDistance(traccarId);

            if (routeData && routeData.km > 0) {
                kmData[idx] = { km: routeData.km, litros: 0, costo: 0 };
                if (kmCell) kmCell.innerHTML = '<span class="text-indigo-700 font-semibold">' + routeData.km.toFixed(2) + '</span>';
                if (spdCell) spdCell.textContent = routeData.maxSpeed > 0 ? routeData.maxSpeed : '—';

                // Fetch summary in background for engineHours only
                await delay(400);
                try {
                    const summaryUrl = BASE + '/geolocalizacion/resumen?deviceId=' + traccarId
                                 + '&from=' + encodeURIComponent(from)
                                 + '&to='   + encodeURIComponent(to);
                    const sRes = await fetch(summaryUrl, { cache: 'no-store' });
                    const sData = await sRes.json();
                    const sObj = Array.isArray(sData) ? (sData[0] || null) : sData;
                    if (sObj && sObj.engineHours) {
                        if (durCell) durCell.textContent = fmtDuration(sObj.engineHours);
                    } else {
                        if (durCell) durCell.innerHTML = '<span class="text-gray-300">—</span>';
                    }
                } catch(e2) {
                    if (durCell) durCell.innerHTML = '<span class="text-gray-300">—</span>';
                }

                calcFuel(idx);
                return;
            }

            // Route empty → try summary as fallback (maybe device has odometer data but no route points)
            await delay(400);
            const summaryUrl = BASE + '/geolocalizacion/resumen?deviceId=' + traccarId
                         + '&from=' + encodeURIComponent(from)
                         + '&to='   + encodeURIComponent(to);
            const summaryRes = await fetch(summaryUrl, { cache: 'no-store' });
            const summaryData = await summaryRes.json();
            const summaryObj = Array.isArray(summaryData) ? (summaryData[0] || null) : summaryData;

            if (summaryObj && summaryObj.distance > 0) {
                const km = +(summaryObj.distance / 1000).toFixed(2);
                kmData[idx] = { km: km, litros: 0, costo: 0 };
                if (kmCell) kmCell.innerHTML = '<span class="text-indigo-700 font-semibold">' + km.toFixed(2) + ' <span class="text-xs text-gray-400">(odómetro)</span></span>';
                if (durCell) durCell.textContent = fmtDuration(summaryObj.engineHours || 0);
                if (spdCell) spdCell.textContent = summaryObj.maxSpeed > 0 ? +(summaryObj.maxSpeed * 1.852).toFixed(1) : '—';
                calcFuel(idx);
                return;
            }

            // No data in range → show last activity
            const info = lastSeenInfo[traccarId];
            if (info && info.lastUpdate) {
                const lastDate = new Date(info.lastUpdate);
                const dateStr  = lastDate.toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric' });
                if (kmCell) kmCell.innerHTML = '<span class="text-amber-500 text-xs"><i class="fa-solid fa-clock-rotate-left mr-1"></i>Última actividad: ' + dateStr + '</span>';
            } else {
                if (kmCell) kmCell.innerHTML = '<span class="text-gray-300 text-xs">Sin registros en Traccar</span>';
            }
            if (durCell) durCell.innerHTML = '<span class="text-gray-300">—</span>';
            if (spdCell) spdCell.innerHTML = '<span class="text-gray-300">—</span>';
            kmData[idx] = { km: null, litros: 0, costo: 0 };

        } catch (e) {
            console.error('Error fetching data for device', traccarId, e);
            if (kmCell) kmCell.innerHTML = '<span class="text-red-400 text-xs">Error</span>';
            if (durCell) durCell.innerHTML = '<span class="text-gray-300">—</span>';
            if (spdCell) spdCell.innerHTML = '<span class="text-gray-300">—</span>';
            kmData[idx] = { km: null, litros: 0, costo: 0 };
        }
    }

    // Fetch real Traccar device list, match by uniqueId (IMEI), then fetch summaries
    async function loadAll() {
        // Step 1: Get real device list from Traccar to resolve correct IDs
        let traccarMap = {};     // uniqueId (IMEI string) → real Traccar device id
        let traccarInfo = {};    // traccar device id → full device info
        try {
            const devRes = await fetch(BASE + '/geolocalizacion/dispositivos');
            const traccarDevices = await devRes.json();
            if (Array.isArray(traccarDevices)) {
                traccarDevices.forEach(function(td) {
                    if (td.uniqueId && td.id) {
                        traccarMap[String(td.uniqueId).trim()] = td.id;
                        traccarMap[String(td.uniqueId).replace(/\s+/g, '')] = td.id;
                        traccarInfo[td.id] = td;
                    }
                });
            }
            console.log('Traccar dispositivos mapeados:', Object.keys(traccarMap).length, traccarMap);
        } catch (e) {
            console.error('Error fetching Traccar devices:', e);
        }

        lastSeenInfo = traccarInfo;

        // Step 2: Resolve real Traccar IDs for all local devices
        // Build set of valid internal IDs to distinguish from uniqueIds
        const validInternalIds = new Set(Object.values(traccarMap));

        const deviceIdMap = []; // [{idx, device, traccarId}]
        for (let idx = 0; idx < devices.length; idx++) {
            const d = devices[idx];
            const uniqueClean = d.unique_id ? d.unique_id.replace(/\s+/g, '') : '';
            let realTraccarId = traccarMap[uniqueClean] || traccarMap[d.unique_id] || null;

            if (!realTraccarId && d.traccar_device_id > 0) {
                const dbVal = d.traccar_device_id;
                if (validInternalIds.has(dbVal)) {
                    // DB has a correct internal ID
                    realTraccarId = dbVal;
                } else if (traccarMap[String(dbVal)]) {
                    // DB stored a Traccar uniqueId instead of internal ID — resolve it
                    realTraccarId = traccarMap[String(dbVal)];
                    console.warn('Device', d.unique_id, '→ BD tiene uniqueId', dbVal,
                                 'en traccar_device_id, ID interno real:', realTraccarId);
                } else {
                    // Unknown value, try it anyway
                    realTraccarId = dbVal;
                }
            }

            if (realTraccarId) {
                if (realTraccarId !== d.traccar_device_id) {
                    console.log('Device', d.unique_id, '→ Traccar ID:', realTraccarId,
                                '(CORREGIDO, BD tenía: ' + d.traccar_device_id + ')');
                    fixTraccarIdInDb(d.id, realTraccarId);
                } else {
                    console.log('Device', d.unique_id, '→ Traccar ID:', realTraccarId, '(OK)');
                }

                const mapaLink = document.getElementById('mapa-link-' + idx);
                if (mapaLink) {
                    mapaLink.href = BASE + '/geolocalizacion?deviceId=' + realTraccarId
                        + '&from=' + encodeURIComponent(dateFrom)
                        + '&to='   + encodeURIComponent(dateTo)
                        + '&name=' + encodeURIComponent(d.unique_id);
                }

                deviceIdMap.push({ idx: idx, device: d, traccarId: realTraccarId });
            } else {
                console.warn('Device', d.unique_id, '→ No se encontró en Traccar');
                const kmCell = document.getElementById('km-' + idx);
                if (kmCell) kmCell.innerHTML = '<span class="text-gray-300 text-xs">No encontrado en Traccar</span>';
            }
        }

        // Step 3: Fetch summary for each device ONE BY ONE with delay
        // Wait 1.5s after devices fetch to avoid rate-limiting
        console.log('Esperando 1.5s antes de iniciar resúmenes...');
        await delay(1500);

        for (let i = 0; i < deviceIdMap.length; i++) {
            const entry = deviceIdMap[i];
            console.log('── Dispositivo ' + (i+1) + '/' + deviceIdMap.length + ': traccarId=' + entry.traccarId + ' ──');
            await fetchDeviceData(entry.idx, entry.traccarId);
            // Wait between requests to avoid rate limiting
            if (i < deviceIdMap.length - 1) {
                await delay(1200);
            }
        }
        updateTotals();
    }

    if (devices.length > 0) {
        loadAll();
    } else {
        document.getElementById('total-km').textContent    = '0.0 km';
        document.getElementById('total-litros').textContent = '—';
        document.getElementById('total-costo').textContent  = '—';
    }

    window.saveKml = async function(btn) {
        const wrap     = btn.closest('div');
        const input    = wrap.querySelector('.kml-input');
        const deviceId = input ? input.dataset.deviceId : null;
        const rowIdx   = input ? input.dataset.rowIdx : null;
        if (!deviceId) return;

        const formData = new FormData();
        formData.append('device_id',    deviceId);
        formData.append('km_por_litro', input.value);

        btn.disabled = true;
        try {
            const res  = await fetch(BASE + '/reportes-gps/guardar-km', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.ok) {
                input.classList.add('border-green-400');
                setTimeout(function() { input.classList.remove('border-green-400'); }, 1500);
                // Update local device km/L and recalculate
                if (rowIdx !== null && devices[rowIdx]) {
                    devices[rowIdx].km_por_litro = input.value !== '' ? parseFloat(input.value) : null;
                    calcFuel(parseInt(rowIdx));
                }
            } else {
                alert('Error: ' + (data.error || 'No se pudo guardar'));
            }
        } catch (e) {
            alert('Error de conexión al guardar km/L: ' + e.message);
        } finally {
            btn.disabled = false;
        }
    };
})();
</script>
