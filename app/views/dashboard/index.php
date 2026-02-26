<!-- Global search bar -->
<div class="mb-5">
    <form method="GET" action="<?= BASE_URL ?>/dashboard/buscar" class="flex gap-2 max-w-xl">
        <div class="relative flex-1">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" name="q" placeholder="Buscar en todo el sistema (activos, vehículos, suministros…)"
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            Buscar
        </button>
    </form>
</div>

<!-- Stats cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?php
    $cards = [
        ['label'=>'Total Activos',         'value'=>$totalActivos,    'icon'=>'fa-boxes-stacked', 'color'=>'indigo', 'href'=>'inventario'],
        ['label'=>'Armas Operativas',       'value'=>$armasOperativas, 'icon'=>'fa-gun',            'color'=>'blue',   'href'=>'inventario?categoria=arma'],
        ['label'=>'Vehículos Operativos',   'value'=>$vehiculosOper,   'icon'=>'fa-car',            'color'=>'green',  'href'=>'vehiculos'],
        ['label'=>'Alertas de Stock',       'value'=>$alertasStock,    'icon'=>'fa-triangle-exclamation','color'=>'red','href'=>'almacen?alerta=1'],
    ];
    $colorMap = [
        'indigo'=>['bg'=>'bg-indigo-50','icon'=>'text-indigo-600','ring'=>'ring-indigo-200'],
        'blue'  =>['bg'=>'bg-blue-50',  'icon'=>'text-blue-600',  'ring'=>'ring-blue-200'],
        'green' =>['bg'=>'bg-green-50', 'icon'=>'text-green-600', 'ring'=>'ring-green-200'],
        'red'   =>['bg'=>'bg-red-50',   'icon'=>'text-red-600',   'ring'=>'ring-red-200'],
    ];
    foreach ($cards as $c):
        $cl = $colorMap[$c['color']];
    ?>
    <a href="<?= BASE_URL ?>/<?= $c['href'] ?>"
       class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
        <div class="<?= $cl['bg'] ?> p-3 rounded-xl ring-1 <?= $cl['ring'] ?>">
            <i class="fa-solid <?= $c['icon'] ?> <?= $cl['icon'] ?> text-xl w-6 text-center"></i>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= $c['value'] ?></p>
            <p class="text-xs text-gray-500 font-medium"><?= $c['label'] ?></p>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <!-- Asset categories pie chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">
            <i class="fa-solid fa-chart-pie mr-2 text-indigo-500"></i>Activos por Categoría
        </h3>
        <div id="chartCategoria"></div>
    </div>

    <!-- Warehouse movements bar chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">
            <i class="fa-solid fa-chart-bar mr-2 text-blue-500"></i>Movimientos de Almacén (7 días)
        </h3>
        <div id="chartMovimientos"></div>
    </div>
</div>

<!-- Bottom row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Recent logbook -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-book-open mr-2 text-green-500"></i>Últimas Entradas de Bitácora
            </h3>
            <a href="<?= BASE_URL ?>/bitacora" class="text-xs text-indigo-600 hover:underline">Ver todo</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Fecha</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Tipo</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Activo</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Oficial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                <?php if (empty($recentBitacora)): ?>
                <tr><td colspan="4" class="text-center py-8 text-gray-400">Sin entradas aún</td></tr>
                <?php else: ?>
                <?php foreach ($recentBitacora as $entry):
                    $tipoColors = ['entrada'=>'green','salida'=>'blue','asignacion'=>'indigo','devolucion'=>'yellow','incidencia'=>'red'];
                    $col = $tipoColors[$entry['tipo']] ?? 'gray';
                    $tipoBadge = "bg-{$col}-100 text-{$col}-700";
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-500"><?= date('d/m H:i', strtotime($entry['fecha'])) ?></td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $col ?>-100 text-<?= $col ?>-700">
                            <?= htmlspecialchars(ucfirst($entry['tipo']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td class="px-4 py-2 text-gray-700 font-medium truncate max-w-[150px]"><?= htmlspecialchars($entry['activo_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($entry['oficial_nombre'] ?? $entry['responsable_nombre'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low stock alerts -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i>Stock Crítico
            </h3>
            <a href="<?= BASE_URL ?>/almacen?alerta=1" class="text-xs text-indigo-600 hover:underline">Ver todo</a>
        </div>
        <?php if (empty($lowStock)): ?>
        <div class="py-8 text-center text-sm text-green-600">
            <i class="fa-solid fa-circle-check text-2xl mb-2 block"></i>Sin alertas de stock
        </div>
        <?php else: ?>
        <ul class="divide-y divide-gray-50 text-sm">
        <?php foreach (array_slice($lowStock, 0, 6) as $s): ?>
            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                <div class="min-w-0">
                    <p class="font-medium text-gray-700 truncate"><?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars(ucfirst($s['categoria']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="text-right ml-2 flex-shrink-0">
                    <span class="text-red-600 font-bold"><?= $s['stock_actual'] ?></span>
                    <span class="text-gray-400 text-xs"> / <?= $s['stock_minimo'] ?> mín</span>
                </div>
            </li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<!-- ApexCharts scripts -->
<script>
(function(){
    // Category pie chart
    var catLabels = <?= $catLabels ?>;
    var catValues = <?= $catValues ?>;
    var labelMap  = {arma:'Armas',vehiculo:'Vehículos',equipo_computo:'Eq. Cómputo',equipo_oficina:'Eq. Oficina',bien_mueble:'Bien Mueble'};
    var mappedLabels = catLabels.map(function(l){ return labelMap[l] || l; });

    if (catValues.length > 0) {
        new ApexCharts(document.getElementById('chartCategoria'), {
            chart: { type:'donut', height:240 },
            series: catValues,
            labels: mappedLabels,
            colors: ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444'],
            legend: { position:'bottom', fontSize:'12px' },
            dataLabels: { enabled: true },
            plotOptions: { pie: { donut: { size:'65%' } } }
        }).render();
    } else {
        document.getElementById('chartCategoria').innerHTML =
          '<p class="text-center text-gray-400 py-10 text-sm">Sin datos</p>';
    }

    // Warehouse movements bar chart
    var movData = <?= $movimientosDia ?>;
    var days    = [...new Set(movData.map(function(r){ return r.dia; }))];
    var entradas= days.map(function(d){ var r=movData.find(function(x){return x.dia===d&&x.tipo==='entrada';}); return r?parseInt(r.total):0; });
    var salidas = days.map(function(d){ var r=movData.find(function(x){return x.dia===d&&x.tipo==='salida';}); return r?parseInt(r.total):0; });
    var fmtDays = days.map(function(d){ var p=d.split('-'); return p[2]+'/'+p[1]; });

    if (days.length > 0) {
        new ApexCharts(document.getElementById('chartMovimientos'), {
            chart: { type:'bar', height:240, toolbar:{ show:false } },
            series: [
                { name:'Entradas', data: entradas },
                { name:'Salidas',  data: salidas  }
            ],
            xaxis: { categories: fmtDays, labels:{ style:{ fontSize:'11px' } } },
            colors: ['#10b981','#ef4444'],
            plotOptions: { bar:{ columnWidth:'55%', borderRadius:3 } },
            legend: { position:'top', fontSize:'12px' },
            dataLabels: { enabled:false },
            grid: { borderColor:'#f1f5f9' }
        }).render();
    } else {
        document.getElementById('chartMovimientos').innerHTML =
          '<p class="text-center text-gray-400 py-10 text-sm">Sin movimientos recientes</p>';
    }
})();
</script>
