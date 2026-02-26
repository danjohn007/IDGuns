<?php
$catData     = json_decode($assetsByCategory, true) ?? [];
$statusData  = json_decode($assetsByStatus,   true) ?? [];
$vehType     = json_decode($vehiclesByType,   true) ?? [];
$vehStatus   = json_decode($vehiclesByStatus, true) ?? [];
$wepType     = json_decode($weaponsByType,    true) ?? [];
$movData     = json_decode($movimientos30,    true) ?? [];
?>

<!-- PySpark API Panel -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-700">
            <i class="fa-solid fa-fire mr-2 text-orange-500"></i>API de Análisis PySpark
        </h3>
        <?php if (empty($pysparkUrl)): ?>
        <a href="<?= BASE_URL ?>/configuracion?tab=pyspark"
           class="text-xs text-indigo-600 hover:underline">Configurar API</a>
        <?php else: ?>
        <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full font-medium">
            <i class="fa-solid fa-circle text-xs mr-1"></i>Configurado
        </span>
        <?php endif; ?>
    </div>

    <?php if (empty($pysparkUrl)): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        La URL del API de PySpark no está configurada.
        Ve a <a href="<?= BASE_URL ?>/configuracion?tab=pyspark" class="underline font-medium">Configuración → PySpark</a>
        para ingresar la URL y token del servicio.
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Consulta PySpark</label>
            <textarea id="pysparkQuery" rows="4" placeholder="Ingresa tu consulta o deja vacío para obtener un resumen general…"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono"></textarea>
            <button onclick="runPySpark()"
                    class="mt-2 bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600 transition-colors">
                <i class="fa-solid fa-play mr-1"></i>Ejecutar Análisis
            </button>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Resultado</label>
            <div id="pysparkResult"
                 class="bg-gray-900 text-green-400 rounded-lg px-4 py-3 text-xs font-mono h-28 overflow-auto">
                <?php if (!empty($pysparkData)): ?>
                <?= htmlspecialchars(json_encode($pysparkData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                <span class="text-gray-500">// Esperando consulta…</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Charts grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">

    <!-- Assets by category -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-chart-pie mr-2 text-indigo-500"></i>Activos por Categoría
        </h3>
        <div id="chartActCat"></div>
    </div>

    <!-- Assets by status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-chart-pie mr-2 text-blue-500"></i>Activos por Estado
        </h3>
        <div id="chartActStatus"></div>
    </div>

    <!-- Vehicles by type -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-chart-bar mr-2 text-green-500"></i>Vehículos por Tipo
        </h3>
        <div id="chartVehType"></div>
    </div>

    <!-- Weapons by type -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-chart-bar mr-2 text-red-500"></i>Armas por Tipo
        </h3>
        <div id="chartWepType"></div>
    </div>

    <!-- Warehouse movements 30 days -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:col-span-2">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">
            <i class="fa-solid fa-chart-line mr-2 text-teal-500"></i>Movimientos de Almacén (30 días)
        </h3>
        <div id="chartMov30"></div>
    </div>

</div>

<!-- Recent assets table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">
            <i class="fa-solid fa-clock-rotate-left mr-2 text-gray-500"></i>Últimos Activos Registrados
        </h3>
        <a href="<?= BASE_URL ?>/inventario" class="text-xs text-indigo-600 hover:underline">Ver todos</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Código</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Nombre</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Categoría</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Estado</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            <?php foreach ($recentActivos as $a): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-2 font-mono text-gray-600"><?= htmlspecialchars($a['codigo'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($a['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2 text-gray-500"><?= htmlspecialchars($a['categoria'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2 text-gray-500"><?= htmlspecialchars($a['estado'], ENT_QUOTES,'UTF-8') ?></td>
                <td class="px-4 py-2 text-gray-400"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function(){
    var catMap = {arma:'Armas',vehiculo:'Vehículos',equipo_computo:'Eq. Cómputo',equipo_oficina:'Eq. Oficina',bien_mueble:'Bien Mueble'};

    // Assets by category
    var catData = <?= $assetsByCategory ?>;
    if (catData.length) {
        new ApexCharts(document.getElementById('chartActCat'), {
            chart: {type:'donut',height:220},
            series: catData.map(function(r){return parseInt(r.total);}),
            labels: catData.map(function(r){return catMap[r.categoria]||r.categoria;}),
            colors: ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444'],
            legend: {position:'bottom',fontSize:'11px'},
            dataLabels:{enabled:true}
        }).render();
    }

    // Assets by status
    var statusData = <?= $assetsByStatus ?>;
    var statusColors = {activo:'#10b981',baja:'#ef4444',mantenimiento:'#f59e0b'};
    if (statusData.length) {
        new ApexCharts(document.getElementById('chartActStatus'), {
            chart: {type:'donut',height:220},
            series: statusData.map(function(r){return parseInt(r.total);}),
            labels: statusData.map(function(r){return r.estado;}),
            colors: statusData.map(function(r){return statusColors[r.estado]||'#94a3b8';}),
            legend: {position:'bottom',fontSize:'11px'},
            dataLabels:{enabled:true}
        }).render();
    }

    // Vehicles by type
    var vehType = <?= $vehiclesByType ?>;
    if (vehType.length) {
        new ApexCharts(document.getElementById('chartVehType'), {
            chart: {type:'bar',height:200,toolbar:{show:false}},
            series: [{name:'Vehículos',data:vehType.map(function(r){return parseInt(r.total);})}],
            xaxis: {categories:vehType.map(function(r){return r.tipo;})},
            colors:['#3b82f6'],
            dataLabels:{enabled:false}
        }).render();
    }

    // Weapons by type
    var wepType = <?= $weaponsByType ?>;
    if (wepType.length) {
        new ApexCharts(document.getElementById('chartWepType'), {
            chart: {type:'bar',height:200,toolbar:{show:false}},
            series: [{name:'Armas',data:wepType.map(function(r){return parseInt(r.total);})}],
            xaxis: {categories:wepType.map(function(r){return r.tipo;})},
            colors:['#ef4444'],
            dataLabels:{enabled:false}
        }).render();
    }

    // Warehouse movements
    var movData = <?= $movimientos30 ?>;
    var days = [...new Set(movData.map(function(r){return r.dia;}))];
    var entradas = days.map(function(d){var r=movData.find(function(x){return x.dia===d&&x.tipo==='entrada';});return r?parseInt(r.total):0;});
    var salidas  = days.map(function(d){var r=movData.find(function(x){return x.dia===d&&x.tipo==='salida';});return r?parseInt(r.total):0;});
    var fmtDays  = days.map(function(d){var p=d.split('-');return p[2]+'/'+p[1];});
    if (days.length) {
        new ApexCharts(document.getElementById('chartMov30'), {
            chart: {type:'area',height:220,toolbar:{show:false}},
            series: [{name:'Entradas',data:entradas},{name:'Salidas',data:salidas}],
            xaxis: {categories:fmtDays,labels:{style:{fontSize:'10px'}}},
            colors: ['#10b981','#ef4444'],
            legend: {position:'top',fontSize:'12px'},
            dataLabels:{enabled:false},
            grid:{borderColor:'#f1f5f9'}
        }).render();
    } else {
        document.getElementById('chartMov30').innerHTML = '<p class="text-center text-gray-400 py-10 text-sm">Sin movimientos en los últimos 30 días</p>';
    }
})();

<?php if (!empty($pysparkUrl)): ?>
function runPySpark() {
    var query = document.getElementById('pysparkQuery').value;
    var result = document.getElementById('pysparkResult');
    result.innerHTML = '<span class="text-yellow-400">// Procesando…</span>';

    fetch('<?= BASE_URL ?>/analytics/pyspark', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token=<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>&query=' + encodeURIComponent(query)
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        result.innerHTML = data.error
            ? '<span class="text-red-400">Error: ' + data.error + '</span>'
            : '<span>' + JSON.stringify(data.result, null, 2) + '</span>';
    })
    .catch(function(e) {
        result.innerHTML = '<span class="text-red-400">Error de conexión: ' + e.message + '</span>';
    });
}
<?php endif; ?>
</script>
