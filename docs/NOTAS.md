# Notas, pendientes e ideas

## Resueltos en 3.0.0
- [x] Terminar el botón "Matricular alumno en clase" en la vista de usuario.
- [x] Terminar la vista de búsqueda de clases.
- [x] Que el botón "Volver" de las búsquedas conserve la búsqueda (ahora las búsquedas son URLs con `?q=`).
- [x] Los buscadores a veces se tildaban (se eliminaron las consultas N+1 y la búsqueda por nombre rota).
- [x] Evitar basura en el RFID de los usuarios: si se deja vacío queda "SIN LLAVERO" y se normaliza en mayúsculas.
- [x] Arduino: corregir la respuesta para administradores sin clases.
- [x] Botón de copiar en el aviso de llavero desconocido.
- [x] Íconos en los mensajes de aviso.
- [x] Buscar administradores (la búsqueda incluye todos los tipos y muestra la columna Tipo).

## Resueltos en 3.1.0
- [x] Que las notificaciones suenen en el equipo.
- [x] Claves foráneas e índice único en `users.dni`.
- [x] Deuda de alumnos.
- [x] No permitir eliminar (desactivar) un alumno con deuda.
- [x] Mostrar los últimos fichajes en la ficha del usuario.
- [x] Poder matricular al alumno en sus clases desde el alta.
- [x] Sistema de logs y auditoría.
- [x] Liquidación de profesores.
- [x] Stock.

## Pendientes / errores conocidos

## Mejoras a futuro (consultar)
- [ ] Enviar información de las clases por email a los alumnos.
- [ ] Si el alumno está en más de una clase, mostrar en sus fichadas a qué clase corresponden, con pagos por clase.
