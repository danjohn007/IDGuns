<?php
$currentTab = $tab ?? 'general';
$tabs = [
    'general'   => ['icon'=>'fa-house',          'label'=>'General'],
    'email'     => ['icon'=>'fa-envelope',        'label'=>'Email'],
    'apariencia'=> ['icon'=>'fa-palette',         'label'=>'Apariencia'],
    'iot'       => ['icon'=>'fa-microchip',       'label'=>'Dispositivos IoT'],
    'qr'        => ['icon'=>'fa-qrcode',          'label'=>'QR API'],
    'gps'       => ['icon'=>'fa-location-dot',    'label'=>'GPS Tracker'],
    'chatbot'   => ['icon'=>'fa-robot',           'label'=>'Chatbot'],
    'pyspark'   => ['icon'=>'fa-fire',            'label'=>'PySpark API'],
    'catalogos' => ['icon'=>'fa-list',            'label'=>'Catálogos'],
    'bitacora_acciones' => ['icon'=>'fa-list-check', 'label'=>'Bitácora de Acciones'],
    'errores'   => ['icon'=>'fa-bug',             'label'=>'Monitor de Errores'],
    'paypal'    => ['icon'=>'fa-paypal',          'label'=>'PayPal'],
];
$s = $settings ?? [];
?>
<div class="flex gap-6">
    <!-- Sidebar tabs -->
    <nav class="w-52 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2 sticky top-4">
            <?php foreach ($tabs as $key=>$info): ?>
            <a href="<?= BASE_URL ?>/configuracion?tab=<?= $key ?>"
               class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors <?= $currentTab===$key ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                <i class="fa-solid <?= $info['icon'] ?> w-4 text-center text-xs"></i>
                <?= $info['label'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- Content -->
    <div class="flex-1 min-w-0">

        <!-- General -->
        <?php if ($currentTab === 'general'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Configuración General</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="general">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Sistema</label>
                        <input type="text" name="app_nombre" value="<?= htmlspecialchars($s['app_nombre']??APP_NAME, ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono de Contacto</label>
                        <input type="text" name="app_telefono" value="<?= htmlspecialchars($s['app_telefono']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horario de Operación</label>
                        <input type="text" name="app_horario" placeholder="Ej. 24/7" value="<?= htmlspecialchars($s['app_horario']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" name="app_direccion" value="<?= htmlspecialchars($s['app_direccion']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Email -->
        <?php elseif ($currentTab === 'email'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Configuración de Correo SMTP</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="email">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Host SMTP</label>
                        <input type="text" name="smtp_host" value="<?= htmlspecialchars($s['smtp_host']??'smtp.gmail.com', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Puerto</label>
                        <input type="number" name="smtp_port" value="<?= htmlspecialchars($s['smtp_port']??'587', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <input type="text" name="smtp_user" value="<?= htmlspecialchars($s['smtp_user']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="smtp_pass" placeholder="••••••••"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Remitente</label>
                        <input type="email" name="smtp_from" value="<?= htmlspecialchars($s['smtp_from']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Remitente</label>
                        <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($s['smtp_from_name']??APP_NAME, ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Apariencia -->
        <?php elseif ($currentTab === 'apariencia'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Apariencia</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="apariencia">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color Primario</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="color_primario" value="<?= htmlspecialchars($s['color_primario']??'#4f46e5', ENT_QUOTES,'UTF-8') ?>"
                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                            <input type="text" value="<?= htmlspecialchars($s['color_primario']??'#4f46e5', ENT_QUOTES,'UTF-8') ?>"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Color Secundario</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="color_secundario" value="<?= htmlspecialchars($s['color_secundario']??'#111827', ENT_QUOTES,'UTF-8') ?>"
                                   class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                            <input type="text" value="<?= htmlspecialchars($s['color_secundario']??'#111827', ENT_QUOTES,'UTF-8') ?>"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del Logo</label>
                        <input type="text" name="logo_url" value="<?= htmlspecialchars($s['logo_url']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="https://... o ruta relativa"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- IoT Devices -->
        <?php elseif ($currentTab === 'iot'): ?>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-5"><i class="fa-solid fa-microchip mr-2 text-blue-500"></i>Agregar Dispositivo IoT</h3>
                <form method="POST" action="<?= BASE_URL ?>/configuracion/iot/guardar">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Dispositivo</label>
                            <input type="text" name="nombre" required placeholder="Ej. Cámara Entrada Principal"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="hikvision">HikVision</option>
                                <option value="shelly">Shelly Cloud</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección IP</label>
                            <input type="text" name="ip" placeholder="192.168.1.100"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Puerto</label>
                            <input type="number" name="puerto" value="80"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                            <input type="text" name="usuario" placeholder="admin"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                            <input type="password" name="password_device"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input type="text" name="api_key"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Token</label>
                            <input type="text" name="token"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select name="activo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <input type="text" name="descripcion"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                            <i class="fa-solid fa-plus mr-2"></i>Agregar Dispositivo
                        </button>
                    </div>
                </form>
            </div>

            <!-- IoT devices list -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Dispositivos Registrados</h3>
                </div>
                <?php if (empty($iotDevices)): ?>
                <p class="text-center text-gray-400 py-8 text-sm">Sin dispositivos IoT registrados</p>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2 text-gray-500">Nombre</th>
                                <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                                <th class="text-left px-4 py-2 text-gray-500">IP : Puerto</th>
                                <th class="text-left px-4 py-2 text-gray-500">Estado</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <?php foreach ($iotDevices as $d): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($d['nombre'], ENT_QUOTES,'UTF-8') ?></td>
                            <td class="px-4 py-2">
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full"><?= htmlspecialchars(ucfirst($d['tipo']), ENT_QUOTES,'UTF-8') ?></span>
                            </td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-600"><?= htmlspecialchars($d['ip']??'—', ENT_QUOTES,'UTF-8') ?>:<?= $d['puerto']??80 ?></td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center gap-1 text-xs <?= $d['activo'] ? 'text-green-600' : 'text-gray-400' ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $d['activo'] ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                                    <?= $d['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/configuracion/iot/eliminar/<?= $d['id'] ?>"
                                   onclick="return confirm('¿Eliminar dispositivo?')"
                                   class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- QR API -->
        <?php elseif ($currentTab === 'qr'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Configuración de API QR</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="qr">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key QR</label>
                        <input type="text" name="qr_api_key" value="<?= htmlspecialchars($s['qr_api_key']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del Servicio QR</label>
                        <input type="url" name="qr_api_url" value="<?= htmlspecialchars($s['qr_api_url']??'https://api.qr-code-generator.com/v1', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- GPS -->
        <?php elseif ($currentTab === 'gps'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-1">Configuración GPS Tracker — Traccar</h3>
            <p class="text-xs text-gray-500 mb-5">
                Credenciales del servidor <a href="https://www.traccar.org" target="_blank" class="text-indigo-600 hover:underline">Traccar</a>.
                <a href="<?= BASE_URL ?>/geolocalizacion" class="ml-2 text-indigo-600 hover:underline">
                    <i class="fa-solid fa-map-location-dot mr-1"></i>Ver mapa de geolocalización
                </a>
            </p>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="gps">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del Servidor Traccar</label>
                        <input type="url" name="traccar_url" value="<?= htmlspecialchars($s['traccar_url']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. http://demo4.traccar.org"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario Traccar</label>
                        <input type="text" name="traccar_usuario" value="<?= htmlspecialchars($s['traccar_usuario']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="admin@example.com"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña Traccar</label>
                        <input type="password" name="traccar_password" value="<?= htmlspecialchars($s['traccar_password']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">API Key GPS (otros servicios)</label>
                        <input type="text" name="gps_api_key" value="<?= htmlspecialchars($s['gps_api_key']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo de Actualización (seg)</label>
                        <input type="number" name="gps_intervalo" min="10" value="<?= htmlspecialchars($s['gps_intervalo']??'30', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Chatbot -->
        <?php elseif ($currentTab === 'chatbot'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Configuración Chatbot WhatsApp</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="chatbot">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Token del Chatbot</label>
                        <input type="text" name="chatbot_token" value="<?= htmlspecialchars($s['chatbot_token']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número WhatsApp</label>
                        <input type="text" name="chatbot_numero" placeholder="+52 442 000 0000" value="<?= htmlspecialchars($s['chatbot_numero']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="chatbot_activo" value="1" id="chatbotActivo" <?= ($s['chatbot_activo']??'')=='1'?'checked':'' ?>
                               class="rounded border-gray-300 text-indigo-600">
                        <label for="chatbotActivo" class="text-sm font-medium text-gray-700">Chatbot Activo</label>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Bitácora de acciones -->
        <?php elseif ($currentTab === 'bitacora_acciones'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-list-check mr-2 text-indigo-500"></i>Bitácora de Acciones del Sistema</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50"><tr>
                        <th class="text-left px-4 py-2 text-gray-500">Fecha</th>
                        <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                        <th class="text-left px-4 py-2 text-gray-500">Activo</th>
                        <th class="text-left px-4 py-2 text-gray-500">Responsable</th>
                        <th class="text-left px-4 py-2 text-gray-500">Descripción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                    <?php
                    try {
                        $logModel = new LogEntry();
                        $logEntries = $logModel->getWithDetails([], 30, 0);
                        foreach ($logEntries as $le):
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-500"><?= date('d/m H:i', strtotime($le['fecha'])) ?></td>
                        <td class="px-4 py-2 capitalize font-medium text-gray-700"><?= htmlspecialchars($le['tipo'], ENT_QUOTES,'UTF-8') ?></td>
                        <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($le['activo_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                        <td class="px-4 py-2 text-gray-600"><?= htmlspecialchars($le['responsable_nombre']??'—', ENT_QUOTES,'UTF-8') ?></td>
                        <td class="px-4 py-2 text-gray-500 max-w-[300px] truncate"><?= htmlspecialchars($le['descripcion']??'', ENT_QUOTES,'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; } catch(\Exception $ex) { echo '<tr><td colspan="5" class="px-4 py-4 text-center text-gray-400">Sin datos</td></tr>'; } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Error monitor -->
        <?php elseif ($currentTab === 'errores'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700"><i class="fa-solid fa-bug mr-2 text-red-500"></i>Monitor de Errores del Sistema</h3>
            </div>
            <?php if (empty($errors)): ?>
            <div class="py-10 text-center text-green-600 text-sm">
                <i class="fa-solid fa-circle-check text-3xl mb-2 block"></i>No se han registrado errores
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50"><tr>
                        <th class="text-left px-4 py-2 text-gray-500">Fecha</th>
                        <th class="text-left px-4 py-2 text-gray-500">Tipo</th>
                        <th class="text-left px-4 py-2 text-gray-500">Mensaje</th>
                        <th class="text-left px-4 py-2 text-gray-500">Archivo</th>
                        <th class="text-left px-4 py-2 text-gray-500">Usuario</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                    <?php foreach ($errors as $e): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-500 whitespace-nowrap"><?= date('d/m H:i', strtotime($e['created_at']??'now')) ?></td>
                        <td class="px-4 py-2"><span class="bg-red-100 text-red-700 px-2 py-0.5 rounded font-medium"><?= htmlspecialchars($e['tipo']??'', ENT_QUOTES,'UTF-8') ?></span></td>
                        <td class="px-4 py-2 text-gray-700 max-w-[250px] truncate"><?= htmlspecialchars($e['mensaje']??'', ENT_QUOTES,'UTF-8') ?></td>
                        <td class="px-4 py-2 font-mono text-gray-400 text-xs"><?= htmlspecialchars($e['archivo']??'', ENT_QUOTES,'UTF-8') ?><?= !empty($e['linea'])?':'.$e['linea']:'' ?></td>
                        <td class="px-4 py-2 text-gray-500"><?= htmlspecialchars($e['usuario_nombre']??'Sistema', ENT_QUOTES,'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- PayPal -->
        <?php elseif ($currentTab === 'paypal'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">Configuración PayPal</h3>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="paypal">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                        <input type="text" name="paypal_client_id" value="<?= htmlspecialchars($s['paypal_client_id']??'', ENT_QUOTES,'UTF-8') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secret</label>
                        <input type="password" name="paypal_secret"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modo</label>
                        <select name="paypal_modo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="sandbox" <?= ($s['paypal_modo']??'sandbox')==='sandbox'?'selected':'' ?>>Sandbox (Pruebas)</option>
                            <option value="live"    <?= ($s['paypal_modo']??'')==='live'?'selected':'' ?>>Live (Producción)</option>
                        </select>
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
        <!-- PySpark API -->
        <?php elseif ($currentTab === 'pyspark'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-5">
                <i class="fa-solid fa-fire mr-2 text-orange-500"></i>Configuración PySpark API
            </h3>
            <p class="text-sm text-gray-500 mb-4">
                Configura la URL y el token del servicio de análisis basado en PySpark para habilitar el módulo de Análisis de Datos.
            </p>
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                <input type="hidden" name="tab" value="pyspark">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL del API PySpark</label>
                        <input type="url" name="pyspark_url"
                               value="<?= htmlspecialchars($s['pyspark_url']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Ej. http://mi-servidor:8080/api/analyze"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-400 mt-1">URL base del endpoint REST del servicio PySpark.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Token de Autenticación</label>
                        <input type="text" name="pyspark_token"
                               value="<?= htmlspecialchars($s['pyspark_token']??'', ENT_QUOTES,'UTF-8') ?>"
                               placeholder="Bearer token o API key"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>

        <!-- Catálogos -->
        <?php elseif ($currentTab === 'catalogos'): ?>
        <?php
        $catalogSections = [
            'activos_categoria'     => ['label'=>'Categorías de Activos',      'items'=>$catActivos     ?? []],
            'suministros_categoria' => ['label'=>'Categorías de Suministros',  'items'=>$catSuministros ?? []],
            'vehiculos_tipo'        => ['label'=>'Tipos de Vehículos',         'items'=>$catVehiculos   ?? []],
            'personal_cargo'        => ['label'=>'Cargos del Personal',        'items'=>$catCargos      ?? []],
        ];
        ?>
        <div class="space-y-6">
            <?php foreach ($catalogSections as $tipo => $section): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-800 mb-4"><?= htmlspecialchars($section['label'], ENT_QUOTES,'UTF-8') ?></h3>

                <!-- Add form -->
                <form method="POST" action="<?= BASE_URL ?>/configuracion/catalogo/guardar" class="flex gap-2 mb-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES,'UTF-8') ?>">
                    <input type="hidden" name="catalog_tipo" value="<?= $tipo ?>">
                    <input type="text" name="etiqueta" placeholder="Etiqueta (ej. Arma)" required
                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" name="clave" placeholder="Clave (ej. arma)" required
                           class="w-36 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="number" name="orden" placeholder="Orden" value="0" min="0"
                           class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i>Agregar
                    </button>
                </form>

                <!-- List -->
                <?php if (empty($section['items'])): ?>
                <p class="text-sm text-gray-400 text-center py-4">Sin entradas. Agrega la primera arriba.</p>
                <?php else: ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-2 text-gray-500 font-medium">Etiqueta</th>
                                <th class="text-left px-4 py-2 text-gray-500 font-medium">Clave</th>
                                <th class="text-left px-4 py-2 text-gray-500 font-medium">Orden</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <?php foreach ($section['items'] as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-gray-800"><?= htmlspecialchars($item['etiqueta'], ENT_QUOTES,'UTF-8') ?></td>
                            <td class="px-4 py-2 font-mono text-xs text-gray-600"><?= htmlspecialchars($item['clave'], ENT_QUOTES,'UTF-8') ?></td>
                            <td class="px-4 py-2 text-gray-500"><?= (int)$item['orden'] ?></td>
                            <td class="px-4 py-2 text-right">
                                <a href="<?= BASE_URL ?>/configuracion/catalogo/eliminar/<?= (int)$item['id'] ?>"
                                   onclick="return confirm('¿Eliminar esta entrada del catálogo?')"
                                   class="text-red-500 hover:text-red-700 text-xs">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- end content -->
</div>
