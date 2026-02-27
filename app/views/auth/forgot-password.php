<div class="w-full max-w-md px-6 py-10">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl mb-4 shadow-lg">
            <i class="fa-solid fa-shield-halved text-white text-3xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-white">IDGuns</h1>
        <p class="text-gray-400 text-sm mt-1">Control de Armas &amp; Activos</p>
    </div>

    <!-- Card -->
    <div class="bg-gray-800 rounded-2xl shadow-xl px-8 py-8 border border-gray-700">
        <h2 class="text-lg font-semibold text-white mb-6">
            <i class="fa-solid fa-key mr-2 text-indigo-400"></i>Recuperar Contraseña
        </h2>

        <?php if (!empty($error)): ?>
        <div class="flex items-center gap-2 mb-5 px-4 py-3 bg-red-900/50 border border-red-700 rounded-lg text-red-300 text-sm">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($info)): ?>
        <div class="flex items-center gap-2 mb-5 px-4 py-3 bg-green-900/50 border border-green-700 rounded-lg text-green-300 text-sm">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/recuperar-contrasena" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fa-solid fa-envelope mr-1 text-gray-500"></i>Correo Electrónico
                </label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5
                              placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                       placeholder="correo@ejemplo.com">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg
                           transition-colors text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                <i class="fa-solid fa-paper-plane mr-2"></i>Enviar instrucciones
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="<?= BASE_URL ?>/login" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i>Volver al inicio de sesión
            </a>
        </div>
    </div>

    <p class="text-center text-gray-600 text-xs mt-6">
        <?= APP_NAME ?> &mdash; Secretaría de Seguridad Ciudadana de Querétaro
    </p>
</div>
