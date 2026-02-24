<?php
$tipoColors = [
    'entrada'    => ['bg'=>'green-100',  'text'=>'green-700'],
    'salida'     => ['bg'=>'blue-100',   'text'=>'blue-700'],
    'asignacion' => ['bg'=>'indigo-100', 'text'=>'indigo-700'],
    'devolucion' => ['bg'=>'yellow-100', 'text'=>'yellow-700'],
    'incidencia' => ['bg'=>'red-100',    'text'=>'red-700'],
];
?>
<!-- Toolbar -->
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" action="<?= BASE_URL ?>/bitacora" class="flex flex-wrap gap-2">
        <select name="tipo" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los tipos</option>
            <?php foreach (['entrada','salida','asignacion','devolucion','incidencia'] as $t): ?>
            <option value="<?= $t ?>" <?= ($filters['tipo']==$t)?'selected':'' ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="turno" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los turnos</option>
            <option value="matutino"   <?= ($filters['turno']=='matutino')?'selected':'' ?>>Matutino</option>
            <option value="vespertino" <?= ($filters['turno']=='vespertino')?'selected':'' ?>>Vespertino</option>
            <option value="nocturno"   <?= ($filters['turno']=='nocturno')?'selected':'' ?>>Nocturno</option>
        </select>
        <select name="oficial_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Todos los oficiales</option>
            <?php foreach ($oficiales as $o): ?>
            <option value="<?= $o['id'] ?>" <?= ($filters['oficial_id']==$o['id'])?'selected':'' ?>>
                <?= htmlspecialchars($o['nombre'].' '.$o['apellidos'], ENT_QUOTES,'UTF-8') ?>
            </option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filters['fecha_desde'], ENT_QUOTES,'UTF-8') ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filters['fecha_hasta'], ENT_QUOTES,'UTF-8') ?>"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-magnifying-glass mr-1"></i>Filtrar
        </button>
        <?php if (array_filter($filters)): ?>
        <a href="<?= BASE_URL ?>/bitacora" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">Limpiar</a>
        <?php endif; ?>
    </form>
    <?php if (in_array($_SESSION['user_role']??'', ['superadmin','admin','bitacora'])): ?>
    <a href="<?= BASE_URL ?>/bitacora/crear"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
        <i class="fa-solid fa-plus mr-1"></i> Nueva Entrada
    </a>
    <?php endif; ?>
</div>

<p class="text-sm text-gray-500 mb-3">Mostrando <?= count($entries) ?> de <?= $total ?> entradas</p>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Fecha</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Tipo</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Activo</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Oficial</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Turno</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Descripción</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium">Responsable</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php if (empty($entries)): ?>
            <tr><td colspan="7" class="text-center py-12 text-gray-400">
                <i class="fa-solid fa-book-open text-3xl block mb-2"></i>Sin entradas en la bitácora
            </td></tr>
            <?php else: ?>
            <?php foreach ($entries as $e):
                $tc = $tipoColors[$e['tipo']] ?? ['bg'=>'gray-100','text'=>'gray-700'];
                $turnos = ['matutino'=>'🌅','vespertino'=>'🌤','nocturno'=>'🌙'];
            ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                    <?= date('d/m/Y', strtotime($e['fecha'])) ?><br>
                    <span class="text-gray-400"><?= date('H:i', strtotime($e['fecha'])) ?></span>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $tc['bg'] ?> text-<?= $tc['text'] ?>">
                        <?= ucfirst($e['tipo']) ?>
                    </span>
                </td>
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800 text-xs"><?= htmlspecialchars($e['activo_nombre']??'—', ENT_QUOTES,'UTF-8') ?></p>
                    <p class="text-gray-400 font-mono text-xs"><?= htmlspecialchars($e['activo_codigo']??'', ENT_QUOTES,'UTF-8') ?></p>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars($e['oficial_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 text-xs"><?= ($turnos[$e['turno']]??'').' '.ucfirst($e['turno']??'') ?></td>
                <td class="px-4 py-3 text-gray-600 text-xs max-w-[250px] truncate"><?= htmlspecialchars($e['descripcion']??'', ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-3 text-gray-500 text-xs"><?= htmlspecialchars($e['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
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
    <a href="<?= BASE_URL ?>/bitacora?<?= $q ?>"
       class="px-3 py-1.5 text-sm rounded-lg <?= $i==$page?'bg-indigo-600 text-white':'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>
