<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?= BASE_URL ?>/vehiculos" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Editar Vehículo</h2>
        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded font-mono"><?= htmlspecialchars($vehicle['codigo']??'', ENT_QUOTES,'UTF-8') ?></span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/vehiculos/actualizar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $vehicle['id'] ?>">
        <input type="hidden" name="activo_id" value="<?= $vehicle['activo_id'] ?>">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Información del Vehículo</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($vehicle['nombre']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <?php if (!empty($catVehiculos)): ?>
                        <?php foreach ($catVehiculos as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['clave'], ENT_QUOTES,'UTF-8') ?>"
                            <?= ($vehicle['tipo']==$cat['clave'])?'selected':'' ?>>
                            <?= htmlspecialchars($cat['etiqueta'], ENT_QUOTES,'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <?php foreach (['patrulla'=>'Patrulla','moto'=>'Moto','camioneta'=>'Camioneta','otro'=>'Otro'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($vehicle['tipo']==$k)?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Placas</label>
                    <input type="text" name="placas" value="<?= htmlspecialchars($vehicle['placas']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" name="marca" value="<?= htmlspecialchars($vehicle['marca']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="modelo" value="<?= htmlspecialchars($vehicle['modelo']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($vehicle['serie']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Año</label>
                    <input type="number" name="anio" min="1990" max="2030" value="<?= $vehicle['anio'] ?? $vehicle['año'] ?? date('Y') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" name="color" value="<?= htmlspecialchars($vehicle['color']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="operativo" <?= ($vehicle['estado']=='operativo')?'selected':'' ?>>Operativo</option>
                        <option value="taller"    <?= ($vehicle['estado']=='taller')?'selected':'' ?>>En Taller</option>
                        <option value="baja"      <?= ($vehicle['estado']=='baja')?'selected':'' ?>>Baja</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kilometraje</label>
                    <input type="number" name="kilometraje" min="0" value="<?= $vehicle['kilometraje']??0 ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <select name="personal_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Sin asignar --</option>
                        <?php foreach ($personal as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (($vehicle['personal_id'] ?? '')==$p['id'])?'selected':'' ?>>
                            <?= htmlspecialchars(trim(($p['cargo'] ? $p['cargo'] . ' ' : '') . $p['nombre'] . ' ' . $p['apellidos']), ENT_QUOTES,'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor ($)</label>
                    <input type="number" name="valor" step="0.01" min="0" value="<?= $vehicle['valor']??'' ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($vehicle['descripcion']??'', ENT_QUOTES,'UTF-8') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Quick add maintenance -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-1">Agregar Mantenimiento</h3>
            <p class="text-xs text-gray-400 mb-4">Opcional — solo complete si desea registrar uno nuevo</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="mantenimiento_tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- No agregar --</option>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                        <option value="accidente">Accidente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="mantenimiento_estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completado">Completado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo ($)</label>
                    <input type="number" name="costo" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor / Taller</label>
                    <input type="text" name="proveedor" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="mantenimiento_desc" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
            </div>
        </div>

        <!-- Quick add fuel -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-1">Registrar Carga de Combustible</h3>
            <p class="text-xs text-gray-400 mb-4">Opcional — solo complete si desea registrar una carga</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Litros</label>
                    <input type="number" name="litros" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Costo Total ($)</label>
                    <input type="number" name="costo_combustible" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Km Actual</label>
                    <input type="number" name="km_actual" min="0" value="<?= $vehicle['kilometraje']??0 ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="<?= BASE_URL ?>/vehiculos" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Actualizar Vehículo
            </button>
        </div>
    </form>

    <!-- Maintenance history -->
    <?php if (!empty($mantenimientos)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-wrench mr-2 text-yellow-500"></i>Historial de Mantenimiento</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                    <th class="text-left px-4 py-2 text-gray-500">Descripción</th>
                    <th class="text-left px-4 py-2 text-gray-500">Inicio</th>
                    <th class="text-left px-4 py-2 text-gray-500">Fin</th>
                    <th class="text-right px-4 py-2 text-gray-500">Costo</th>
                    <th class="text-left px-4 py-2 text-gray-500">Estado</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                <?php foreach ($mantenimientos as $m): ?>
                <tr>
                    <td class="px-4 py-2 capitalize font-medium text-gray-700"><?= htmlspecialchars($m['tipo'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 text-gray-600 max-w-[200px] truncate"><?= htmlspecialchars($m['descripcion']??'', ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= $m['fecha_inicio'] ? date('d/m/Y', strtotime($m['fecha_inicio'])) : '—' ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= $m['fecha_fin']    ? date('d/m/Y', strtotime($m['fecha_fin']))    : '—' ?></td>
                    <td class="px-4 py-2 text-right text-gray-700"><?= $m['costo'] ? '$'.number_format($m['costo'],2) : '—' ?></td>
                    <td class="px-4 py-2">
                        <?php $ec=['pendiente'=>'yellow','en_proceso'=>'blue','completado'=>'green'][$m['estado']]??'gray'; ?>
                        <span class="bg-<?= $ec ?>-100 text-<?= $ec ?>-700 px-2 py-0.5 rounded-full text-xs"><?= ucfirst(str_replace('_',' ',$m['estado'])) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Fuel history -->
    <?php if (!empty($combustibles)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-gas-pump mr-2 text-orange-500"></i>Historial de Combustible</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50"><tr>
                    <th class="text-left px-4 py-2 text-gray-500">Fecha</th>
                    <th class="text-right px-4 py-2 text-gray-500">Litros</th>
                    <th class="text-right px-4 py-2 text-gray-500">Costo</th>
                    <th class="text-right px-4 py-2 text-gray-500">Km</th>
                    <th class="text-left px-4 py-2 text-gray-500">Responsable</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                <?php foreach ($combustibles as $c): ?>
                <tr>
                    <td class="px-4 py-2 text-gray-500"><?= date('d/m/Y', strtotime($c['fecha'])) ?></td>
                    <td class="px-4 py-2 text-right font-medium text-gray-700"><?= number_format($c['litros'],2) ?>L</td>
                    <td class="px-4 py-2 text-right text-gray-700"><?= $c['costo'] ? '$'.number_format($c['costo'],2) : '—' ?></td>
                    <td class="px-4 py-2 text-right text-gray-500"><?= number_format($c['kilometraje']) ?></td>
                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($c['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
