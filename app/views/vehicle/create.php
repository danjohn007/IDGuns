<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/vehiculos" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Registrar Nuevo Vehículo</h2>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/vehiculos/guardar" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Información del Vehículo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre / Descripción *</label>
                    <input type="text" name="nombre" required placeholder="Ej. Patrulla Ford Explorer #08"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php if (!empty($catVehiculos)): ?>
                        <?php foreach ($catVehiculos as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['clave'], ENT_QUOTES,'UTF-8') ?>">
                            <?= htmlspecialchars($cat['etiqueta'], ENT_QUOTES,'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <option value="patrulla">Patrulla</option>
                        <option value="moto">Motocicleta</option>
                        <option value="camioneta">Camioneta</option>
                        <option value="otro">Otro</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placas *</label>
                    <input type="text" name="placas" required placeholder="Ej. QRO-1234-A"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" name="marca" placeholder="Ej. Ford, Dodge, Chevrolet"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="modelo" placeholder="Ej. Explorer, Charger, Silverado"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                    <input type="text" name="serie"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <input type="number" name="anio" min="1990" max="2030" value="<?= date('Y') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" name="color" placeholder="Ej. Blanco y Azul"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="operativo">Operativo</option>
                        <option value="taller">En Taller</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kilometraje Actual</label>
                    <input type="number" name="kilometraje" min="0" value="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <div class="relative">
                        <input type="text" id="personal_search" autocomplete="off" placeholder="Buscar por nombre…"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <input type="hidden" name="personal_id" id="personal_id">
                        <div id="personal_dropdown"
                             class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden max-h-48 overflow-y-auto text-sm"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Adquisición</label>
                    <input type="date" name="fecha_adquisicion"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor ($)</label>
                    <input type="number" name="valor" step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" placeholder="Ej. Estacionamiento Zona Norte"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
        </div>
        <!-- GPS Device (Traccar) fields -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4 cursor-pointer" onclick="toggleGpsVeh()">
                <h3 class="font-semibold text-gray-700">
                    <i class="fa-solid fa-map-location-dot mr-2 text-indigo-500"></i>Dispositivo GPS (Traccar)
                    <span class="ml-2 text-xs font-normal text-gray-400">— Opcional</span>
                </h3>
                <i id="gps-veh-chevron" class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
            </div>
            <div id="gps-veh-fields" class="hidden">
                <p class="text-xs text-gray-500 mb-4">
                    Enlaza un dispositivo GPS de tu servidor
                    <a href="https://www.traccar.org/api-reference/" target="_blank" class="text-indigo-600 hover:underline">Traccar</a>
                    a este vehículo.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Dispositivo GPS</label>
                        <input type="text" name="gps_nombre" placeholder="Ej. GPS Patrulla QRO-123-A"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identificador Único (IMEI / uniqueId) *</label>
                        <input type="text" name="gps_unique_id" placeholder="Ej. 123456789012345"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">IMEI del dispositivo o ID único configurado en Traccar.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID en Traccar</label>
                        <input type="number" name="gps_traccar_id" min="1" placeholder="ID del dispositivo en el servidor Traccar"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono del dispositivo</label>
                        <input type="text" name="gps_telefono" placeholder="Ej. +52 442 000 0000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo del dispositivo GPS</label>
                        <input type="text" name="gps_modelo" placeholder="Ej. Queclink GV300, Teltonika FMB920"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría Traccar</label>
                        <select name="gps_categoria"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <?php foreach (GpsDevice::getCategoryOptions() as $k => $v): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($v, ENT_QUOTES,'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contacto / Responsable GPS</label>
                        <input type="text" name="gps_contacto" placeholder="Nombre o email del responsable"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID de Grupo Traccar</label>
                        <input type="number" name="gps_grupo_id" min="0" placeholder="ID del grupo (opcional)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <a href="<?= BASE_URL ?>/vehiculos" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Vehículo
            </button>
        </div>
    </form>
</div>

<script>
function toggleGpsVeh() {
    const fields  = document.getElementById('gps-veh-fields');
    const chevron = document.getElementById('gps-veh-chevron');
    fields.classList.toggle('hidden');
    chevron.style.transform = fields.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// ── Personal autocomplete ─────────────────────────────────────────────────────
(function() {
    const input    = document.getElementById('personal_search');
    const hidden   = document.getElementById('personal_id');
    const dropdown = document.getElementById('personal_dropdown');
    let   timer    = null;

    input.addEventListener('input', function() {
        clearTimeout(timer);
        const q = this.value.trim();
        hidden.value = '';
        if (q.length < 1) { dropdown.classList.add('hidden'); return; }
        timer = setTimeout(() => {
            fetch('<?= BASE_URL ?>/personal/buscar?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    if (!data.length) {
                        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-400">Sin resultados</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }
                    data.forEach(p => {
                        const label = [(p.cargo||''), p.nombre, p.apellidos].filter(Boolean).join(' ');
                        const div = document.createElement('div');
                        div.className = 'px-3 py-2 cursor-pointer hover:bg-indigo-50';
                        div.textContent = label;
                        div.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            input.value  = label;
                            hidden.value = p.id;
                            dropdown.classList.add('hidden');
                        });
                        dropdown.appendChild(div);
                    });
                    dropdown.classList.remove('hidden');
                });
        }, 200);
    });

    input.addEventListener('blur', function() {
        setTimeout(() => dropdown.classList.add('hidden'), 150);
    });
})();
</script>
