# IDGuns — Sistema de Control de Armas y Activos

Sistema de control de inventario, armas, vehículos y bitácora para la **Secretaría de Seguridad Ciudadana de Querétaro**.

---

## Requisitos del Sistema

| Componente   | Versión mínima |
|--------------|---------------|
| PHP          | 7.4+          |
| MySQL        | 5.7+          |
| Apache       | 2.4+ con `mod_rewrite` |
| Extensiones  | `pdo`, `pdo_mysql`, `mbstring`, `session` |

---

## Instalación

### 1. Clonar / Copiar el proyecto

```bash
cp -r IDGuns/ /var/www/html/idguns/
```

### 2. Crear y poblar la base de datos

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS idguns CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p idguns < database/schema.sql
```

### 3. Configurar credenciales de la BD

Editar **`config/config.php`**:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'idguns');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Habilitar mod_rewrite en Apache

```bash
a2enmod rewrite
systemctl restart apache2
```

### 5. Configurar VirtualHost Apache

```apache
<VirtualHost *:80>
    ServerName idguns.local
    DocumentRoot /var/www/html/idguns

    <Directory /var/www/html/idguns>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/idguns_error.log
    CustomLog ${APACHE_LOG_DIR}/idguns_access.log combined
</VirtualHost>
```

### 6. Verificar instalación

Acceder a: `http://idguns.local/test_connection.php`

---

## Credenciales por Defecto

> ⚠️ **Cambiar en producción. La contraseña de todos los usuarios de ejemplo es: `password`**

| Usuario      | Rol           |
|-------------|---------------|
| `superadmin` | Super Admin   |
| `lvega`      | Admin         |
| `sherrera`   | Admin         |
| `dlopez`     | Almacén       |
| `esalinas`   | Bitácora      |

Para generar hashes seguros:
```bash
php -r "echo password_hash('TuNuevaContrasena', PASSWORD_BCRYPT) . PHP_EOL;"
```

---

## Módulos

| Módulo | URL | Descripción |
|--------|-----|-------------|
| Dashboard | `/dashboard` | Estadísticas y gráficas generales |
| Inventario | `/inventario` | CRUD de activos (armas, vehículos, equipos) |
| Almacén | `/almacen` | Suministros, stock y movimientos |
| Vehículos | `/vehiculos` | Flota vehicular, mantenimiento, combustible |
| Bitácora | `/bitacora` | Registro cronológico de movimientos |
| Administración | `/admin` | Usuarios, reportes, actividad |
| Configuración | `/configuracion` | Sistema, IoT, email, GPS, chatbot |

---

## Arquitectura MVC

```
index.php              → Front controller / router
config/config.php      → Configuración de la app + BASE_URL auto-detect
config/database.php    → Singleton PDO
app/controllers/       → Controladores (extienden BaseController)
app/models/            → Modelos (extienden BaseModel con PDO)
app/views/             → Vistas PHP + Tailwind CSS CDN
database/schema.sql    → Esquema completo + datos de ejemplo Querétaro
```

## Seguridad implementada

- Contraseñas con `password_hash()` bcrypt
- CSRF tokens en todos los formularios POST
- Sanitización con `htmlspecialchars()` en todas las salidas
- Consultas parametrizadas PDO (prevención SQL Injection)
- Control de roles por acción
- Cookies de sesión con `httponly`

---

*IDGuns v1.0 — © 2024 Secretaría de Seguridad Ciudadana de Querétaro*
