<?php
$defaultCatLabel = ['arma'=>'Arma','vehiculo'=>'Vehículo','equipo_computo'=>'Eq. Cómputo','equipo_oficina'=>'Eq. Oficina','bien_mueble'=>'Bien Mueble'];
// Merge static defaults with any dynamic catalog categories
$catMap = $defaultCatLabel;
if (!empty($catActivos)) {
    $catMap = [];
    foreach ($catActivos as $cat) { $catMap[$cat['clave']] = $cat['etiqueta']; }
}
$statColor = ['activo'=>'green','baja'=>'red','mantenimiento'=>'yellow'];
?>
<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" action="<?= BASE_URL ?>/inventario" class="flex flex-wrap gap-2">
        <input type="text" name="buscar" placeholder="Buscar…" value="<?= htmlspecialchars($filters['buscar'], ENT_QUOTES,'UTF-8') ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-44">
        <select name="categoria" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todas las categorías</option>
            <?php foreach ($catMap as $k=>$v): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES,'UTF-8') ?>" <?= ($filters['categoria']==$k)?'selected':'' ?>><?= htmlspecialchars($v, ENT_QUOTES,'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los estados</option>
            <option value="activo"        <?= ($filters['estado']=='activo')?'selected':'' ?>>Activo</option>
            <option value="baja"          <?= ($filters['estado']=='baja')?'selected':'' ?>>Baja</option>
            <option value="mantenimiento" <?= ($filters['estado']=='mantenimiento')?'selected':'' ?>>Mantenimiento</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-magnifying-glass mr-1"></i>Filtrar
        </button>
        <?php if ($filters['buscar'] || $filters['categoria'] || $filters['estado']): ?>
        <a href="<?= BASE_URL ?>/inventario" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
            Limpiar
        </a>
        <?php endif; ?>
    </form>
    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
    <div class="flex gap-2">
        <button onclick="window.print()"
                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
            <i class="fa-solid fa-print mr-1"></i>Imprimir
        </button>
        <a href="<?= BASE_URL ?>/inventario/exportar?<?= http_build_query($filters) ?>"
           class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-file-excel mr-1"></i>Excel
        </a>
        <a href="<?= BASE_URL ?>/inventario/crear"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Nuevo Activo
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Count -->
<p class="text-sm text-gray-500 mb-3">
    Mostrando <?= count($activos) ?> de <?= $total ?> activos
</p>

<!-- Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Código</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Nombre</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Categoría</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Marca / Modelo</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Serie</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Estado</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Responsable</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Ubicación</th>
                    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
                    <th class="px-4 py-3"></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php if (empty($activos)): ?>
            <tr><td colspan="9" class="text-center py-12 text-gray-400">
                <i class="fa-solid fa-box-open text-3xl block mb-2"></i>Sin activos registrados
            </td></tr>
            <?php else: ?>
            <?php foreach ($activos as $a):
                $sc = $statColor[$a['estado']] ?? 'gray';
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($a['codigo'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 font-medium text-gray-800"><?= htmlspecialchars($a['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3">
                    <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium">
                        <?= htmlspecialchars($catMap[$a['categoria']] ?? $a['categoria'], ENT_QUOTES,'UTF-8') ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars(trim(($a['marca']??'').' '.($a['modelo']??'')), ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 font-mono text-xs text-gray-500"><?= htmlspecialchars($a['serie']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                        bg-<?= $sc ?>-100 text-<?= $sc ?>-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-<?= $sc ?>-500"></span>
                        <?= ucfirst($a['estado']) ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($a['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 text-gray-500 text-xs max-w-[140px] truncate"><?= htmlspecialchars($a['ubicacion']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
                <td class="px-4 py-3 whitespace-nowrap">
                    <a href="<?= BASE_URL ?>/inventario/editar/<?= $a['id'] ?>"
                       class="text-indigo-600 hover:text-indigo-800 mr-3 text-xs font-medium">
                        <i class="fa-solid fa-pen-to-square"></i> Editar
                    </a>
                    <a href="<?= BASE_URL ?>/inventario/eliminar/<?= $a['id'] ?>"
                       onclick="return confirm('¿Eliminar este activo?')"
                       class="text-red-500 hover:text-red-700 text-xs font-medium">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
                <?php endif; ?>
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
    <?php for ($i=1; $i<=$pages; $i++):
        $q = http_build_query(array_merge($filters, ['pagina'=>$i]));
    ?>
    <a href="<?= BASE_URL ?>/inventario?<?= $q ?>"
       class="px-3 py-1.5 text-sm rounded-lg <?= $i==$page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
