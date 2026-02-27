<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3 mb-2">
        <h2 class="text-lg font-semibold text-gray-800">Mi Perfil</h2>
    </div>

    <!-- Profile Info Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold shadow">
                <?= strtoupper(substr($user['nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($user['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 capitalize mt-1">
                    <?= htmlspecialchars($user['rol'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>

        <h4 class="font-medium text-gray-700 mb-4 text-sm border-b border-gray-100 pb-2">
            <i class="fa-solid fa-pen mr-1 text-indigo-500"></i>Editar Información Personal
        </h4>

        <form method="POST" action="<?= BASE_URL ?>/perfil/actualizar" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                    <input type="text" name="nombre" required
                           value="<?= htmlspecialchars($user['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                    <input type="email" name="email"
                           value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar cambios
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h4 class="font-medium text-gray-700 mb-4 text-sm border-b border-gray-100 pb-2">
            <i class="fa-solid fa-lock mr-1 text-indigo-500"></i>Cambiar Contraseña
        </h4>
        <form method="POST" action="<?= BASE_URL ?>/perfil/cambiar-contrasena" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Actual</label>
                <input type="password" name="password_actual" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input type="password" name="password_nuevo" required minlength="6"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmar" required minlength="6"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-key mr-2"></i>Cambiar Contraseña
                </button>
            </div>
        </form>
    </div>
</div>
