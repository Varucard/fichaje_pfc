# 🥋 Palillo Fight Club | Fichaje PFC

Sistema de control de asistencia, pagos de cuotas y clases para el **Palillo Fight Club**, con un **lector RFID basado en Arduino** en la entrada.

- Alta y gestión de clientes (alumnos) y profesores.
- Clases con profesores y alumnos matriculados.
- Pagos de cuotas con monto, cálculo automático de vencimiento y control de deuda.
- Fichadas automáticas con llavero RFID o manuales desde el panel.
- Control de deuda y liquidación mensual de profesores.
- Aviso de cumpleaños, llaveros desconocidos y cuotas por vencer.
- Auditoría de acciones y logs técnicos.
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
docker compose exec php php bin/migrar.php
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
3. Crear la base de datos y un usuario en phpMyAdmin, e importar `database/schema.sql` (y opcionalmente `database/seed.sql`). Después aplicar las migraciones con `php bin/migrar.php`.
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

## 💰 Cuotas, pagos y deuda

- **Cuota mensual** de un alumno = suma de los precios de sus clases.
- Cada **pago** cubre un mes desde su fecha (si el día no existe en el mes siguiente, vence el último día del mes) y guarda:
  - el **monto cobrado** (por defecto la cuota completa; se puede cargar otro valor en *Pago manual*),
  - la **cuota** que correspondía en ese momento,
  - el **reparto por clase**, proporcional al precio (tabla `payment_classes`), que es la base de la liquidación de profesores.
- **Deuda** = cuotas vencidas × cuota actual + saldos de pagos parciales (cuota − monto cobrado). Un alumno con clases que nunca pagó adeuda una cuota. Los pagos anteriores a la v3.1 (sin monto) se consideran completos.
- **Deudores** (panel → *Deudores*): alumnos activos con deuda, de mayor a menor, con la deuda total.
- No se puede desactivar a un alumno con deuda.
- En el alta, el cliente se puede matricular directamente en sus clases. Para el alta *con pago*, elegir al menos una.

---

## 👨‍🏫 Liquidación de profesores

Panel → *Liquidaciones* (o el botón *Liquidar* en la ficha del profesor).

- **Base** = lo cobrado en el mes (según la fecha de pago) en las clases que dicta el profesor, tomando el reparto por clase de cada pago. Si una clase tiene varios profesores, lo cobrado se divide en partes iguales.
- **Monto** = base × porcentaje del profesor. Cada profesor puede tener su propio porcentaje (se edita en la misma pantalla); si no tiene uno, se usa `LIQUIDACION_PORCENTAJE` (por defecto 50 %).
- Flujo: el cálculo se ve en vivo → **Registrar** (guarda el monto y el detalle; no cambia aunque después entren más pagos) → **Pagar**. Una liquidación registrada y no pagada se puede **Anular** para recalcularla.
- Se usan los profesores asignados actualmente a cada clase. Los pagos anteriores a la v3.1 no tienen reparto por clase y no cuentan.

---

## 🗃️ Migraciones

Los cambios de estructura de la base viven en `database/migrations/NNN_descripcion.sql` y se aplican con:

```bash
php bin/migrar.php            # aplica las pendientes
php bin/migrar.php --estado   # lista las pendientes sin aplicar nada
```

Cada migración aplicada se registra en la tabla `migraciones`, así que es seguro ejecutarlo varias veces. **Después de cada actualización del sistema hay que correr `bin/migrar.php`.** Antes de migrar producción, conviene hacer un respaldo desde el panel.

| Migración | Qué hace |
|---|---|
| 001 | Teléfono como texto (antes INT) |
| 002 | Limpia matriculaciones huérfanas o repetidas y hace único el DNI |
| 003 | Claves foráneas entre usuarios, clases, pagos y fichadas |
| 004 | Tabla `auditoria` |
| 005 | Monto y cuota en `payments`; reparto por clase en `payment_classes` |
| 006 | Porcentaje de liquidación en `users` y tabla `liquidaciones` |

---

## 📜 Logs y auditoría

**Auditoría** (panel → *Auditoría*): historial de quién hizo qué y cuándo. Se guarda en la tabla `auditoria` e incluye inicios de sesión (también los fallidos), altas, cambios (con el valor anterior y el nuevo), bajas, pagos, fichadas manuales, matriculaciones, clases, liquidaciones, stock, respaldos y reinicios del lector. Se puede filtrar por fecha, tipo y texto o DNI, y la ficha de cada usuario muestra su propio historial.

Para auditar una acción nueva, desde un servicio:

```php
$this->auditoria->registrar('entidad.accion', 'Descripción legible', 'entidad', $id, ['datos' => 'extra']);
```

**Logs técnicos** (panel → *Logs*, o los archivos `storage/logs/app-AAAA-MM-DD.log`): una entrada JSON por línea, con fecha, nivel, mensaje, IP, petición y usuario. Registran errores con su traza, accesos rechazados, inicios de sesión fallidos, 404 y eventos del sistema.

```php
use App\Support\Log;
Log::info('Mensaje', ['dato' => 1]);   // también debug(), warning() y error($mensaje, $excepcion)
```

| Variable `.env` | Valor por defecto | Uso |
|---|---|---|
| `LOG_NIVEL` | `info` | Nivel mínimo: `debug`, `info`, `warning`, `error` |
| `LOG_DIAS_RETENCION` | `90` | Los archivos más viejos se borran solos (0 = nunca) |

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
- Los cambios de estructura de la base van como un archivo nuevo en `database/migrations/` (no se edita `schema.sql`).
- Toda acción que modifique datos se audita desde su servicio con `AuditoriaService`.
- Ramas: `main` (estable) ← `dev` (integración) ← ramas de funcionalidad (`feat/…`, `fix/…`).

---

## 🗄️ Base de datos

| Tabla | Contenido |
|---|---|
| `users` | Clientes, profesores y administradores (`type_user`: 1 profesor, 2 alumno, 3 admin; `asset`: activo) |
| `classes` | Clases y precio |
| `user_class` / `teacher_class` | Matriculaciones de alumnos / profesores |
| `payments` | Pagos: fecha, vencimiento (`date_of_renovation`), monto cobrado y cuota |
| `payment_classes` | Parte de cada pago asignada a cada clase |
| `liquidaciones` | Liquidaciones mensuales de profesores (una por profesor y período) |
| `incomes` | Fichadas (ingresos) |
| `uid_incomes` | Llaveros desconocidos pendientes de mostrar en el panel |
| `types_users` | Catálogo de tipos de usuario |

| `auditoria` | Historial de acciones de los administradores |
| `migraciones` | Control de migraciones aplicadas |

La estructura base está en `database/schema.sql`; los cambios posteriores, en `database/migrations/` (ver *Migraciones*).

---

Ver [CHANGELOG.md](CHANGELOG.md) para el historial de versiones y [docs/NOTAS.md](docs/NOTAS.md) para pendientes e ideas.
