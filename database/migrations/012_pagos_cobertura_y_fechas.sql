-- Migración 012: cobertura de cada pago, día ancla del vencimiento y fechas de alta.
--
-- - cubre_desde: desde qué fecha cubre el pago (el vencimiento anterior si venía al día o
--   adeudaba, o la fecha de pago si era el primero). La liquidación reparte desde ese mes.
-- - dia_ancla: día del mes en que vence la cuota del alumno (ej. 31). Evita que un
--   vencimiento recortado a fin de mes (28/02) arrastre ese día a los meses siguientes.
-- - creado_en: cuándo se registró (para detectar pagos duplicados por doble envío).
-- - users.cuota_desde: desde cuándo se le vuelve a cobrar al reactivarlo (no se cobra el
--   tiempo que estuvo inactivo).

ALTER TABLE payments
  ADD COLUMN cubre_desde DATE NULL,
  ADD COLUMN dia_ancla TINYINT NULL,
  ADD COLUMN creado_en DATETIME NULL;

UPDATE payments SET cubre_desde = discharge_date WHERE cubre_desde IS NULL;
UPDATE payments SET dia_ancla = DAY(date_of_renovation) WHERE dia_ancla IS NULL;

ALTER TABLE users
  ADD COLUMN cuota_desde DATE NULL COMMENT 'Al reactivar un alumno: la deuda y los pagos se cuentan desde esta fecha';

-- La fecha de ingreso no debe cambiar si alguna vez se actualiza la fila
-- (TIMESTAMP con ON UPDATE CURRENT_TIMESTAMP la pisaba; además TIMESTAMP termina en 2038).
ALTER TABLE incomes
  MODIFY addmission_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de ingreso';
