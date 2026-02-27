<?php
// Inline styles for full-height map within the layout's <main>
?>
<style>
  #geo-map { height: calc(100vh - 160px); min-height: 400px; border-radius: 0.75rem; }
  .leaflet-popup-content { font-size: 0.85rem; min-width: 220px; }
  .leaflet-popup-content h4 { margin: 0 0 8px; font-weight: 700; font-size: 1rem; color: #1e293b; }
  .leaflet-popup-content table { width: 100%; border-collapse: collapse; }
  .leaflet-popup-content td { padding: 3px 4px; vertical-align: top; }
  .leaflet-popup-content td:first-child { color: #64748b; white-space: nowrap; padding-right: 8px; }
  .popup-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
  .popup-btn { padding: 5px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;
               cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 4px; }
  .popup-btn-blue   { background:#4f46e5; color:#fff; }
  .popup-btn-green  { background:#059669; color:#fff; }
  .popup-btn-gray   { background:#e2e8f0; color:#334155; }
  .popup-btn:hover  { opacity:0.85; }
  #route-panel { position:absolute; bottom:16px; left:50%; transform:translateX(-50%);
                 background:#fff; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.15);
                 padding:14px 18px; z-index:1000; min-width:280px; display:none; }
  #route-panel h5 { margin:0 0 8px; font-weight:700; color:#1e293b; font-size:0.9rem; }
  #route-panel .close-btn { position:absolute; top:8px; right:10px; background:none; border:none;
                             font-size:1.2rem; cursor:pointer; color:#94a3b8; }
  .spinner { display:inline-block; width:18px; height:18px; border:2px solid #e2e8f0;
             border-top-color:#4f46e5; border-radius:50%; animation:spin .7s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  #no-traccar-banner { position:absolute; top:60px; left:50%; transform:translateX(-50%);
                       background:#fef3c7; border:1px solid #fbbf24; border-radius:8px;
                       padding:10px 18px; z-index:1000; font-size:0.83rem; color:#92400e;
                       display:none; max-width:380px; text-align:center; }
</style>

<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-3">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-location-dot text-indigo-600 text-xl"></i>
        <span class="font-semibold text-gray-700">Mapa de Dispositivos GPS</span>
        <span id="device-count-badge"
              class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-0.5 rounded-full">
            <?= count($devices) ?> registrados
        </span>
    </div>
    <div class="flex gap-2">
        <button onclick="loadPositions()" id="btn-refresh"
                class="flex items-center gap-2 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-rotate"></i> Actualizar
        </button>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/configuracion?tab=gps"
           class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
            <i class="fa-solid fa-gear"></i> Configurar Traccar
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Map container -->
<div class="relative rounded-xl overflow-hidden shadow border border-gray-200">
    <div id="no-traccar-banner">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
        Servidor Traccar no configurado.
        <a href="<?= BASE_URL ?>/configuracion?tab=gps" class="underline font-semibold ml-1">Configurar</a>
    </div>
    <div id="geo-map"></div>

    <!-- Route info panel (overlaid on map) -->
    <div id="route-panel">
        <button class="close-btn" onclick="closeRoutePanel()">×</button>
        <h5 id="route-title">Ruta</h5>
        <div id="route-body">
            <span class="spinner"></span> Cargando…
        </div>
    </div>
</div>

<?php if (empty($traccarUrl)): ?>
<!-- Devices registered locally (no Traccar connection) -->
<?php if (!empty($devices)): ?>
<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h3 class="font-semibold text-gray-700 mb-3 text-sm">
        <i class="fa-solid fa-list mr-1 text-gray-400"></i> Dispositivos GPS Registrados (sin conexión Traccar)
    </h3>
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs text-gray-600">
            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-wider">
                <tr>
                    <th class="px-3 py-2 text-left">Activo</th>
                    <th class="px-3 py-2 text-left">Nombre Dispositivo</th>
                    <th class="px-3 py-2 text-left">Unique ID</th>
                    <th class="px-3 py-2 text-left">Categoría</th>
                    <th class="px-3 py-2 text-left">Traccar ID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($devices as $d): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        <span class="font-mono text-gray-400"><?= htmlspecialchars($d['activo_codigo'], ENT_QUOTES,'UTF-8') ?></span>
                        <?= htmlspecialchars($d['activo_nombre'], ENT_QUOTES,'UTF-8') ?>
                    </td>
                    <td class="px-3 py-2 font-medium text-gray-800"><?= htmlspecialchars($d['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-3 py-2 font-mono"><?= htmlspecialchars($d['unique_id'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($d['categoria_traccar'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-3 py-2"><?= $d['traccar_device_id'] ? '#'.(int)$d['traccar_device_id'] : '<span class="text-gray-300">—</span>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Leaflet CSS/JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
const BASE_URL       = '<?= BASE_URL ?>';
const TRACCAR_URL    = <?= json_encode($traccarUrl) ?>;
const LOCAL_DEVICES  = <?= json_encode($devices) ?>;
const TZ             = <?= json_encode($timezone ?? 'America/Mexico_City') ?>;

// ── Map init ─────────────────────────────────────────────────────────────────
const map = L.map('geo-map', { zoomControl: true }).setView([20.5888, -100.3899], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
    maxZoom: 19,
}).addTo(map);

// ── State ─────────────────────────────────────────────────────────────────────
const markers       = {};   // deviceId → marker
let   routeLayer    = null;
let   activeDeviceId = null;

// ── Icons ─────────────────────────────────────────────────────────────────────
function makeIcon(color) {
    const svgOnline = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="40">
        <path fill="${color}" stroke="#fff" stroke-width="1.5"
              d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
        <circle cx="12" cy="9" r="3" fill="#fff"/>
    </svg>`;
    return L.divIcon({
        html: svgOnline,
        className: '',
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        popupAnchor: [0, -38],
    });
}

const iconOnline  = makeIcon('#22c55e');
const iconOffline = makeIcon('#94a3b8');
const iconUnknown = makeIcon('#f59e0b');

function statusIcon(status) {
    if (status === 'online')  return iconOnline;
    if (status === 'offline') return iconOffline;
    return iconUnknown;
}

// Build a mapping of traccar_device_id → local device info
const localDeviceMap = {};
LOCAL_DEVICES.forEach(d => {
    if (d.traccar_device_id) localDeviceMap[d.traccar_device_id] = d;
});

// ── Load positions from Traccar via PHP proxy ─────────────────────────────────
async function loadPositions() {
    if (!TRACCAR_URL) {
        document.getElementById('no-traccar-banner').style.display = 'block';
        // If no Traccar, place markers for local devices at default coords
        placeLocalFallbackMarkers();
        return;
    }
    document.getElementById('no-traccar-banner').style.display = 'none';
    const btn = document.getElementById('btn-refresh');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="border-top-color:#fff"></span> Actualizando…';

    try {
        const [posRes, devRes] = await Promise.all([
            fetch(BASE_URL + '/geolocalizacion/posiciones'),
            fetch(BASE_URL + '/geolocalizacion/dispositivos'),
        ]);
        const positions = await posRes.json();
        const devices   = await devRes.json();

        if (positions.error || devices.error) {
            document.getElementById('no-traccar-banner').style.display = 'block';
            document.getElementById('no-traccar-banner').innerHTML =
                '<i class="fa-solid fa-triangle-exclamation mr-1"></i>' +
                (positions.error || devices.error) +
                ' <a href="' + BASE_URL + '/configuracion?tab=gps" class="underline font-semibold ml-1">Configurar</a>';
            placeLocalFallbackMarkers();
            return;
        }

        // Build device info map
        const deviceMap = {};
        (devices || []).forEach(d => { deviceMap[d.id] = d; });

        // Place / update markers
        (positions || []).forEach(pos => {
            if (!pos.latitude || !pos.longitude) return;
            const dev   = deviceMap[pos.deviceId] || {};
            const local = localDeviceMap[pos.deviceId] || {};
            const name  = dev.name || local.nombre || ('Device #' + pos.deviceId);
            const latlng = [pos.latitude, pos.longitude];

            if (markers[pos.deviceId]) {
                markers[pos.deviceId].setLatLng(latlng);
                markers[pos.deviceId].setIcon(statusIcon(dev.status));
            } else {
                const m = L.marker(latlng, { icon: statusIcon(dev.status) }).addTo(map);
                m.on('click', () => openDevicePopup(m, pos, dev, local));
                markers[pos.deviceId] = m;
            }
        });

        // Fit map to markers
        const allMarkers = Object.values(markers);
        if (allMarkers.length > 0) {
            const group = L.featureGroup(allMarkers);
            map.fitBounds(group.getBounds().pad(0.2));
        }
    } catch (e) {
        console.error('Error loading positions:', e);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Actualizar';
    }
}

// Fallback: show local devices without position data
function placeLocalFallbackMarkers() {
    if (!LOCAL_DEVICES.length) return;
    LOCAL_DEVICES.forEach((d, i) => {
        const id = d.traccar_device_id || ('local_' + d.id);
        if (markers[id]) return;
        // Spread markers around Querétaro with small offset
        const lat = 20.5888 + (i * 0.003);
        const lng = -100.3899 + (i * 0.003);
        const m = L.marker([lat, lng], { icon: iconUnknown }).addTo(map);
        m.on('click', () => openLocalPopup(m, d));
        markers[id] = m;
    });
}

// ── Popup for Traccar device ──────────────────────────────────────────────────
function openDevicePopup(marker, pos, dev, local) {
    activeDeviceId = pos.deviceId;
    const speed   = pos.speed ? (pos.speed * 1.852).toFixed(1) + ' km/h' : '0.0 km/h';
    const updated = dev.lastUpdate ? new Date(dev.lastUpdate).toLocaleString('es-MX', { timeZone: TZ }) : '—';
    const status  = dev.status === 'online' ? '<span style="color:#22c55e">● En línea</span>'
                  : dev.status === 'offline' ? '<span style="color:#94a3b8">● Sin conexión</span>'
                  : '<span style="color:#f59e0b">● Desconocido</span>';
    const name = dev.name || local.nombre || ('Dispositivo #' + pos.deviceId);
    const assetLink = local.activo_codigo
        ? `<a href="${BASE_URL}/inventario" style="color:#4f46e5;font-size:0.78rem">${local.activo_codigo} — ${local.activo_nombre || ''}</a>`
        : '';

    const html = `
        <h4><i class="fa-solid fa-location-dot" style="color:#4f46e5;margin-right:4px"></i>${escHtml(name)}</h4>
        ${assetLink ? `<div style="margin-bottom:8px">${assetLink}</div>` : ''}
        <table>
            <tr><td>Estado</td><td>${status}</td></tr>
            <tr><td>Última actualización</td><td>${updated}</td></tr>
            <tr><td>Velocidad</td><td>${speed}</td></tr>
            <tr><td>Coordenadas</td><td>${pos.latitude.toFixed(5)}°, ${pos.longitude.toFixed(5)}°</td></tr>
            ${pos.altitude ? `<tr><td>Altitud</td><td>${pos.altitude.toFixed(0)} m</td></tr>` : ''}
            ${dev.model ? `<tr><td>Modelo</td><td>${escHtml(dev.model)}</td></tr>` : ''}
        </table>
        <div class="popup-actions">
            <button class="popup-btn popup-btn-blue" onclick="loadTodayRoute(${pos.deviceId}, '${escHtml(name)}')">
                <i class="fa-solid fa-route"></i> Ruta de hoy
            </button>
            <button class="popup-btn popup-btn-green" onclick="openHistoryModal(${pos.deviceId}, '${escHtml(name)}')">
                <i class="fa-solid fa-calendar-days"></i> Historial
            </button>
            ${TRACCAR_URL ? `<button class="popup-btn" style="background:#7c3aed;color:#fff" onclick="openReplay(${pos.deviceId}, '${escHtml(name)}')">
                <i class="fa-solid fa-play"></i> Repetición Ruta
            </button>` : ''}
            <button class="popup-btn popup-btn-gray" onclick="map.closePopup()">
                <i class="fa-solid fa-xmark"></i> Cerrar
            </button>
        </div>`;

    marker.bindPopup(html, { maxWidth: 320 }).openPopup();
}

// Popup for local device without Traccar position
function openLocalPopup(marker, d) {
    const html = `
        <h4><i class="fa-solid fa-location-dot" style="color:#f59e0b;margin-right:4px"></i>${escHtml(d.nombre)}</h4>
        <table>
            <tr><td>Activo</td><td>${escHtml(d.activo_codigo||'')} ${escHtml(d.activo_nombre||'')}</td></tr>
            <tr><td>Unique ID</td><td>${escHtml(d.unique_id||'')}</td></tr>
            <tr><td>Categoría</td><td>${escHtml(d.categoria_traccar||'')}</td></tr>
        </table>
        <p style="color:#f59e0b;font-size:0.78rem;margin-top:8px">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Sin posición en tiempo real (configure el servidor Traccar)
        </p>`;
    marker.bindPopup(html, { maxWidth: 280 }).openPopup();
}

// ── Timezone helper ───────────────────────────────────────────────────────────
// Returns the UTC offset string (e.g. "-06:00") for the configured timezone
function getTzOffsetStr(tz) {
    const now    = new Date();
    const utcMs  = new Date(now.toLocaleString('en-US', { timeZone: 'UTC' })).getTime();
    const tzMs   = new Date(now.toLocaleString('en-US', { timeZone: tz })).getTime();
    const offMin = (utcMs - tzMs) / 60000; // positive = behind UTC
    const sign   = offMin >= 0 ? '-' : '+';
    const abs    = Math.abs(offMin);
    const hh     = String(Math.floor(abs / 60)).padStart(2, '0');
    const mm     = String(abs % 60).padStart(2, '0');
    return sign + hh + ':' + mm;
}

// ── Route: today ─────────────────────────────────────────────────────────────
async function loadTodayRoute(deviceId, deviceName) {
    map.closePopup();
    // Get today's date in configured timezone
    const today  = new Date().toLocaleDateString('en-CA', { timeZone: TZ }); // YYYY-MM-DD
    const offset = getTzOffsetStr(TZ);
    const from   = today + 'T00:00:00' + offset;
    const to     = today + 'T23:59:59' + offset;
    showRoutePanel(deviceName, '<span class="spinner"></span> Cargando ruta de hoy…');

    try {
        const res  = await fetch(`${BASE_URL}/geolocalizacion/ruta?deviceId=${deviceId}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
        const data = await res.json();
        const label = 'Ruta de hoy — ' + new Date().toLocaleDateString('es-MX', { timeZone: TZ });
        drawRoute(data, deviceName, label);
    } catch (e) {
        showRoutePanel(deviceName, '<span style="color:red">Error al cargar la ruta.</span>');
    }
}

// ── Route Replay (Traccar web) ────────────────────────────────────────────────
function openReplay(deviceId, deviceName) {
    map.closePopup();
    const today = new Date().toLocaleDateString('en-CA', { timeZone: TZ }); // YYYY-MM-DD
    const from  = today + 'T00:00:00.000Z';
    const to    = today + 'T23:59:59.000Z';
    const replayUrl = TRACCAR_URL + '/replay?deviceId=' + deviceId
        + '&from=' + encodeURIComponent(from)
        + '&to='   + encodeURIComponent(to);

    const body = `
        <div style="font-size:0.82rem;color:#374151">
            <div style="margin-bottom:8px"><strong>${escHtml(deviceName)}</strong></div>
            <div style="display:flex;gap:8px;margin-bottom:10px">
                <div>
                    <label style="color:#64748b;font-size:0.78rem">Desde</label><br>
                    <input type="date" id="replay-from" value="${today}" max="${today}"
                           style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.82rem">
                </div>
                <div>
                    <label style="color:#64748b;font-size:0.78rem">Hasta</label><br>
                    <input type="date" id="replay-to" value="${today}" max="${today}"
                           style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.82rem">
                </div>
            </div>
            <a id="replay-link" href="${replayUrl}" target="_blank"
               class="popup-btn" style="background:#7c3aed;color:#fff;display:inline-flex;text-decoration:none">
                <i class="fa-solid fa-play"></i> Abrir Repetición Ruta
            </a>
        </div>`;
    showRoutePanel('Repetición Ruta — ' + deviceName, body);

    // Update link dynamically when dates change
    ['replay-from', 'replay-to'].forEach(id => {
        setTimeout(() => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => {
                const f = (document.getElementById('replay-from')?.value || today) + 'T00:00:00.000Z';
                const t = (document.getElementById('replay-to')?.value   || today) + 'T23:59:59.000Z';
                const link = document.getElementById('replay-link');
                if (link) link.href = TRACCAR_URL + '/replay?deviceId=' + deviceId
                    + '&from=' + encodeURIComponent(f)
                    + '&to='   + encodeURIComponent(t);
            });
        }, 50);
    });
}

// ── History modal ─────────────────────────────────────────────────────────────
function openHistoryModal(deviceId, deviceName) {
    map.closePopup();
    const today = new Date().toLocaleDateString('en-CA', { timeZone: TZ }); // YYYY-MM-DD
    const html = `
        <div style="font-size:0.83rem">
            <div style="display:flex;gap:8px;margin-bottom:8px">
                <div>
                    <label style="color:#64748b">Desde</label><br>
                    <input type="date" id="hist-from" value="${today}" max="${today}"
                           style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.82rem">
                </div>
                <div>
                    <label style="color:#64748b">Hasta</label><br>
                    <input type="date" id="hist-to"   value="${today}" max="${today}"
                           style="border:1px solid #d1d5db;border-radius:6px;padding:4px 8px;font-size:0.82rem">
                </div>
            </div>
            <button class="popup-btn popup-btn-blue" onclick="loadHistoryRoute(${deviceId}, '${escHtml(deviceName)}')">
                <i class="fa-solid fa-route"></i> Mostrar Ruta
            </button>
        </div>`;
    showRoutePanel('Historial — ' + deviceName, html);
}

async function loadHistoryRoute(deviceId, deviceName) {
    const today  = new Date().toLocaleDateString('en-CA', { timeZone: TZ });
    const offset = getTzOffsetStr(TZ);
    const from = (document.getElementById('hist-from')?.value || today) + 'T00:00:00' + offset;
    const to   = (document.getElementById('hist-to')?.value   || today) + 'T23:59:59' + offset;
    showRoutePanel('Historial — ' + deviceName, '<span class="spinner"></span> Cargando historial…');

    try {
        const res  = await fetch(`${BASE_URL}/geolocalizacion/ruta?deviceId=${deviceId}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`);
        const data = await res.json();
        const label = 'Historial ' + from.split('T')[0] + ' → ' + to.split('T')[0];
        drawRoute(data, deviceName, label);
    } catch (e) {
        showRoutePanel(deviceName, '<span style="color:red">Error al cargar el historial.</span>');
    }
}

// ── Draw route on map ─────────────────────────────────────────────────────────
function drawRoute(positions, deviceName, label) {
    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }

    if (!Array.isArray(positions) || positions.length === 0 || positions.error) {
        showRoutePanel(label, `<span style="color:#64748b">Sin posiciones para este periodo.</span>
            <div style="margin-top:6px">
                <button class="popup-btn popup-btn-gray" onclick="closeRoutePanel()">Cerrar</button>
            </div>`);
        return;
    }

    const latlngs = positions
        .filter(p => p.latitude && p.longitude)
        .map(p => [p.latitude, p.longitude]);

    if (latlngs.length === 0) {
        showRoutePanel(label, '<span style="color:#64748b">Sin posiciones válidas.</span>');
        return;
    }

    routeLayer = L.layerGroup().addTo(map);

    // Route line
    L.polyline(latlngs, { color: '#4f46e5', weight: 4, opacity: 0.8 }).addTo(routeLayer);

    // Start marker (green)
    L.circleMarker(latlngs[0], { radius: 7, color: '#22c55e', fillColor: '#22c55e', fillOpacity: 1 })
     .bindTooltip('Inicio').addTo(routeLayer);

    // End marker (red)
    L.circleMarker(latlngs[latlngs.length - 1], { radius: 7, color: '#ef4444', fillColor: '#ef4444', fillOpacity: 1 })
     .bindTooltip('Fin').addTo(routeLayer);

    map.fitBounds(L.polyline(latlngs).getBounds().pad(0.15));

    const dist = calcDistance(latlngs).toFixed(2);
    const body = `
        <div style="font-size:0.82rem;color:#374151">
            <div><i class="fa-solid fa-route" style="color:#4f46e5;margin-right:4px"></i>
                 <strong>${positions.length}</strong> puntos registrados</div>
            <div style="margin-top:4px"><i class="fa-solid fa-road" style="color:#059669;margin-right:4px"></i>
                 Distancia aproximada: <strong>${dist} km</strong></div>
        </div>
        <div style="margin-top:8px;display:flex;gap:6px">
            <button class="popup-btn popup-btn-gray" onclick="clearRoute()">
                <i class="fa-solid fa-eraser"></i> Limpiar ruta
            </button>
        </div>`;
    showRoutePanel(label, body);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function showRoutePanel(title, body) {
    const panel = document.getElementById('route-panel');
    document.getElementById('route-title').textContent = title;
    document.getElementById('route-body').innerHTML   = body;
    panel.style.display = 'block';
}

function closeRoutePanel() {
    document.getElementById('route-panel').style.display = 'none';
}

function clearRoute() {
    if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
    closeRoutePanel();
}

function calcDistance(latlngs) {
    let d = 0;
    for (let i = 1; i < latlngs.length; i++) {
        d += map.distance(latlngs[i - 1], latlngs[i]);
    }
    return d / 1000;
}

function escHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ── Auto-load ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadPositions);

// Auto-refresh every 30 seconds
setInterval(loadPositions, 30000);
</script>
