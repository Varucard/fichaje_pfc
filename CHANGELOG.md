# Changelog

## [3.4.1] - 2026-10-02

Correcciones de la revisión exhaustiva del código (seguridad, lógica, interfaz y firmware).

### Seguridad
- **XSS almacenado sin autenticación** en la Auditoría (DNI de un login fallido). El DNI se valida antes de guardarse y el link se escapa.
- El bloqueo de login se evadía con variantes del mismo DNI. El contador de intentos es atómico y el intento se reserva antes de comparar la contraseña.
- Redirección abierta con `/\`. Content-Security-Policy y otros encabezados de seguridad.
- La sesión se revalida contra la base: se cierra si el admin fue desactivado o cambió su contraseña.

### Pagos y deuda (reglas definidas)
- La cuota vale hasta el día del vencimiento inclusive.
- Un pago tardío cubre la cuota más vieja adeudada. Al reactivar, se cobra desde la reactivación.
- Saldo de pagos parciales neteado (un pago de más lo cancela).
- Día ancla: el fin de mes no arrastra días perdidos.
- Pagos duplicados rechazados. Solo se elimina el último pago.
- Fechas imposibles rechazadas y montos con separador de miles.

### Liquidación
- Solo meses cerrados.
- Una asistencia por alumno, clase y día.
- Prorrateo de pagos de varios meses desde el mes que cubren.

### Integración
- La ruta del firmware anterior funciona en XAMPP.
- El reinicio del Arduino informa el resultado real y el estado del lector exige una respuesta HTTP.
- Zona horaria de MySQL igual a la de PHP.
- Centavos visibles.
- Clave duplicada → mensaje claro.

### Interfaz
- Sonido y copiar más robustos.
- Polling que se pausa con la pestaña oculta.
- Ventanas que no se cierran por error.
- Formularios que conservan lo escrito.
- Accesibilidad y tablas sin desborde.

### Firmware
- Lecturas con plazo absoluto (el watchdog ya no se dispara con clientes lentos).
- "Servidor lento" distinto de "sin conexión".
- LCD sin parpadeo y LED verde solo para acceso permitido.
- `Host` con puerto y puerto de reinicio configurable.

## [3.4.0] - 2026-10-02

### Operación
- **Backups automáticos** diarios con retención configurable, alerta por email si fallan y pantalla de backups con descarga.
- `bin/tareas.php` reúne las tareas programadas: emails, backup diario y control del lector. `procesar-emails.php` se mantiene por compatibilidad.
- **Estado del lector** en el dashboard (en línea o sin respuesta, latencia y última lectura) y alerta por email si deja de responder.

### Reportes
- **Reporte de caja** mensual (cuotas + ventas) con gráfico, variación interanual y detalle por clase y por producto.
- **Exportación a CSV para Excel**: deudores, clientes, profesores, pagos, liquidaciones y caja. Protegida contra inyección de fórmulas y auditada.

### Seguridad
- Bloqueo temporal por intentos fallidos de inicio de sesión (por DNI y por IP).
- Cierre de sesión por inactividad. El polling de las pantallas no mantiene viva la sesión, y si vence vuelve al login.
- Gestión de administradores desde el panel y "Mi cuenta" para cambiar la contraseña. Desde la búsqueda de clientes ya no se puede desactivar un administrador.

### Interfaz
- "Registrar fichada" y "Abonar clase" usan ventanas propias en lugar de `prompt()`. Abonar permite elegir el plan o la promoción.

## [3.3.0] - 2026-10-02

### Pagos
- Pagos de varios meses (1 a 12) y promociones configurables: meses pagos, meses bonificados y descuento.
- Adelanto de pago: si se paga antes del vencimiento, los meses se suman desde el vencimiento actual y no se pierden días.
- El comprobante muestra el plan y la fecha hasta la que vale la cuota.

### Liquidación de profesores
- Nuevo modo **por asistencia** ($ por cada ingreso de un alumno a sus clases), además del % de lo cobrado.
- En el modo %, un pago de varios meses se reparte entre los meses que cubre en lugar de contarse todo en el mes del pago.

### Clases
- Al crear una clase se pueden elegir sus alumnos, con un buscador para filtrarlos.

## [3.2.0] - 2026-10-02

### Horarios y clase en la fichada
- Horarios semanales por clase, con validación de superposiciones.
- Cada fichada (lector o manual) se asigna a la clase en horario. Si no coincide ninguna, se registra igual como "Sin clase".
- La clase se ve en los ingresos, las búsquedas y la ficha del alumno. El lector la muestra en el LCD.
- Asistencias de los últimos 30 días en la ficha de cada clase.

### Emails
- Avisos automáticos: vencimiento próximo, cuota vencida/deuda, inasistencia ("te extrañamos"), cumpleaños y bienvenida.
- Comprobante de pago en PDF (sin validez fiscal), adjunto por email y descargable desde la ficha.
- Resumen semanal para el administrador.
- Cola de envío con reintentos, panel con configuración, vista previa, reintento y email de prueba.
- Baja de avisos por link (con `List-Unsubscribe`) o desde la ficha; todo queda auditado.
- Envío por SMTP (Gmail). En desarrollo, Mailpit en Docker; además hay un modo archivo.

### Correcciones
- El router no aceptaba cuantificadores con llaves en los parámetros (ej: `{token:[0-9a-f]{32}}`).

## [3.1.1] - 2026-10-02

### Correcciones
- La migración 003 fallaba en bases con pagos o fichadas de usuarios borrados a mano. Ahora esos registros se mueven a tablas de respaldo antes de crear las claves foráneas.
- El migrador retoma una migración que falló a mitad de camino, en lugar de quedar bloqueado por los cambios que ya se habían aplicado.

## [3.1.0] - 2026-10-02

### Logs y auditoría
- Auditoría de acciones (quién hizo qué y cuándo, con los cambios realizados): pantalla *Auditoría* con filtros, e historial en la ficha de cada usuario.
- Logs técnicos con niveles, contexto de la petición, registro de accesos rechazados y 404, limpieza automática y visor en el panel.

### Base de datos
- Migrador (`bin/migrar.php`) con control de migraciones aplicadas.
- DNI único, claves foráneas y limpieza de matriculaciones huérfanas o repetidas.

### Pagos y deuda
- Los pagos guardan el monto cobrado y la cuota (suma de las clases del alumno), y se reparten por clase.
- Pagos parciales: la diferencia queda como saldo adeudado.
- Cálculo de deuda, listado de deudores y bloqueo de la desactivación de alumnos con deuda.
- Alta de clientes con matriculación en sus clases.

### Liquidación de profesores
- Cálculo mensual: porcentaje (por profesor o por defecto) de lo cobrado en sus clases.
- Registro, pago y anulación de liquidaciones, con historial en la ficha del profesor.

### Stock
- Productos con precio, stock y stock mínimo, y aviso en el panel cuando hay que reponer.
- Ventas (opcionalmente a un cliente), entradas y ajustes de inventario, con historial por producto y ventas del mes.

### Mejoras
- Corregida la grilla de las tablas con botones de acción.
- Últimos ingresos en la ficha del alumno.
- Sonido en los avisos de llavero desconocido y en los ingresos nuevos.

## [3.0.0] - 2026-10-02

Reestructuración completa del proyecto: separación en capas, seguridad y firmware.

### Arquitectura
- Front controller (`public/index.php`) y router con URLs limpias. Solo `public/` queda expuesta.
- Capas separadas: controladores finos, servicios con las reglas de negocio, repositorios con el SQL y vistas solo de presentación (layouts y partials, sin estilos inline).
- Autoload PSR-4 con Composer, contenedor de dependencias y una única conexión PDO.
- Consultas N+1 reemplazadas por JOIN (listado de clases, ficha de usuario, últimos ingresos).
- Mensajes flash en lugar de `alert()` impresos desde PHP.
- Registro de errores en `storage/logs` (antes los errores se descartaban en silencio).
- Tests con PHPUnit, CI en GitHub Actions y Docker con `public/` como DocumentRoot.

### Seguridad
- **Login:** se podía ingresar con cualquier contraseña conociendo un DNI registrado.
- Protección CSRF en todos los formularios; las bajas y eliminaciones pasan de GET a POST.
- XSS en la tabla de últimos ingresos (`innerHTML`) y escapado consistente en las vistas.
- El token del lector sale del código (`ARDUINO_TOKEN`). El endpoint de llaveros pendientes exige sesión.
- Respaldo: la contraseña de la BD ya no se pasa sin escapar a la línea de comandos.
- Se dejan de versionar el archivo `env` y el dump con datos personales. Se agregan `schema.sql` y un seed ficticio.

### Correcciones
- La búsqueda de usuarios por nombre nunca devolvía resultados.
- No se detectaba el llavero duplicado al dar de alta un usuario.
- El cálculo del vencimiento estaba en tres versiones distintas; ahora es un mes después, ajustado a fin de mes.
- El historial de pagos mostraba los 5 más viejos en lugar de los 5 más recientes.
- Profesores y administradores recibían "sin clase" en el lector en lugar de "admin".
- Los nombres con tildes se normalizaban mal ("PÉREZ" → "PÉrez").
- El teléfono como `INT` rompía con números largos (migración `001`).
- Los respaldos hechos con mysqldump de MariaDB no se podían restaurar en MySQL.

### Nuevo
- Matricular en una clase desde la ficha del usuario (el botón estaba sin terminar).
- Pago manual con selector de fecha en la ficha del cliente.
- Aviso de llavero desconocido sin bloquear la pantalla, con botón para copiar el código.
- Antirrebote: no se registran fichadas duplicadas del mismo alumno dentro de 5 minutos.
- `bin/crear-admin.php` para crear el administrador o cambiar su contraseña.

### Firmware
- Timeouts en la consulta al servidor (antes el lector podía quedar colgado) y lectura de la respuesta con tamaño acotado.
- Watchdog de 8 segundos, detección de shield o cable desconectado y mensaje ante errores HTTP.
- Antirrebote del llavero apoyado. El reinicio remoto ahora exige token.
- Configuración en `config.h` (no versionado). Migración a ArduinoJson 7.

## [2.1.0] - 2025-05-25

- Corregida la vista de crear clases cuando no hay profesores cargados; ahora solo muestra profesores activos.
- Respaldo de la base desde el panel, con ruta configurable.
- Botón en la vista de usuario hacia el listado de clases.
- Corregidas Lista de Clases y Lista de Ingresos cuando no hay datos.
- Footer.
- Corrección de excepciones por valores NULL en PHP 8.
- Finalizada la vista de búsqueda de clases.
- Corregido el error al crear clases sin profesores y al generar un pago automático.
- Mejoras visuales en el modal de cumpleaños.
