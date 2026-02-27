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
        <h2 class="text-lg font-semibold text-white mb-6">Iniciar Sesión</h2>

        <?php if (!empty($error)): ?>
        <div class="flex items-center gap-2 mb-5 px-4 py-3 bg-red-900/50 border border-red-700 rounded-lg text-red-300 text-sm">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fa-solid fa-user mr-1 text-gray-500"></i>Usuario
                </label>
                <input type="text" id="username" name="username" required autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5
                              placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                       placeholder="nombre de usuario">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fa-solid fa-lock mr-1 text-gray-500"></i>Contraseña
                </label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5
                              placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                       placeholder="••••••••">
            </div>

            <!-- Math Captcha -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">
                    <i class="fa-solid fa-shield-halved mr-1 text-gray-500"></i>Verificación Humana
                </label>
                <div class="flex items-center gap-3">
                    <span class="text-white font-semibold text-base bg-gray-700 px-4 py-2.5 rounded-lg border border-gray-600 select-none">
                        <?= (int)($captchaA ?? 0) ?> + <?= (int)($captchaB ?? 0) ?> =
                    </span>
                    <input type="number" name="captcha_answer" required min="0" max="18"
                           class="flex-1 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2.5
                                  placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm"
                           placeholder="Respuesta">
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg
                           transition-colors text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Ingresar al Sistema
            </button>

            <div class="text-center">
                <a href="<?= BASE_URL ?>/recuperar-contrasena"
                   class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                    <i class="fa-solid fa-key mr-1"></i>¿Olvidó su contraseña?
                </a>
            </div>
        </form>
    </div>

    <p class="text-center text-gray-600 text-xs mt-6">
        <?= APP_NAME ?> &mdash; Secretaría de Seguridad Ciudadana de Querétaro
    </p>
</div>
