-- Migración 001 (aplicar sobre bases existentes creadas con el dump original).
-- El teléfono era INT(13): los números de más de 10 dígitos (ej: 5491100000000)
-- no entran en un INT y los ceros a la izquierda se perdían.
ALTER TABLE `users` MODIFY `phone_number` varchar(20) DEFAULT NULL;
