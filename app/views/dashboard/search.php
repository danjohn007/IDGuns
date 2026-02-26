<!-- Search form -->
<div class="mb-6">
    <form method="GET" action="<?= BASE_URL ?>/dashboard/buscar" class="flex gap-2 max-w-xl">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES,'UTF-8') ?>"
                   placeholder="Buscar en todo el sistema…"
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            Buscar
        </button>
        <a href="<?= BASE_URL ?>/dashboard"
           class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition-colors">
            Volver
        </a>
    </form>
</div>

<?php if ($q === ''): ?>
<div class="text-center py-16 text-gray-400">
    <i class="fa-solid fa-magnifying-glass text-4xl block mb-3"></i>
    Ingresa un término para buscar en todo el sistema
</div>
<?php elseif (empty($results)): ?>
<div class="text-center py-16 text-gray-400">
    <i class="fa-solid fa-circle-xmark text-4xl block mb-3"></i>
    No se encontraron resultados para <strong class="text-gray-600">"<?= htmlspecialchars($q, ENT_QUOTES,'UTF-8') ?>"</strong>
</div>
<?php else: ?>
<p class="text-sm text-gray-500 mb-4">
    <?= count($results) ?> resultado(s) para <strong>"<?= htmlspecialchars($q, ENT_QUOTES,'UTF-8') ?>"</strong>
</p>

<?php
$typeConfig = [
    'activo'     => ['label'=>'Activo',      'icon'=>'fa-boxes-stacked', 'color'=>'indigo', 'href'=>'inventario/editar/'],
    'vehiculo'   => ['label'=>'Vehículo',    'icon'=>'fa-car',           'color'=>'blue',   'href'=>'vehiculos/editar/'],
    'suministro' => ['label'=>'Suministro',  'icon'=>'fa-warehouse',     'color'=>'green',  'href'=>'almacen'],
    'oficial'    => ['label'=>'Oficial',     'icon'=>'fa-user-shield',   'color'=>'purple', 'href'=>'admin'],
];
?>
<div class="space-y-2">
<?php foreach ($results as $r):
    $tc = $typeConfig[$r['tipo']] ?? ['label'=>ucfirst($r['tipo']),'icon'=>'fa-circle','color'=>'gray','href'=>''];
    $isDetailLink = !empty($r['id']) && str_ends_with($tc['href'], '/');
    $href = BASE_URL . '/' . ($isDetailLink ? $tc['href'] . $r['id'] : $tc['href']);
?>
<a href="<?= $href ?>"
   class="flex items-center gap-4 bg-white rounded-xl shadow-sm border border-gray-200 px-5 py-3.5 hover:shadow-md transition-shadow">
    <div class="w-9 h-9 rounded-lg bg-<?= $tc['color'] ?>-50 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid <?= $tc['icon'] ?> text-<?= $tc['color'] ?>-600 text-sm"></i>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 truncate"><?= htmlspecialchars($r['nombre'], ENT_QUOTES,'UTF-8') ?></p>
        <p class="text-xs text-gray-400">
            <?= $tc['label'] ?>
            <?php if (!empty($r['codigo'])): ?>
            · <span class="font-mono"><?= htmlspecialchars($r['codigo'], ENT_QUOTES,'UTF-8') ?></span>
            <?php endif; ?>
            <?php if (!empty($r['extra'])): ?>
            · <?= htmlspecialchars($r['extra'], ENT_QUOTES,'UTF-8') ?>
            <?php endif; ?>
        </p>
    </div>
    <?php if (!empty($r['estado'])): ?>
    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
        <?= htmlspecialchars($r['estado'], ENT_QUOTES,'UTF-8') ?>
    </span>
    <?php endif; ?>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
