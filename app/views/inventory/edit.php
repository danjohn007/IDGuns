<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/inventario" class="text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-semibold text-gray-800">Editar Activo: <?= htmlspecialchars($activo['nombre'], ENT_QUOTES,'UTF-8') ?></h2>
        <span class="font-mono text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded"><?= htmlspecialchars($activo['codigo'], ENT_QUOTES,'UTF-8') ?></span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/inventario/actualizar" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $activo['id'] ?>">

        <!-- Base asset info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Activo *</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($activo['nombre'], ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="activo"        <?= $activo['estado']=='activo'?'selected':'' ?>>Activo</option>
                        <option value="mantenimiento" <?= $activo['estado']=='mantenimiento'?'selected':'' ?>>Mantenimiento</option>
                        <option value="baja"          <?= $activo['estado']=='baja'?'selected':'' ?>>Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" name="marca" value="<?= htmlspecialchars($activo['marca']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="modelo" value="<?= htmlspecialchars($activo['modelo']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($activo['serie']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <select name="responsable_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($activo['responsable_id']==$u['id'])?'selected':'' ?>>
                            <?= htmlspecialchars($u['nombre'], ENT_QUOTES,'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" value="<?= htmlspecialchars($activo['ubicacion']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Adquisición</label>
                    <input type="date" name="fecha_adquisicion" value="<?= htmlspecialchars($activo['fecha_adquisicion']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor ($)</label>
                    <input type="number" name="valor" step="0.01" min="0" value="<?= $activo['valor'] ?? '' ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($activo['descripcion']??'', ENT_QUOTES,'UTF-8') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Weapon extra fields -->
        <?php if (!empty($arma)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4"><i class="fa-solid fa-gun mr-2 text-gray-500"></i>Datos del Arma</h3>
            <input type="hidden" name="arma_id" value="<?= $arma['id'] ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="arma_tipo"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php foreach (['pistola'=>'Pistola','rifle'=>'Rifle','escopeta'=>'Escopeta','otro'=>'Otro'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($arma['tipo']==$k)?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Calibre</label>
                    <input type="text" name="calibre" value="<?= htmlspecialchars($arma['calibre']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie Arma</label>
                    <input type="text" name="arma_serie" value="<?= htmlspecialchars($arma['numero_serie']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado del Arma</label>
                    <select name="arma_estado"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="operativa"     <?= ($arma['estado']=='operativa')?'selected':'' ?>>Operativa</option>
                        <option value="mantenimiento" <?= ($arma['estado']=='mantenimiento')?'selected':'' ?>>Mantenimiento</option>
                        <option value="baja"          <?= ($arma['estado']=='baja')?'selected':'' ?>>Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oficial Asignado</label>
                    <select name="oficial_asignado_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($oficiales as $o): ?>
                        <option value="<?= $o['id'] ?>" <?= ($arma['oficial_asignado_id']==$o['id'])?'selected':'' ?>>
                            <?= htmlspecialchars($o['rango'].' '.$o['nombre'].' '.$o['apellidos'], ENT_QUOTES,'UTF-8') ?> — <?= htmlspecialchars($o['placa'], ENT_QUOTES,'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Municiones Asignadas</label>
                    <input type="number" name="municiones_asignadas" min="0" value="<?= (int)($arma['municiones_asignadas']??0) ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- GPS Device (Traccar) fields -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4 cursor-pointer" onclick="toggleGpsEdit()">
                <h3 class="font-semibold text-gray-700">
                    <i class="fa-solid fa-map-location-dot mr-2 text-indigo-500"></i>Dispositivo GPS (Traccar)
                    <?php if (!empty($gpsDevice)): ?>
                    <span class="ml-2 text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Configurado</span>
                    <?php else: ?>
                    <span class="ml-2 text-xs font-normal text-gray-400">— Opcional</span>
                    <?php endif; ?>
                </h3>
                <i id="gps-chevron" class="fa-solid fa-chevron-<?= !empty($gpsDevice) ? 'up' : 'down' ?> text-gray-400 transition-transform"></i>
            </div>
            <div id="gps-fields" class="<?= !empty($gpsDevice) ? '' : 'hidden' ?>">
                <?php if (!empty($gpsDevice)): ?>
                <input type="hidden" name="gps_device_id" value="<?= (int)$gpsDevice['id'] ?>">
                <?php endif; ?>
                <p class="text-xs text-gray-500 mb-4">
                    Enlaza un dispositivo GPS de tu servidor
                    <a href="https://www.traccar.org/api-reference/" target="_blank" class="text-indigo-600 hover:underline">Traccar</a>
                    a este activo.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Dispositivo GPS</label>
                        <input type="text" name="gps_nombre"
                               value="<?= htmlspecialchars($gpsDevice['nombre']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. GPS Patrulla QRO-123-A"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Identificador Único (IMEI / uniqueId)</label>
                        <input type="text" name="gps_unique_id"
                               value="<?= htmlspecialchars($gpsDevice['unique_id']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. 123456789012345"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID en Traccar</label>
                        <input type="number" name="gps_traccar_id" min="1"
                               value="<?= $gpsDevice['traccar_device_id'] ?? '' ?>"
                               placeholder="ID del dispositivo en el servidor Traccar"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono del dispositivo</label>
                        <input type="text" name="gps_telefono"
                               value="<?= htmlspecialchars($gpsDevice['telefono']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. +52 442 000 0000"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modelo del dispositivo GPS</label>
                        <input type="text" name="gps_modelo"
                               value="<?= htmlspecialchars($gpsDevice['modelo_dispositivo']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. Queclink GV300, Teltonika FMB920"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría Traccar</label>
                        <select name="gps_categoria"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <?php foreach (GpsDevice::getCategoryOptions() as $k => $v): ?>
                            <option value="<?= $k ?>" <?= (($gpsDevice['categoria_traccar']??'car')===$k)?'selected':'' ?>>
                                <?= htmlspecialchars($v, ENT_QUOTES,'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contacto / Responsable GPS</label>
                        <input type="text" name="gps_contacto"
                               value="<?= htmlspecialchars($gpsDevice['contacto']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Nombre o email del responsable"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID de Grupo Traccar</label>
                        <input type="number" name="gps_grupo_id" min="0"
                               value="<?= $gpsDevice['grupo_id'] ?? '' ?>"
                               placeholder="ID del grupo (opcional)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
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
                <i class="fa-solid fa-floppy-disk mr-2"></i>Actualizar Activo
            </button>
        </div>
    </form>
</div>

<script>
function toggleGpsEdit() {
    const fields  = document.getElementById('gps-fields');
    const chevron = document.getElementById('gps-chevron');
    fields.classList.toggle('hidden');
    chevron.className = 'fa-solid fa-chevron-' + (fields.classList.contains('hidden') ? 'down' : 'up') + ' text-gray-400 transition-transform';
}
</script>
