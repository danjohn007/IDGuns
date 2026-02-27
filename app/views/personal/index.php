<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Personal Registrado</h2>
            <p class="text-sm text-gray-500 mt-0.5"><?= count($personal) ?> registro(s) activo(s)</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
        <a href="<?= BASE_URL ?>/personal/crear"
           class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Agregar Personal
        </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($personal)): ?>
        <div class="text-center py-16 text-gray-400">
            <i class="fa-solid fa-users fa-3x mb-3 opacity-30"></i>
            <p class="font-medium text-gray-500">No hay personal registrado</p>
            <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
            <a href="<?= BASE_URL ?>/personal/crear"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-plus"></i> Agregar primer registro
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Cargo</th>
                        <th class="px-4 py-3 text-left">N° Empleado / Placa</th>
                        <th class="px-4 py-3 text-left">Teléfono</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
                        <th class="px-4 py-3 text-right">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <?php foreach ($personal as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400 font-mono text-xs"><?= $p['id'] ?></td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if (!empty($p['cargo'])): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                <?= htmlspecialchars($p['cargo'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">
                            <?= htmlspecialchars($p['numero_empleado'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="px-4 py-3"><?= htmlspecialchars($p['telefono'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars($p['email'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <?php if (in_array($_SESSION['user_role'] ?? '', ['superadmin', 'admin'])): ?>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/personal/editar/<?= $p['id'] ?>"
                               class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="<?= BASE_URL ?>/personal/eliminar/<?= $p['id'] ?>"
                               onclick="return confirm('¿Dar de baja a este personal?')"
                               class="ml-1 inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fa-solid fa-ban"></i> Baja
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
