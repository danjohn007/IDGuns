<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center gap-3 mb-2">
        <a href="<?= BASE_URL ?>/admin" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Reportes del Sistema</h2>
    </div>

    <!-- Assets by category -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-boxes-stacked mr-2 text-indigo-500"></i>Activos por Categoría y Estado</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Categoría</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Estado</th>
                        <th class="text-right px-4 py-2 text-gray-500 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php
                $catMap   = ['arma'=>'Arma','vehiculo'=>'Vehículo','equipo_computo'=>'Eq. Cómputo','equipo_oficina'=>'Eq. Oficina','bien_mueble'=>'Bien Mueble'];
                $statColors = ['activo'=>'green','baja'=>'red','mantenimiento'=>'yellow'];
                foreach ($assetsByCategory as $row):
                    $sc = $statColors[$row['estado']]??'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-700"><?= $catMap[$row['categoria']]??$row['categoria'] ?></td>
                    <td class="px-4 py-2">
                        <span class="bg-<?= $sc ?>-100 text-<?= $sc ?>-700 text-xs px-2 py-0.5 rounded-full"><?= ucfirst($row['estado']) ?></span>
                    </td>
                    <td class="px-4 py-2 text-right font-bold text-gray-800"><?= $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Weapons summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-gun mr-2 text-red-500"></i>Resumen de Armas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Tipo</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Calibre</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Estado</th>
                        <th class="text-right px-4 py-2 text-gray-500 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($weaponsSummary as $w):
                    $wc = ['operativa'=>'green','mantenimiento'=>'yellow','baja'=>'red'][$w['estado']]??'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 capitalize font-medium text-gray-700"><?= htmlspecialchars($w['tipo'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($w['calibre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2">
                        <span class="bg-<?= $wc ?>-100 text-<?= $wc ?>-700 text-xs px-2 py-0.5 rounded-full"><?= ucfirst($w['estado']) ?></span>
                    </td>
                    <td class="px-4 py-2 text-right font-bold text-gray-800"><?= $w['total'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Vehicles summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-car mr-2 text-blue-500"></i>Resumen de Vehículos</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Tipo</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-medium">Estado</th>
                        <th class="text-right px-4 py-2 text-gray-500 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($vehiclesSummary as $v):
                    $vc = ['operativo'=>'green','taller'=>'yellow','baja'=>'red'][$v['estado']]??'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 capitalize font-medium text-gray-700"><?= htmlspecialchars($v['tipo'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2">
                        <span class="bg-<?= $vc ?>-100 text-<?= $vc ?>-700 text-xs px-2 py-0.5 rounded-full"><?= ucfirst($v['estado']) ?></span>
                    </td>
                    <td class="px-4 py-2 text-right font-bold text-gray-800"><?= $v['total'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low stock -->
    <?php if (!empty($lowStock)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-red-100 bg-red-50">
            <h3 class="text-sm font-semibold text-red-700"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Suministros con Stock Crítico</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500">Nombre</th>
                        <th class="text-left px-4 py-2 text-gray-500">Categoría</th>
                        <th class="text-right px-4 py-2 text-gray-500">Stock Actual</th>
                        <th class="text-right px-4 py-2 text-gray-500">Stock Mínimo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($lowStock as $s): ?>
                <tr class="hover:bg-red-50">
                    <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($s['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 capitalize text-gray-600"><?= htmlspecialchars($s['categoria'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2 text-right font-bold text-red-600"><?= $s['stock_actual'] ?></td>
                    <td class="px-4 py-2 text-right text-gray-500"><?= $s['stock_minimo'] ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monthly movements -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-chart-line mr-2 text-green-500"></i>Movimientos Mensuales (Últimos 6 meses)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500">Mes</th>
                        <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                        <th class="text-right px-4 py-2 text-gray-500">Total Unidades</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($monthlyMovements as $m):
                    $mc = $m['tipo']==='entrada' ? 'green' : 'red';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($m['mes'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-2">
                        <span class="bg-<?= $mc ?>-100 text-<?= $mc ?>-700 text-xs px-2 py-0.5 rounded-full"><?= ucfirst($m['tipo']) ?></span>
                    </td>
                    <td class="px-4 py-2 text-right font-bold text-gray-800"><?= number_format($m['total']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
