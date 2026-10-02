# Changelog

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
