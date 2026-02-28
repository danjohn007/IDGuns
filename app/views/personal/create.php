<div class="max-w-xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/personal" class="text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-lg font-semibold text-gray-800">Registrar Nuevo Personal</h2>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/personal/guardar" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Datos del Personal</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre(s) *</label>
                    <input type="text" name="nombre" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Arturo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Apellidos</label>
                    <input type="text" name="apellidos"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Ríos Mendoza">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargo / Rango</label>
                    <?php if (!empty($cargos)): ?>
                    <select name="cargo"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Sin cargo --</option>
                        <?php foreach ($cargos as $c): ?>
                        <option value="<?= htmlspecialchars($c['etiqueta'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($c['etiqueta'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <input type="text" name="cargo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. Comandante, Cabo, Sargento">
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° Empleado / Placa</label>
                    <input type="text" name="numero_empleado"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. P-1024">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                    <input type="tel" name="telefono" maxlength="10" pattern="[0-9]{10}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej. 4421234567">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="correo@ejemplo.com">
                </div>
            </div>
        </div>

        <div class="flex gap-3 justify-end">
            <a href="<?= BASE_URL ?>/personal"
               class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
            </button>
        </div>
    </form>
</div>
