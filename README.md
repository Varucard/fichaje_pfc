# 🥋 Palillo Fight Club | Fichaje PFC

Sistema de control de asistencia, pagos de cuotas y clases para el **Palillo Fight Club**, con un **lector RFID basado en Arduino** en la entrada.

- Alta y gestión de clientes (alumnos) y profesores.
- Clases con profesores y alumnos matriculados.
- Pagos de cuotas con cálculo automático de vencimiento.
- Fichadas automáticas con llavero RFID o manuales desde el panel.
- Aviso de cumpleaños, llaveros desconocidos y cuotas por vencer.
- Respaldo de la base de datos y reinicio remoto del lector.

---

## 📁 Estructura del proyecto

```
fichaje_pfc/
├── public/              Única carpeta expuesta por el servidor web
│   ├── index.php        Front controller: todas las peticiones entran por acá
│   ├── css/  js/  img/
├── src/                 Código PHP (namespace App\, autoload PSR-4)
│   ├── Core/            Router, Request, View, Session, Csrf, Auth, Container, App
│   ├── Controllers/     Reciben la petición, llaman a un servicio y responden
│   │   └── Api/         Endpoints JSON (panel y lector Arduino)
│   ├── Services/        Reglas de negocio (pagos, fichajes, usuarios, clases…)
│   ├── Repositories/    Acceso a datos: todo el SQL vive acá
│   ├── Domain/          Enums y valores del dominio (TipoUsuario, EstadoLectura, Llavero)
│   ├── Exceptions/
│   └── Support/         Helpers de vistas y log
├── views/               Plantillas: solo presentación
│   ├── layouts/  partials/
│   └── auth/  dashboard/  usuarios/  clases/  fichajes/  errors/
├── config/              app.php (configuración) y routes.php (mapa de URLs)
├── database/            schema.sql, seed.sql (datos ficticios) y migrations/
├── storage/             logs/ y backups/ (generados, no se versionan)
├── bin/                 Scripts de consola (crear-admin.php)
├── tests/               Tests de PHPUnit
├── firmware/            Código del Arduino, carcasa 3D e imágenes → ver firmware/README.md
└── docker/  Dockerfile  docker-compose.yml
```

### ¿Dónde va cada cosa?

| Si necesitás… | Va en… |
|---|---|
| Una URL nueva | `config/routes.php` + un método en `src/Controllers/` |
| Una regla de negocio ("no se puede X si Y") | `src/Services/` (lanzar `ValidacionException` con el mensaje para el usuario) |
| Una consulta SQL | `src/Repositories/` |
| HTML | `views/` (escapar siempre con `e()`) |
| JavaScript de una pantalla | `public/js/` e incluirlo desde la vista con `$this->script('nombre')` |

Flujo de una petición: `public/index.php` → `Router` → `Controller` → `Service` → `Repository` → vista o JSON.

---

## 🚢 Instalación con Docker (recomendada)

Requisitos: [Docker Desktop](https://www.docker.com/products/docker-desktop/).

```bash
git clone https://github.com/varucard/fichaje_pfc.git
cd fichaje_pfc
cp .env.example .env          # completar claves y ARDUINO_TOKEN
docker compose up -d --build
docker compose exec php php bin/crear-admin.php
```

- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081

La primera vez se crea la base con `database/schema.sql` y los **datos ficticios** de `database/seed.sql`. Para empezar con la base vacía, hay que quitar la línea del seed en `docker-compose.yml` antes del primer `up`.

Para depurar con Xdebug: `XDEBUG=1 XDEBUG_MODE=debug docker compose up -d --build`, y en VS Code usar la extensión *PHP Debug* con el mapeo `/var/www/html` → `${workspaceFolder}`.

---

## 📝 Instalación en XAMPP

Requisitos: XAMPP 8.2 o superior (PHP ≥ 8.1, MariaDB ≥ 10.4 / MySQL ≥ 8) y [Composer](https://getcomposer.org/).

1. Clonar el repositorio dentro de `htdocs` (por ejemplo, `C:\xampp\htdocs\fichaje_pfc`).
2. Instalar dependencias: `composer install --no-dev`.
3. Crear la base de datos y un usuario en phpMyAdmin, e importar `database/schema.sql` (y opcionalmente `database/seed.sql`).
4. Copiar `.env.example` como `.env` y completar los datos. `APP_URL` debe ser `http://localhost/fichaje_pfc` y `MYSQL_DB_HOST`, `127.0.0.1`.
5. Crear el administrador: `php bin/crear-admin.php`.
6. Entrar a http://localhost/fichaje_pfc.

El `.htaccess` de la raíz redirige todo a `public/` y bloquea el acceso a `.env`, `src/`, `config/` y demás carpetas internas. Requiere `mod_rewrite`, que viene activo en XAMPP. En producción conviene apuntar el *DocumentRoot* directamente a `public/`.

Para los respaldos, PHP usa `mysqldump` si está en el PATH. Si no lo encuentra, genera un volcado propio en `storage/backups/`.

---

## 🔌 Lector RFID (Arduino)

Toda la documentación del hardware, las librerías, la configuración y el protocolo está en **[firmware/README.md](firmware/README.md)**.

Resumen: el lector consulta `GET /api/arduino/lectura?uid=…&auth=<ARDUINO_TOKEN>` y el panel lo reinicia con `GET /reiniciar` en el puerto 8080 del Arduino.

> **Compatibilidad:** los lectores con el firmware anterior consultan `/config/get_uid.php?auth=ABC123`. Esa ruta se mantiene, así que siguen funcionando si se configura `ARDUINO_TOKEN=ABC123` en el `.env` hasta que se actualice el firmware. Después hay que cambiar el token por uno aleatorio en ambos lados.

---

## 🧪 Desarrollo

```bash
composer install
composer test          # PHPUnit
composer lint          # Verificación de sintaxis
composer serve         # Servidor embebido en http://localhost:8000 (requiere MySQL aparte)
```

Convenciones:

- PHP 8.1+, `declare(strict_types=1)`, PSR-4 y PSR-12 con indentación de 2 espacios (ver `.editorconfig`).
- Los formularios POST llevan `<?= csrf_field() ?>`. El router rechaza los POST sin token.
- Toda salida en vistas pasa por `e()`.
- Los cambios de estructura de la base van como un archivo nuevo en `database/migrations/` y además se reflejan en `schema.sql`.
- Ramas: `main` (estable) ← `dev` (integración) ← ramas de funcionalidad (`feat/…`, `fix/…`).

---

## 🗄️ Base de datos

| Tabla | Contenido |
|---|---|
| `users` | Clientes, profesores y administradores (`type_user`: 1 profesor, 2 alumno, 3 admin; `asset`: activo) |
| `classes` | Clases y precio |
| `user_class` / `teacher_class` | Matriculaciones de alumnos / profesores |
| `payments` | Pagos con fecha de pago y de vencimiento (`date_of_renovation`) |
| `incomes` | Fichadas (ingresos) |
| `uid_incomes` | Llaveros desconocidos pendientes de mostrar en el panel |
| `types_users` | Catálogo de tipos de usuario |

Migraciones pendientes para bases creadas con el dump anterior: `database/migrations/001_telefono_varchar.sql`.

---

Ver [CHANGELOG.md](CHANGELOG.md) para el historial de versiones y [docs/NOTAS.md](docs/NOTAS.md) para pendientes e ideas.
