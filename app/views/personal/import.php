<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= BASE_URL ?>/personal" class="text-gray-500 hover:text-gray-700"><i class="fa-solid fa-arrow-left"></i></a>
        <h2 class="text-lg font-semibold text-gray-800">Importación de Contactos desde CSV</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">

        <!-- Format info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
            <p class="font-semibold mb-2"><i class="fa-solid fa-circle-info mr-1"></i>Formato del archivo CSV</p>
            <p class="mb-1">El archivo debe tener las columnas en este orden (sin encabezado o con encabezado en la primera fila):</p>
            <code class="block bg-white border border-blue-200 rounded px-3 py-2 text-xs font-mono mt-2">
                nombre, apellidos, cargo, email, telefono, número_empleado
            </code>
            <p class="mt-2 text-xs text-blue-600">Solo <strong>nombre</strong> es obligatorio. Los demás campos son opcionales.</p>
        </div>

        <!-- Upload form -->
        <form method="POST" action="<?= BASE_URL ?>/personal/procesar-importar" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-file-csv mr-1 text-green-600"></i>Seleccionar archivo CSV
                    </label>
                    <input type="file" name="archivo" accept=".csv,text/csv" required
                           class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2
                                  file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0
                                  file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-upload mr-2"></i>Importar Contactos
                    </button>
                    <a href="<?= BASE_URL ?>/personal"
                       class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                </div>
            </div>
        </form>

        <!-- Example -->
        <div>
            <p class="text-xs font-medium text-gray-500 mb-2">Ejemplo de archivo CSV:</p>
            <pre class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-xs font-mono text-gray-600 overflow-x-auto">nombre,apellidos,cargo,email,telefono,numero_empleado
Juan,Pérez García,Comandante,jperez@ejemplo.com,+52 442 123 4567,EMP-001
María,González López,Sargento,mgonzalez@ejemplo.com,,EMP-002
Carlos,Ramírez,Policía,,+52 442 987 6543,EMP-003</pre>
        </div>
    </div>
</div>
