<?php
// Inline styles for the Geozonas layout
?>
<style>
  #gz-map { height: calc(100vh - 200px); min-height: 420px; border-radius: 0 0.75rem 0.75rem 0; }
  .gz-sidebar { height: calc(100vh - 200px); min-height: 420px; overflow-y: auto; }
  .gz-item { display:flex; align-items:center; justify-content:space-between; padding:0.625rem 1rem;
             border-bottom:1px solid #f1f5f9; cursor:pointer; transition:background .15s; }
  .gz-item:hover { background:#f8fafc; }
  .gz-item.active { background:#eef2ff; }
  .gz-item h4 { font-size:0.875rem; font-weight:600; color:#1e293b; margin:0; }
  .gz-item p  { font-size:0.75rem; color:#64748b; margin:2px 0 0; }
  #no-traccar-gz { background:#fef3c7; border:1px solid #fbbf24; border-radius:8px;
                   padding:10px 18px; font-size:0.83rem; color:#92400e; text-align:center; margin-bottom:12px; }
  .spinner { display:inline-block; width:18px; height:18px; border:2px solid #e2e8f0;
             border-top-color:#4f46e5; border-radius:50%; animation:spin .7s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-3">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-draw-polygon text-indigo-600 text-xl"></i>
        <span class="font-semibold text-gray-700">Geozonas</span>
    </div>
    <div class="flex gap-2">
        <button onclick="loadGeozonas()"
                class="flex items-center gap-2 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-rotate"></i> Actualizar
        </button>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <button onclick="toggleForm()"
                class="flex items-center gap-2 px-3 py-1.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Nueva Geozona
        </button>
        <?php endif; ?>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/configuracion?tab=gps"
           class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
            <i class="fa-solid fa-gear"></i> Configurar Traccar
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($traccarUrl)): ?>
<div id="no-traccar-gz">
    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
    Servidor Traccar no configurado. Las geozonas se obtienen desde Traccar.
    <a href="<?= BASE_URL ?>/configuracion?tab=gps" class="underline font-semibold ml-1">Configurar</a>
</div>
<?php endif; ?>

<!-- Create form (initially hidden) -->
<?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
<div id="create-form" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4">
    <h3 class="font-semibold text-gray-700 mb-3 text-sm">
        <i class="fa-solid fa-draw-polygon mr-1 text-indigo-500"></i> Crear Nueva Geozona en Traccar
    </h3>
    <form method="POST" action="<?= BASE_URL ?>/geozonas/guardar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                <input type="text" name="nombre" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Ej. Perímetro Norte">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                <input type="text" name="descripcion"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Descripción opcional">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Área (WKT) *
                    <span class="text-gray-400 font-normal ml-1">
                        — Ej: <code class="text-xs bg-gray-100 px-1 rounded">POLYGON((lng lat, lng lat, ...))</code>
                        o <code class="text-xs bg-gray-100 px-1 rounded">CIRCLE(lat lng, radio_metros)</code>
                    </span>
                </label>
                <textarea name="area" id="gz-area" rows="2" required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="CIRCLE(20.5888 -100.3899, 500)"></textarea>
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Dibuja en el mapa y copia las coordenadas, o ingresa directamente el área WKT de Traccar.
                </p>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-floppy-disk mr-1"></i>Crear Geozona
            </button>
            <button type="button" onclick="toggleForm()"
                    class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition-colors">
                Cancelar
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Main layout: sidebar + map -->
<div class="flex rounded-xl overflow-hidden shadow border border-gray-200 bg-white">
    <!-- Sidebar: list of geofences -->
    <div class="gz-sidebar w-72 flex-shrink-0 border-r border-gray-100">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Geozonas</span>
            <span id="gz-count" class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-0.5 rounded-full">0</span>
        </div>
        <div id="gz-list">
            <div class="flex justify-center py-10 text-gray-300">
                <span class="spinner"></span>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="flex-1">
        <div id="gz-map"></div>
    </div>
</div>

<!-- Edit Geozona Modal -->
<?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
<div id="edit-gz-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
        <h3 class="font-semibold text-gray-700 mb-4 text-sm">
            <i class="fa-solid fa-pen mr-1 text-indigo-500"></i> Editar Geozona
        </h3>
        <form method="POST" action="<?= BASE_URL ?>/geozonas/actualizar" id="edit-gz-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" id="edit-gz-id">
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre *</label>
                    <input type="text" name="nombre" id="edit-gz-nombre" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Descripción</label>
                    <input type="text" name="descripcion" id="edit-gz-desc"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Área (WKT)</label>
                    <textarea name="area" id="edit-gz-area" rows="2" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar cambios
                </button>
                <button type="button" onclick="closeEditForm()"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
const BASE_URL    = '<?= BASE_URL ?>';
const TRACCAR_URL = <?= json_encode($traccarUrl) ?>;

// ── Map init ─────────────────────────────────────────────────────────────────
const map = L.map('gz-map').setView([20.5888, -100.3899], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
    maxZoom: 19,
}).addTo(map);

let gzLayers = {};  // traccar_id → leaflet layer

// ── Load geofences ────────────────────────────────────────────────────────────
async function loadGeozonas() {
    if (!TRACCAR_URL) {
        renderEmptyList('Configure el servidor Traccar para ver las geozonas.');
        return;
    }
    try {
        const res  = await fetch(BASE_URL + '/geozonas/listar');
        const data = await res.json();

        if (data.error) {
            renderEmptyList(data.error);
            return;
        }

        document.getElementById('gz-count').textContent = data.length;
        renderList(data);
        renderGeofencesOnMap(data);
    } catch (e) {
        renderEmptyList('Error de red al cargar geozonas.');
    }
}

function renderList(geofences) {
    const list = document.getElementById('gz-list');
    if (!geofences.length) {
        list.innerHTML = '<p class="text-center text-sm text-gray-400 py-10">Sin geozonas registradas en Traccar</p>';
        return;
    }
    list.innerHTML = geofences.map(g => `
        <div class="gz-item" onclick="focusGeofence(${g.id})">
            <div>
                <h4><i class="fa-solid fa-draw-polygon mr-1 text-indigo-400 text-xs"></i>${escHtml(g.name)}</h4>
                ${g.description ? `<p>${escHtml(g.description)}</p>` : ''}
                <p class="font-mono text-[10px] text-gray-300 mt-0.5">ID Traccar: ${g.id}</p>
            </div>
            <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
            <div class="flex items-center gap-1 ml-2" onclick="event.stopPropagation()">
                <button onclick="openEditForm(${g.id}, ${JSON.stringify(g.name)}, ${JSON.stringify(g.description||'')}, ${JSON.stringify(g.area||'')})"
                   class="text-indigo-400 hover:text-indigo-600 p-1" title="Editar">
                    <i class="fa-solid fa-pen text-xs"></i>
                </button>
                <a href="${BASE_URL}/geozonas/eliminar/${g.id}"
                   onclick="return confirm('¿Eliminar la geozona «' + escHtml(g.name) + '»?')"
                   class="text-red-400 hover:text-red-600 p-1" title="Eliminar">
                    <i class="fa-solid fa-trash text-xs"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    `).join('');
}

function renderEmptyList(msg) {
    document.getElementById('gz-list').innerHTML =
        `<p class="text-center text-sm text-gray-400 py-10 px-4">${escHtml(msg)}</p>`;
}

// ── Render geofences on map ───────────────────────────────────────────────────
function renderGeofencesOnMap(geofences) {
    // Remove old layers
    Object.values(gzLayers).forEach(l => map.removeLayer(l));
    gzLayers = {};

    geofences.forEach(g => {
        const layer = parseAndDraw(g);
        if (layer) {
            layer.bindTooltip(escHtml(g.name), { permanent: false, direction: 'top' });
            layer.addTo(map);
            gzLayers[g.id] = layer;
        }
    });

    // Fit bounds to all layers
    const layers = Object.values(gzLayers);
    if (layers.length > 0) {
        const group = L.featureGroup(layers);
        map.fitBounds(group.getBounds().pad(0.2));
    }
}

function focusGeofence(id) {
    const layer = gzLayers[id];
    if (!layer) return;
    if (layer.getBounds) {
        map.fitBounds(layer.getBounds().pad(0.3));
    } else if (layer.getLatLng) {
        map.setView(layer.getLatLng(), 15);
    }
    document.querySelectorAll('.gz-item').forEach(el => el.classList.remove('active'));
    // highlight the clicked item
    event && event.currentTarget && event.currentTarget.classList.add('active');
}

/**
 * Parse Traccar WKT area string and draw a Leaflet layer.
 * Supports: CIRCLE(lat lng, radius), POLYGON((lng lat, ...)), LINESTRING(lng lat, ...)
 */
function parseAndDraw(g) {
    const area = (g.area || '').trim();
    if (!area) return null;

    // CIRCLE(lat lng, radius)
    const circleMatch = area.match(/^CIRCLE\s*\(\s*([-\d.]+)\s+([-\d.]+)\s*,\s*([\d.]+)\s*\)$/i);
    if (circleMatch) {
        const lat = parseFloat(circleMatch[1]);
        const lng = parseFloat(circleMatch[2]);
        const rad = parseFloat(circleMatch[3]);
        return L.circle([lat, lng], { radius: rad, color: '#4f46e5', fillOpacity: 0.15, weight: 2 });
    }

    // POLYGON((lat lng, ...)) — Traccar stores coordinates as lat lng (non-standard WKT)
    const polyMatch = area.match(/^POLYGON\s*\(\s*\((.+)\)\s*\)$/i);
    if (polyMatch) {
        const coords = polyMatch[1].split(',').map(pair => {
            const parts = pair.trim().split(/\s+/);
            return [parseFloat(parts[0]), parseFloat(parts[1])]; // [lat, lng] — Traccar lat-lng order
        });
        return L.polygon(coords, { color: '#4f46e5', fillOpacity: 0.15, weight: 2 });
    }

    return null;
}

function toggleForm() {
    const f = document.getElementById('create-form');
    f.classList.toggle('hidden');
}

function openEditForm(id, name, desc, area) {
    document.getElementById('edit-gz-id').value    = id;
    document.getElementById('edit-gz-nombre').value = name;
    document.getElementById('edit-gz-desc').value   = desc;
    document.getElementById('edit-gz-area').value   = area;
    document.getElementById('edit-gz-modal').classList.remove('hidden');
}

function closeEditForm() {
    document.getElementById('edit-gz-modal').classList.add('hidden');
}

function escHtml(s) {
    return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

document.addEventListener('DOMContentLoaded', loadGeozonas);
</script>
