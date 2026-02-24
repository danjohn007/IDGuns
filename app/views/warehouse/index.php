<?php
$stockColor = fn($s) => match(true) {
    $s['stock_actual'] <= $s['stock_minimo']              => ['bg'=>'red-100',    'text'=>'red-700',    'label'=>'Crítico'],
    $s['stock_actual'] <= (int)($s['stock_minimo'] * 1.5) => ['bg'=>'yellow-100', 'text'=>'yellow-700', 'label'=>'Bajo'],
    default                                                => ['bg'=>'green-100',  'text'=>'green-700',  'label'=>'OK'],
};
$catLabel = ['limpieza'=>'Limpieza','papeleria'=>'Papelería','uniforme'=>'Uniformes','municion'=>'Munición','herramienta'=>'Herramienta','otro'=>'Otro'];
?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" action="<?= BASE_URL ?>/almacen" class="flex flex-wrap gap-2">
        <input type="text" name="buscar" placeholder="Buscar suministro…" value="<?= htmlspecialchars($filters['buscar'], ENT_QUOTES,'UTF-8') ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-44">
        <select name="categoria" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todas</option>
            <?php foreach ($catLabel as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($filters['categoria']==$k)?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="alerta" value="1" <?= !empty($filters['alerta'])?'checked':'' ?> class="rounded border-gray-300 text-indigo-600">
            Solo alertas
        </label>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-magnifying-glass mr-1"></i>Filtrar
        </button>
        <?php if ($filters['buscar'] || $filters['categoria'] || $filters['alerta']): ?>
        <a href="<?= BASE_URL ?>/almacen" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>
    <div class="flex gap-2">
        <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin','almacen'])): ?>
        <a href="<?= BASE_URL ?>/almacen/crear"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Nuevo Suministro
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Low stock banner -->
<?php if (count($lowStock) > 0): ?>
<div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-5 py-3 flex items-start gap-3">
    <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-semibold text-red-800">¡Alerta! <?= count($lowStock) ?> suministros con stock crítico</p>
        <p class="text-xs text-red-600 mt-0.5"><?= implode(', ', array_map(fn($s)=>htmlspecialchars($s['nombre'],ENT_QUOTES,'UTF-8'), array_slice($lowStock,0,3))) ?><?= count($lowStock)>3?' y más…':'' ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Supplies table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Nombre</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Categoría</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Unidad</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Stock</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Mín</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">Máx</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Nivel</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Ubicación</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php if (empty($suministros)): ?>
            <tr><td colspan="9" class="text-center py-10 text-gray-400">Sin suministros registrados</td></tr>
            <?php else: ?>
            <?php foreach ($suministros as $s):
                $sc = $stockColor($s);
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($s['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3"><span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= $catLabel[$s['categoria']]??$s['categoria'] ?></span></td>
                <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($s['unidad']??'pza', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 text-right font-bold text-gray-800"><?= $s['stock_actual'] ?></td>
                <td class="px-4 py-3 text-right text-gray-500"><?= $s['stock_minimo'] ?></td>
                <td class="px-4 py-3 text-right text-gray-500"><?= $s['stock_maximo'] ?></td>
                <td class="px-4 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $sc['bg'] ?> text-<?= $sc['text'] ?>">
                        <?= $sc['label'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs truncate max-w-[120px]"><?= htmlspecialchars($s['ubicacion']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin','almacen'])): ?>
                    <button onclick="openMovModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nombre'],ENT_QUOTES,'UTF-8') ?>', <?= $s['stock_actual'] ?>)"
                            class="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2">
                        <i class="fa-solid fa-arrows-rotate"></i> Mover
                    </button>
                    <a href="<?= BASE_URL ?>/almacen/editar/<?= $s['id'] ?>"
                       class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mr-2">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
                    <a href="<?= BASE_URL ?>/almacen/eliminar/<?= $s['id'] ?>"
                       onclick="return confirm('¿Eliminar este suministro?')"
                       class="text-red-500 hover:text-red-700 text-xs font-medium">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="flex items-center justify-center gap-1 mt-4">
    <?php for ($i=1; $i<=$pages; $i++): ?>
    <a href="<?= BASE_URL ?>/almacen?pagina=<?= $i ?>"
       class="px-3 py-1.5 text-sm rounded-lg <?= $i==$page?'bg-indigo-600 text-white':'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Recent movements -->
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-clock-rotate-left mr-2 text-blue-500"></i>Movimientos Recientes</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500">Fecha</th>
                    <th class="text-left px-4 py-2 text-gray-500">Suministro</th>
                    <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                    <th class="text-right px-4 py-2 text-gray-500">Cantidad</th>
                    <th class="text-left px-4 py-2 text-gray-500">Responsable</th>
                    <th class="text-left px-4 py-2 text-gray-500">Motivo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            <?php foreach ($recientes as $m): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 text-gray-500"><?= date('d/m H:i', strtotime($m['fecha'])) ?></td>
                <td class="px-4 py-2 font-medium text-gray-700"><?= htmlspecialchars($m['suministro_nombre']??'', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $m['tipo']==='entrada'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' ?>">
                        <?= ucfirst($m['tipo']) ?>
                    </span>
                </td>
                <td class="px-4 py-2 text-right font-bold <?= $m['tipo']==='entrada'?'text-green-600':'text-red-600' ?>">
                    <?= $m['tipo']==='entrada'?'+':'-' ?><?= $m['cantidad'] ?>
                </td>
                <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($m['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2 text-gray-500 truncate max-w-[180px]"><?= htmlspecialchars($m['motivo']??'', ENT_QUOTES,'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Movement modal -->
<div id="movModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-arrows-rotate mr-2 text-blue-500"></i>Registrar Movimiento</h3>
        <form method="POST" action="<?= BASE_URL ?>/almacen/movimiento">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(isset($csrf)?$csrf:$_SESSION['csrf_token']??'', ENT_QUOTES,'UTF-8') ?>">
            <input type="hidden" name="suministro_id" id="movSuministroId">
            <p class="text-sm text-gray-600 mb-4">
                Suministro: <strong id="movNombre" class="text-gray-800"></strong>
                — Stock actual: <strong id="movStock" class="text-gray-800"></strong>
            </p>
            <div class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                        <input type="number" name="cantidad" min="1" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oficial</label>
                    <select name="oficial_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">-- Sin especificar --</option>
                        <?php foreach ($oficiales as $o): ?>
                        <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre'].' '.$o['apellidos'], ENT_QUOTES,'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <input type="text" name="motivo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Asignación turno matutino">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notas" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-5 justify-end">
                <button type="button" onclick="closeMovModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</button>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    <i class="fa-solid fa-check mr-1"></i>Registrar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openMovModal(id, nombre, stock) {
    document.getElementById('movSuministroId').value = id;
    document.getElementById('movNombre').textContent = nombre;
    document.getElementById('movStock').textContent  = stock;
    document.getElementById('movModal').classList.remove('hidden');
}
function closeMovModal() {
    document.getElementById('movModal').classList.add('hidden');
}
</script>
