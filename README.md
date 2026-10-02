# 🥋 Palillo Fight Club | Fichaje PFC

Sistema de control de asistencia, pagos de cuotas y clases para el **Palillo Fight Club**, con un **lector RFID basado en Arduino** en la entrada.

- Alta y gestión de clientes (alumnos) y profesores.
- Clases con profesores y alumnos matriculados.
- Pagos de cuotas con monto, de varios meses o con promociones (ej: 3 + 1 gratis), adelantos sin pérdida de días y control de deuda.
- Fichadas automáticas con llavero RFID o manuales desde el panel, con la clase deducida por horario.
- Control de deuda y liquidación mensual de profesores.
- Stock de productos con ventas, entradas y ajustes de inventario.
- Emails automáticos (vencimiento, deuda, cumpleaños, inactividad, bienvenida, resumen semanal) y comprobante de pago en PDF.
- Aviso de cumpleaños, llaveros desconocidos y cuotas por vencer.
- Auditoría de acciones y logs técnicos.
- Backups automáticos diarios, control del lector con alertas, reporte de caja y exportación a Excel.
- Gestión de administradores, bloqueo por intentos fallidos y cierre de sesión por inactividad.
- Respaldo de la base de datos y reinicio remoto del lector.

---

## 📁 Estructura del proyecto

```
fichaje_pfc/
├── public/              Única carpeta expuesta por el servidor web
│   ├── index.php        Front controller: todas las peticiones entran por acá
│   ├── css/  js/  img/
├── src/                 Código PHP (namespace App\, autoload PSR-4)
│   ├── Core/            Router, Request, View, Session, Csrf, Auth, Container, Migrador, App
│   ├── Controllers/     Reciben la petición, llaman a un servicio y responden
│   │   └── Api/         Endpoints JSON (panel y lector Arduino)
│   ├── Services/        Reglas de negocio (pagos, deuda, liquidaciones, stock, auditoría…)
│   ├── Repositories/    Acceso a datos: todo el SQL vive acá
│   ├── Domain/          Enums y valores del dominio (TipoUsuario, EstadoLectura, Llavero)
│   ├── Exceptions/
│   └── Support/         Helpers de vistas y log
├── views/               Plantillas: solo presentación
│   ├── layouts/  partials/
│   ├── emails/          Plantillas de los emails (layout + una por tipo) y panel
│   ├── comprobantes/    PDF del comprobante de pago
│   └── auth/ dashboard/ usuarios/ clases/ fichajes/ deudas/ liquidaciones/ promociones/
│       stock/ reportes/ administradores/ sistema/ publico/ errors/
├── config/              app.php (configuración) y routes.php (mapa de URLs)
├── database/            schema.sql, seed.sql (datos ficticios) y migrations/
├── storage/             logs/, backups/ y emails/ (generados, no se versionan)
├── bin/                 Scripts de consola (tareas.php, migrar.php, crear-admin.php;
│                        procesar-emails.php queda por compatibilidad)
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
- Mailpit (buzón de prueba para ver los emails sin enviarlos): http://localhost:8025

El servicio `tareas` corre `bin/tareas.php` cada 15 minutos (emails, backup diario y control del lector).

La primera vez se crea la base con `database/schema.sql` y los **datos ficticios** de `database/seed.sql`. Para empezar con la base vacía, hay que quitar la línea del seed en `docker-compose.yml` antes del primer `up`.

Para depurar con Xdebug: `XDEBUG=1 XDEBUG_MODE=debug docker compose up -d --build`, y en VS Code usar la extensión *PHP Debug* con el mapeo `/var/www/html` → `${workspaceFolder}`.

---

## 📝 Instalación en XAMPP

Requisitos: XAMPP 8.2 o superior (PHP ≥ 8.1, MariaDB ≥ 10.4 / MySQL ≥ 8) y [Composer](https://getcomposer.org/).

1. Clonar el repositorio dentro de `htdocs` (por ejemplo, `C:\xampp\htdocs\fichaje_pfc`).
2. Instalar dependencias: `composer install --no-dev`.
3. Crear la base de datos y un usuario en phpMyAdmin, e importar `database/schema.sql` (y opcionalmente `database/seed.sql`). Después aplicar las migraciones con `php bin/migrar.php`.
4. Copiar `.env.example` como `.env` y completar los datos. `MYSQL_DB_HOST` debe ser `127.0.0.1` y `APP_URL`, la dirección con la que se entra al panel **desde otros equipos** (por ejemplo, `http://192.168.0.245/fichaje_pfc`). Se usa en los links de los emails, así que si queda `localhost` los links no van a funcionar para los clientes.
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
- **Plan del pago**: 1, 2, 3, 6 o 12 meses (cuota × meses) o una **promoción** activa. El monto se completa solo según el plan y se puede modificar (pago parcial).
- **La cuota vale hasta el día del vencimiento inclusive** (es lo que dice el comprobante).
- **Cobertura de cada pago:** si el alumno ya tenía un vencimiento, el pago se suma desde ahí. Un **adelanto** no pierde días, y un **pago tardío cubre la cuota más vieja adeudada**, así que si debía 3 meses y paga 1, sigue debiendo 2. Si nunca pagó, cubre desde la fecha de pago. Al **reactivar** un alumno se le cobra desde la reactivación, no el tiempo que estuvo inactivo.
- El vencimiento conserva siempre el **día ancla**: un alumno que paga el 31 vence el 28/02 y vuelve a vencer el 31/03.
- Solo se puede eliminar el **último pago** de un alumno, porque los vencimientos se encadenan. Un segundo pago del mismo alumno dentro de los 20 segundos se rechaza (doble clic).
- **Promociones** (panel → *Promociones*): "N meses pagos + M bonificados", con descuento opcional. Ejemplos: "3 + 1 gratis" (3 pagos, 1 bonificado) o "Semestral 10 % off" (6 pagos, 10 %). No se borran: se desactivan, para conservar el historial de los pagos que las usaron.
- **Deuda** = cuotas vencidas × cuota actual + saldo de pagos parciales, neteado: lo que se debió cobrar menos lo cobrado, así que un pago de más cancela un saldo anterior. Un alumno con clases que nunca pagó adeuda una cuota. Los pagos anteriores a la v3.1 (sin monto) se consideran completos.
- **Deudores** (panel → *Deudores*): alumnos activos con deuda, de mayor a menor, con la deuda total.
- No se puede desactivar a un alumno con deuda.
- En el alta, el cliente se puede matricular directamente en sus clases, y al crear una clase se pueden elegir sus profesores y alumnos. Para el alta *con pago*, elegir al menos una.

---

## 👨‍🏫 Liquidación de profesores

Panel → *Liquidaciones* (o el botón *Liquidar* en la ficha del profesor).

Cada profesor se liquida en uno de dos modos, que se elige en la misma pantalla:

| Modo | Base | Monto |
|---|---|---|
| **% de lo cobrado** (por defecto) | Lo cobrado en el mes en sus clases, según el reparto por clase de cada pago. Un pago de varios meses se reparte en partes iguales entre los meses que cubre. | Base × porcentaje del profesor (si no tiene uno propio, `LIQUIDACION_PORCENTAJE`, por defecto 50 %) |
| **$ por asistencia** | Ingresos de alumnos a sus clases en el mes, según la clase de cada fichada (ver *Horarios*) | Base × monto por asistencia |

Si una clase tiene varios profesores, la base de esa clase se divide en partes iguales.
- Flujo: el cálculo se ve en vivo → **Registrar** (solo para **meses cerrados**; guarda el monto y el detalle) → **Pagar**. Una liquidación registrada y no pagada se puede **Anular** para recalcularla.
- En el modo por asistencia se cuenta una asistencia por alumno, clase y día, aunque haya fichado con el llavero y además a mano.
- Se usan los profesores asignados actualmente a cada clase. Los pagos anteriores a la v3.1 no tienen reparto por clase y no cuentan.

---

## 📦 Stock

Panel → *Stock*. El botón muestra un contador rojo cuando hay productos en su stock mínimo o debajo.

- **Productos**: nombre, descripción, precio de venta, stock y stock mínimo. Se pueden desactivar (no se borran, para conservar el historial).
- **Movimientos** (desde la pantalla de cada producto):
  - *Venta*: cantidad y precio (por defecto el del producto). Se puede asociar al DNI de un cliente.
  - *Entrada*: compra o reposición, con costo unitario opcional.
  - *Ajuste de inventario*: se carga el stock real contado y un motivo obligatorio.
- El stock nunca queda negativo. Cada movimiento bloquea el producto en la base, así dos ventas simultáneas no pueden pasar el límite.
- El listado muestra las ventas del mes, y cada movimiento queda en el historial del producto y en la auditoría.

---

## 🕒 Horarios y clase de cada fichada

Cada clase puede tener horarios semanales (ficha de la clase → *Horarios*: día, desde, hasta). Al fichar, con el lector o a mano, se deduce a qué clase vino el alumno:

1. Entre las clases en las que está inscripto, se busca la que tiene horario **hoy**, desde `MINUTOS_ANTES_DE_CLASE` (por defecto 30) antes del inicio hasta la hora de fin.
2. Si coinciden varias (por ejemplo, llegó entre el fin de una clase y el comienzo de otra), gana la que empieza más cerca.
3. Si el alumno tiene **una sola clase** y esa clase todavía no tiene horarios, se asigna esa.
4. Si no coincide ninguna, la fichada **se registra igual** como "Sin clase" (marcada en naranja para revisar). El lector no bloquea a nadie por horario.

La clase aparece en *Últimos ingresos*, en las búsquedas y en la ficha del alumno, y el lector la muestra en la pantalla (firmware 3.2). El listado de clases muestra los horarios y la ficha de cada clase cuenta los ingresos de los últimos 30 días.

---

## ✉️ Emails

Los emails se **encolan** (tabla `emails_cola`) y se envían en segundo plano con `bin/tareas.php`. Así una operación nunca espera ni falla por el correo, y si el envío falla se reintenta (hasta `MAIL_MAX_INTENTOS`, con espera creciente).

| Email | Cuándo | Tipo |
|---|---|---|
| Vencimiento próximo | N días antes de que venza la cuota (por defecto 3) | Aviso |
| Cuota vencida / deuda | Mientras tenga deuda, como máximo uno cada N días (por defecto 7) | Aviso |
| Te extrañamos | Alumno al día que no viene hace N días (por defecto 14), uno por ausencia | Aviso |
| Cumpleaños | El día del cumpleaños | Aviso |
| Bienvenida | Al dar de alta un cliente | Aviso |
| Comprobante de pago | Al registrar un pago, con el PDF adjunto | Transaccional |
| Resumen semanal | El día elegido, al email del administrador: ingresos, pagos, altas, ventas, deudores, stock bajo y liquidaciones pendientes | Transaccional |

- Cada email se activa o desactiva y se configura en **panel → Emails**. Ahí también se ve la cola (enviados, pendientes y con error, con el detalle del error), se puede previsualizar cada mail, reintentar o cancelar, mandar uno de prueba o procesar en el momento.
- **Baja:** los avisos incluyen un link (y el encabezado `List-Unsubscribe`, que Gmail muestra como "Anular suscripción") para dejar de recibirlos. Los comprobantes se envían igual. La preferencia también se cambia desde la ficha del cliente.
- Nunca se manda dos veces el mismo aviso: cada uno tiene una clave única (por ejemplo, un aviso por vencimiento o un saludo por año), así que el proceso se puede ejecutar todas las veces que se quiera.
- Solo reciben emails los clientes con email cargado. Conviene pedirlo en el alta.

**Comprobante de pago:** PDF (A5) con los datos del gimnasio, el cliente, el período cubierto y el detalle por clase. Se descarga desde el historial de pagos de la ficha. **No es una factura**: la factura electrónica de ARCA es un desarrollo aparte.

### Configurar Gmail

1. En la cuenta de Gmail del gimnasio, activar la **verificación en 2 pasos**.
2. Crear una **contraseña de aplicación** en https://myaccount.google.com/apppasswords.
3. Completar en el `.env`: `MAIL_USUARIO` y `MAIL_REMITENTE` con la cuenta, y `MAIL_PASSWORD` con la contraseña de aplicación (16 letras, sin espacios). Gmail admite unos 500 envíos por día.
4. En el panel → Emails, cargar el email del administrador para el resumen y la dirección y el teléfono del gimnasio, y probar con *Enviar prueba*.

### Programar las tareas

- **Docker:** ya lo hace el servicio `tareas`.
- **Linux:** con cron, `0,15,30,45 * * * * php /ruta/al/proyecto/bin/tareas.php`.
- **Windows / XAMPP:** en el Programador de tareas, ejecutar `C:\xampp\php\php.exe C:\xampp\htdocs\fichaje_pfc\bin\tareas.php` cada 15 minutos.

---

## 💾 Backups y tareas programadas

`bin/tareas.php` (cada 15 minutos) hace todo lo programado:

- **Emails:** genera los avisos del día y envía la cola.
- **Backup diario:** a partir de `BACKUP_HORA` (por defecto, las 3), una vez por día. Borra los de más de `BACKUP_DIAS_RETENCION` días (por defecto 30), pero conserva siempre los 3 últimos. Si falla, avisa por email al administrador.
- **Control del lector:** si el Arduino no responde, avisa por email (como máximo una vez cada 6 horas).

Panel → *Backups*: lista de respaldos con descarga, último backup y botón *Respaldar ahora*. **Importante:** guardá periódicamente una copia fuera del equipo (pendrive o Drive), porque si se rompe el disco se pierden también los backups locales.

Las alertas llegan al *Email del administrador* que se configura en Emails.

## 📡 Estado del lector

El dashboard muestra si el lector responde (🟢/🔴), la latencia y cuándo fue la última lectura de un llavero. Se actualiza cada minuto. Si deja de responder, `bin/tareas.php` avisa por email.

## 📊 Reporte de caja y exportaciones

- Panel → *Caja*: ingresos por mes del año (cuotas + ventas de productos) en un gráfico, con la variación respecto del año anterior y una vista en tabla. Al hacer clic en un mes se ve el detalle por clase y por producto. El criterio es de caja: cada cobro suma en el mes en que se cobró.
- **Exportar a Excel (CSV):** deudores, clientes, profesores, pagos de un período, liquidaciones y la caja del año, desde el botón de cada pantalla. Los archivos usan `;` como separador y coma decimal, se abren directo con Excel en español y quedan auditados porque contienen datos personales.

## 🔐 Acceso y administradores

- Panel → *Administradores*: alta de administradores, cambio de contraseña y activar/desactivar. No se puede desactivar el propio usuario ni el último administrador activo.
- Panel → *Mi cuenta*: cambiar la propia contraseña (pide la actual).
- **Contraseñas:** mínimo 8 caracteres, distintas del DNI.
- **Intentos fallidos:** después de `LOGIN_MAX_INTENTOS` (por defecto 5) con el mismo DNI en 15 minutos, el acceso se bloquea 15 minutos. Por IP, el límite es 20. Queda auditado.
- **Inactividad:** la sesión se cierra después de `SESION_INACTIVIDAD_MINUTOS` (por defecto 120) sin uso. Las pantallas que se actualizan solas no cuentan como uso.
- El primer administrador se crea con `php bin/crear-admin.php`.

---

## 🗃️ Migraciones

Los cambios de estructura de la base viven en `database/migrations/NNN_descripcion.sql` y se aplican con:

```bash
php bin/migrar.php            # aplica las pendientes
php bin/migrar.php --estado   # lista las pendientes sin aplicar nada
```

Cada migración aplicada se registra en la tabla `migraciones`, así que es seguro ejecutarlo varias veces. Si una migración falla a mitad de camino (por ejemplo, por un DNI duplicado), el mensaje indica la sentencia y el motivo: se corrige el dato y se vuelve a ejecutar, y retoma desde esa sentencia (tabla `migraciones_progreso`). **Después de cada actualización del sistema hay que correr `bin/migrar.php`.** Antes de migrar producción, conviene hacer un respaldo desde el panel.

| Migración | Qué hace |
|---|---|
| 001 | Teléfono como texto (antes INT) |
| 002 | Limpia matriculaciones huérfanas o repetidas y hace único el DNI |
| 003 | Claves foráneas entre usuarios, clases, pagos y fichadas. Los pagos y fichadas de usuarios que ya no existen se mueven a `huerfanos_payments` / `huerfanos_incomes` para revisarlos |
| 004 | Tabla `auditoria` |
| 005 | Monto y cuota en `payments`; reparto por clase en `payment_classes` |
| 006 | Porcentaje de liquidación en `users` y tabla `liquidaciones` |
| 007 | Tablas `productos` y `movimientos_stock` |
| 008 | Emails: `emails_cola`, `configuracion` y preferencia de avisos en `users` |
| 009 | Horarios de clases (`clase_horarios`) y clase de cada fichada (`incomes.id_class`) |
| 010 | Promociones, meses cubiertos por pago y liquidación por asistencia |
| 011 | Control de intentos fallidos de inicio de sesión (`intentos_login`) |

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
| `promociones` | Promociones de pago (meses pagos, bonificados, descuento) |
| `liquidaciones` | Liquidaciones mensuales de profesores (una por profesor y período) |
| `productos` / `movimientos_stock` | Productos y su historial de entradas, ventas y ajustes |
| `emails_cola` | Emails encolados, enviados o con error |
| `configuracion` | Opciones editables desde el panel (avisos y datos del gimnasio) |
| `incomes` | Fichadas (ingresos), con la clase deducida por horario |
| `clase_horarios` | Horarios semanales de cada clase |
| `uid_incomes` | Llaveros desconocidos pendientes de mostrar en el panel |
| `types_users` | Catálogo de tipos de usuario |
| `auditoria` | Historial de acciones de los administradores |
| `intentos_login` | Intentos fallidos de inicio de sesión (bloqueo temporal) |
| `migraciones` / `migraciones_progreso` | Control de migraciones aplicadas y de una migración a medias |
| `huerfanos_payments` / `huerfanos_incomes` | Respaldo de pagos y fichadas de usuarios que ya no existían (migración 003) |

La estructura base está en `database/schema.sql`; los cambios posteriores, en `database/migrations/` (ver *Migraciones*).

---

Ver [CHANGELOG.md](CHANGELOG.md) para el historial de versiones y [docs/NOTAS.md](docs/NOTAS.md) para pendientes e ideas.
