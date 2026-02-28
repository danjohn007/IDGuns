<?php
$estadoColor = ['operativo'=>'green','taller'=>'yellow','baja'=>'red'];
$tipoLabel   = ['patrulla'=>'Patrulla','moto'=>'Moto','camioneta'=>'Camioneta','otro'=>'Otro'];
?>
<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" action="<?= BASE_URL ?>/vehiculos" class="flex flex-wrap gap-2">
        <input type="text" name="buscar" placeholder="Buscar placa, nombre, código…"
               value="<?= htmlspecialchars($filters['buscar'], ENT_QUOTES,'UTF-8') ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 w-52">
        <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los tipos</option>
            <?php foreach ($tipoLabel as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($filters['tipo']==$k)?'selected':'' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <select name="estado" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los estados</option>
            <option value="operativo" <?= ($filters['estado']=='operativo')?'selected':'' ?>>Operativo</option>
            <option value="taller"    <?= ($filters['estado']=='taller')?'selected':'' ?>>En Taller</option>
            <option value="baja"      <?= ($filters['estado']=='baja')?'selected':'' ?>>Baja</option>
        </select>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-filter mr-1"></i>Filtrar
        </button>
        <?php if ($filters['estado'] || $filters['tipo'] || $filters['buscar']): ?>
        <a href="<?= BASE_URL ?>/vehiculos" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>
    <div class="flex gap-2">
        <button onclick="window.print()"
                class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
            <i class="fa-solid fa-print mr-1"></i>Imprimir
        </button>
        <a href="<?= BASE_URL ?>/vehiculos/exportar?<?= http_build_query($filters) ?>"
           class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition-colors">
            <i class="fa-solid fa-file-excel mr-1"></i>Excel
        </a>
        <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
        <a href="<?= BASE_URL ?>/vehiculos/crear"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Nuevo Vehículo
        </a>
        <?php endif; ?>
    </div>
</div>

<p class="text-sm text-gray-500 mb-3">Mostrando <?= count($vehiculos) ?> de <?= $total ?> vehículos</p>

<!-- Vehicle grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
<?php if (empty($vehiculos)): ?>
    <div class="md:col-span-3 text-center py-16 text-gray-400">
        <i class="fa-solid fa-car text-4xl block mb-3"></i>Sin vehículos registrados
    </div>
<?php else: ?>
<?php foreach ($vehiculos as $v):
    $sc = $estadoColor[$v['estado']] ?? 'gray';
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
    <div class="flex items-start justify-between mb-3">
        <div>
            <p class="font-semibold text-gray-800"><?= htmlspecialchars($v['nombre']??($v['marca'].' '.$v['modelo']), ENT_QUOTES,'UTF-8') ?></p>
            <p class="text-xs text-gray-400 font-mono mt-0.5"><?= htmlspecialchars($v['codigo'], ENT_QUOTES,'UTF-8') ?></p>
        </div>
        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-<?= $sc ?>-100 text-<?= $sc ?>-700">
            <span class="w-1.5 h-1.5 rounded-full bg-<?= $sc ?>-500"></span>
            <?= ucfirst($v['estado']) ?>
        </span>
    </div>
    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600 mb-4">
        <div><span class="text-gray-400">Placas:</span> <strong><?= htmlspecialchars($v['placas']??'—', ENT_QUOTES,'UTF-8') ?></strong></div>
        <div><span class="text-gray-400">Tipo:</span> <?= $tipoLabel[$v['tipo']]??$v['tipo'] ?></div>
        <div><span class="text-gray-400">Año:</span> <?= htmlspecialchars($v['anio'] ?? $v['año'] ?? '—', ENT_QUOTES,'UTF-8') ?></div>
        <div><span class="text-gray-400">Color:</span> <?= htmlspecialchars($v['color']??'—', ENT_QUOTES,'UTF-8') ?></div>
        <div><span class="text-gray-400">Km:</span> <?= number_format($v['kilometraje']??0) ?></div>
        <div><span class="text-gray-400">Responsable:</span> <?= htmlspecialchars($v['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></div>
    </div>
    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin'])): ?>
    <div class="flex gap-2 pt-3 border-t border-gray-100">
        <a href="<?= BASE_URL ?>/vehiculos/editar/<?= $v['id'] ?>"
           class="flex-1 text-center text-xs font-medium text-indigo-600 hover:text-indigo-800 py-1.5 rounded-lg hover:bg-indigo-50 transition-colors">
            <i class="fa-solid fa-pen-to-square mr-1"></i>Editar
        </a>
        <a href="<?= BASE_URL ?>/vehiculos/eliminar/<?= $v['id'] ?>"
           onclick="return confirm('¿Eliminar este vehículo?')"
           class="flex-1 text-center text-xs font-medium text-red-500 hover:text-red-700 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
            <i class="fa-solid fa-trash mr-1"></i>Eliminar
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="flex items-center justify-center gap-1 mt-5">
    <?php for ($i=1; $i<=$pages; $i++):
        $q = http_build_query(array_merge($filters, ['pagina'=>$i]));
    ?>
    <a href="<?= BASE_URL ?>/vehiculos?<?= $q ?>"
       class="px-3 py-1.5 text-sm rounded-lg <?= $i==$page?'bg-indigo-600 text-white':'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
