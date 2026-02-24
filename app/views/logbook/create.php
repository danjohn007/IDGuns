<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/bitacora" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Nueva Entrada de Bitácora</h2>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/bitacora/guardar" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimiento *</label>
                    <select name="tipo" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                        <option value="asignacion">Asignación</option>
                        <option value="devolucion">Devolución</option>
                        <option value="incidencia">Incidencia</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                    <select name="turno"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="matutino">🌅 Matutino</option>
                        <option value="vespertino">🌤 Vespertino</option>
                        <option value="nocturno">🌙 Nocturno</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activo Relacionado</label>
                    <select name="activo_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin activo específico --</option>
                        <?php foreach ($activos as $a): ?>
                        <option value="<?= $a['id'] ?>">[<?= htmlspecialchars($a['codigo'], ENT_QUOTES,'UTF-8') ?>] <?= htmlspecialchars($a['nombre'], ENT_QUOTES,'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Activo</label>
                    <input type="text" name="activo_tipo" placeholder="Ej. arma, vehículo, equipo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oficial</label>
                    <select name="oficial_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin especificar --</option>
                        <?php foreach ($oficiales as $o): ?>
                        <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['rango'].' '.$o['nombre'].' '.$o['apellidos'], ENT_QUOTES,'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y Hora</label>
                    <input type="datetime-local" name="fecha" value="<?= date('Y-m-d\TH:i') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado Anterior</label>
                    <input type="text" name="estado_anterior" placeholder="Ej. Operativa, En almacén"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado Nuevo</label>
                    <input type="text" name="estado_nuevo" placeholder="Ej. Asignada, En patrulla"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción *</label>
                    <textarea name="descripcion" rows="3" required
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Describa el movimiento con detalle…"></textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="<?= BASE_URL ?>/bitacora" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar Entrada
            </button>
        </div>
    </form>
</div>
