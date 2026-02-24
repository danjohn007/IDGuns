<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?= BASE_URL ?>/almacen" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Editar Suministro: <?= htmlspecialchars($suministro['nombre'], ENT_QUOTES,'UTF-8') ?></h2>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/almacen/actualizar">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $suministro['id'] ?>">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="nombre" required value="<?= htmlspecialchars($suministro['nombre'], ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select name="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <?php foreach (['limpieza'=>'Limpieza','papeleria'=>'Papelería','uniforme'=>'Uniformes','municion'=>'Munición','herramienta'=>'Herramienta','otro'=>'Otro'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= $suministro['categoria']==$k?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                    <input type="text" name="unidad" value="<?= htmlspecialchars($suministro['unidad']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" min="0" value="<?= $suministro['stock_minimo'] ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Máximo</label>
                    <input type="number" name="stock_maximo" min="0" value="<?= $suministro['stock_maximo'] ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Precio Unitario ($)</label>
                    <input type="number" name="precio_unitario" step="0.01" min="0" value="<?= $suministro['precio_unitario']??'' ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                    <input type="text" name="ubicacion" value="<?= htmlspecialchars($suministro['ubicacion']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
                    <input type="text" name="proveedor" value="<?= htmlspecialchars($suministro['proveedor']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex gap-3 justify-end mt-5">
                <a href="<?= BASE_URL ?>/almacen" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Actualizar
                </button>
            </div>
        </div>
    </form>

    <!-- Movement history -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-clock-rotate-left mr-2 text-blue-500"></i>Historial de Movimientos</h3>
            <span class="text-xs text-gray-400">Últimos <?= count($movimientos) ?> registros</span>
        </div>
        <?php if (empty($movimientos)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Sin movimientos registrados</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500">Fecha</th>
                        <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                        <th class="text-right px-4 py-2 text-gray-500">Cantidad</th>
                        <th class="text-left px-4 py-2 text-gray-500">Responsable</th>
                        <th class="text-left px-4 py-2 text-gray-500">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td class="px-4 py-2 text-gray-500"><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $m['tipo']==='entrada'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' ?>">
                            <?= ucfirst($m['tipo']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right font-bold <?= $m['tipo']==='entrada'?'text-green-600':'text-red-600' ?>">
                        <?= $m['tipo']==='entrada'?'+':'-' ?><?= $m['cantidad'] ?>
                    </td>
                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($m['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 text-gray-500"><?= htmlspecialchars($m['motivo']??'', ENT_QUOTES,'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
