<?php
$rolColors = ['superadmin'=>'red','admin'=>'indigo','almacen'=>'blue','bitacora'=>'green'];
$rolLabels = ['superadmin'=>'Super Admin','admin'=>'Admin','almacen'=>'Almacén','bitacora'=>'Bitácora'];
$showCreate= $create ?? false;
$editUser  = $editUser ?? null;
?>
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Gestión de Usuarios</h2>
        <?php if (in_array($_SESSION['user_role']??'', ['superadmin'])): ?>
        <a href="<?= BASE_URL ?>/admin/crear-usuario"
           class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
            <i class="fa-solid fa-plus mr-1"></i> Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <!-- Create / Edit form -->
    <?php if ($showCreate || $editUser): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-700 mb-4">
            <?= $editUser ? 'Editar Usuario' : 'Nuevo Usuario' ?>
        </h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/<?= $editUser ? 'actualizar-usuario' : 'guardar-usuario' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
            <?php if ($editUser): ?>
            <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                    <input type="text" name="nombre" required
                           value="<?= htmlspecialchars($editUser['nombre']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <?php if (!$editUser): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario *</label>
                    <input type="text" name="username" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($editUser['email']??'', ENT_QUOTES,'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <?= $editUser ? 'Nueva Contraseña (dejar en blanco para mantener)' : 'Contraseña *' ?>
                    </label>
                    <input type="password" name="password" <?= $editUser ? '' : 'required' ?>
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="rol" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <?php foreach ($rolLabels as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($editUser['rol']??'')===$k?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($editUser): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="activo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="1" <?= ($editUser['activo']??0)?'selected':'' ?>>Activo</option>
                        <option value="0" <?= !($editUser['activo']??0)?'selected':'' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex gap-3 mt-5 justify-end">
                <a href="<?= BASE_URL ?>/admin/usuarios" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                    <i class="fa-solid fa-floppy-disk mr-2"></i><?= $editUser ? 'Actualizar' : 'Crear Usuario' ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Users table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Nombre</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Usuario</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Correo</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Rol</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Estado</th>
                        <th class="text-left px-4 py-3 text-gray-500 font-medium">Creado</th>
                        <?php if (in_array($_SESSION['user_role']??'', ['superadmin'])): ?>
                        <th class="px-4 py-3"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php foreach ($users as $u):
                    $rc = $rolColors[$u['rol']] ?? 'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                <?= strtoupper(substr($u['nombre'],0,1)) ?>
                            </div>
                            <span class="font-medium text-gray-800"><?= htmlspecialchars($u['nombre'], ENT_QUOTES,'UTF-8') ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($u['username'], ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($u['email']??'—', ENT_QUOTES,'UTF-8') ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-<?= $rc ?>-100 text-<?= $rc ?>-700">
                            <?= $rolLabels[$u['rol']] ?? $u['rol'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 text-xs <?= $u['activo'] ? 'text-green-600' : 'text-gray-400' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $u['activo'] ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                            <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs"><?= isset($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?></td>
                    <?php if (in_array($_SESSION['user_role']??'', ['superadmin'])): ?>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="<?= BASE_URL ?>/admin/editar-usuario/<?= $u['id'] ?>"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium mr-3">
                            <i class="fa-solid fa-pen-to-square"></i> Editar
                        </a>
                        <?php if ($u['id'] != ($_SESSION['user_id']??0)): ?>
                        <a href="<?= BASE_URL ?>/admin/eliminar-usuario/<?= $u['id'] ?>"
                           onclick="return confirm('¿Eliminar usuario <?= htmlspecialchars($u['nombre'],ENT_QUOTES,'UTF-8') ?>?')"
                           class="text-red-500 hover:text-red-700 text-xs font-medium">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
