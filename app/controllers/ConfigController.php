<?php
class ConfigController extends BaseController
{
    private Setting $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $settings   = $this->settingModel->getAllGrouped();
        $iotDevices = $this->settingModel->getIotDevices();
        $errors     = $this->settingModel->getErrors(30);

        $tab = $_GET['tab'] ?? 'general';

        // Catalog data
        $catActivos     = [];
        $catSuministros = [];
        $catVehiculos   = [];
        try {
            $catActivos     = $this->settingModel->getCatalogByType('activos_categoria');
            $catSuministros = $this->settingModel->getCatalogByType('suministros_categoria');
            $catVehiculos   = $this->settingModel->getCatalogByType('vehiculos_tipo');
        } catch (\Throwable $e) {
            // catalogos table may not exist yet
        }

        $this->render('config/index', [
            'title'          => 'Configuración',
            'flash'          => $this->getFlash(),
            'settings'       => $settings,
            'iotDevices'     => $iotDevices,
            'errors'         => $errors,
            'csrf'           => $this->csrfToken(),
            'tab'            => $tab,
            'catActivos'     => $catActivos,
            'catSuministros' => $catSuministros,
            'catVehiculos'   => $catVehiculos,
        ]);
    }

    public function save(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('configuracion');
        }

        $tab = $_POST['tab'] ?? 'general';

        // Map of allowed settings keys per tab
        $allowedKeys = [
            'general'   => ['app_nombre', 'app_telefono', 'app_horario', 'app_direccion'],
            'email'     => ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'],
            'apariencia'=> ['color_primario', 'color_secundario', 'logo_url'],
            'qr'        => ['qr_api_key', 'qr_api_url'],
            'gps'       => ['gps_api_key', 'gps_api_url', 'gps_intervalo', 'traccar_url', 'traccar_usuario', 'traccar_password'],
            'chatbot'   => ['chatbot_token', 'chatbot_numero', 'chatbot_activo'],
            'paypal'    => ['paypal_client_id', 'paypal_secret', 'paypal_modo'],
            'pyspark'   => ['pyspark_url', 'pyspark_token'],
        ];

        $keys = $allowedKeys[$tab] ?? [];
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $this->settingModel->set($key, htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8'));
            }
        }

        $this->setFlash('success', 'Configuración guardada correctamente.');
        $this->redirect('configuracion?tab=' . urlencode($tab));
    }

    public function iot(): void
    {
        $this->requireRole(['superadmin', 'admin']);
        $this->redirect('configuracion?tab=iot');
    }

    public function saveIot(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('configuracion?tab=iot');
        }

        $id = (int) ($_POST['id'] ?? 0);

        $data = [
            'nombre'      => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'tipo'        => $_POST['tipo'] ?? 'hikvision',
            'ip'          => htmlspecialchars(trim($_POST['ip'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'puerto'      => (int) ($_POST['puerto'] ?? 80),
            'usuario'     => htmlspecialchars(trim($_POST['usuario'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'api_key'     => htmlspecialchars(trim($_POST['api_key'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'token'       => htmlspecialchars(trim($_POST['token'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'activo'      => (int) ($_POST['activo'] ?? 1),
            'descripcion' => htmlspecialchars(trim($_POST['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        if ($id > 0) $data['id'] = $id;

        // Store password hash only if provided
        if (!empty($_POST['password_device'])) {
            $data['password_hash'] = password_hash($_POST['password_device'], PASSWORD_BCRYPT);
        }

        $this->settingModel->saveIotDevice($data);
        $this->setFlash('success', $id ? 'Dispositivo actualizado.' : 'Dispositivo agregado.');
        $this->redirect('configuracion?tab=iot');
    }

    public function deleteIot(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->settingModel->deleteIotDevice($id);
            $this->setFlash('success', 'Dispositivo eliminado.');
        }
        $this->redirect('configuracion?tab=iot');
    }

    public function saveCatalog(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->setFlash('error', 'Petición inválida.');
            $this->redirect('configuracion?tab=catalogos');
        }

        $tipo     = $_POST['catalog_tipo'] ?? '';
        $etiqueta = htmlspecialchars(trim($_POST['etiqueta'] ?? ''), ENT_QUOTES, 'UTF-8');
        $clave    = htmlspecialchars(trim($_POST['clave']    ?? ''), ENT_QUOTES, 'UTF-8');
        $orden    = (int) ($_POST['orden'] ?? 0);
        $id       = (int) ($_POST['id']    ?? 0);

        $allowedTipos = ['activos_categoria', 'suministros_categoria', 'vehiculos_tipo'];
        if (!in_array($tipo, $allowedTipos) || empty($etiqueta) || empty($clave)) {
            $this->setFlash('error', 'Datos inválidos para el catálogo.');
            $this->redirect('configuracion?tab=catalogos');
        }

        $data = compact('tipo', 'etiqueta', 'clave', 'orden');
        if ($id > 0) $data['id'] = $id;

        $this->settingModel->saveCatalogItem($data);
        $this->setFlash('success', 'Entrada del catálogo guardada.');
        $this->redirect('configuracion?tab=catalogos');
    }

    public function deleteCatalog(): void
    {
        $this->requireRole(['superadmin', 'admin']);

        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->settingModel->deleteCatalogItem($id);
            $this->setFlash('success', 'Entrada eliminada del catálogo.');
        }
        $this->redirect('configuracion?tab=catalogos');
    }
}
