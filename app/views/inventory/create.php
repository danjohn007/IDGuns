<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/inventario" class="text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-semibold text-gray-800">Registrar Nuevo Activo</h2>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/inventario/guardar" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">

        <!-- Base asset info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Activo *</label>
                    <input type="text" name="nombre" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Pistola Glock 17 #001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                    <select name="categoria" id="selectCategoria" required onchange="toggleExtra(this.value)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="arma">Arma</option>
                        <option value="vehiculo">Vehículo</option>
                        <option value="equipo_computo">Equipo de Cómputo</option>
                        <option value="equipo_oficina">Equipo de Oficina</option>
                        <option value="bien_mueble">Bien Mueble</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="activo">Activo</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" name="marca"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="modelo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                    <input type="text" name="serie"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <select name="responsable_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'], ENT_QUOTES,'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Armería Principal">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Adquisición</label>
                    <input type="date" name="fecha_adquisicion"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor ($)</label>
                    <input type="number" name="valor" step="0.01" min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Weapon extra fields -->
        <div id="extraArma" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fa-solid fa-gun mr-2 text-gray-500"></i>Datos del Arma</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="arma_tipo"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pistola">Pistola</option>
                        <option value="rifle">Rifle</option>
                        <option value="escopeta">Escopeta</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Calibre</label>
                    <input type="text" name="calibre" placeholder="Ej. 9mm, .45, 5.56x45"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie Arma</label>
                    <input type="text" name="arma_serie"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oficial Asignado</label>
                    <select name="oficial_asignado_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($oficiales as $o): ?>
                        <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['rango'].' '.$o['nombre'].' '.$o['apellidos'], ENT_QUOTES,'UTF-8') ?> — <?= htmlspecialchars($o['placa'], ENT_QUOTES,'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municiones Asignadas</label>
                    <input type="number" name="municiones_asignadas" min="0" value="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Vehicle extra fields -->
        <div id="extraVehiculo" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fa-solid fa-car mr-2 text-gray-500"></i>Datos del Vehículo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="vehiculo_tipo"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="patrulla">Patrulla</option>
                        <option value="moto">Motocicleta</option>
                        <option value="camioneta">Camioneta</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placas</label>
                    <input type="text" name="placas" placeholder="Ej. QRO-123-A"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <input type="number" name="anio" min="1990" max="2030" value="<?= date('Y') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" name="color"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kilometraje</label>
                    <input type="number" name="kilometraje" min="0" value="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="<?= BASE_URL ?>/inventario"
               class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
               Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Activo
            </button>
        </div>
    </form>
</div>

<script>
function toggleExtra(categoria) {
    document.getElementById('extraArma').classList.add('hidden');
    document.getElementById('extraVehiculo').classList.add('hidden');
    if (categoria === 'arma') document.getElementById('extraArma').classList.remove('hidden');
    if (categoria === 'vehiculo') document.getElementById('extraVehiculo').classList.remove('hidden');
}
toggleExtra(document.getElementById('selectCategoria').value);
</script>
