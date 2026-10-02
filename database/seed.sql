-- =====================================================================
-- Datos de prueba FICTICIOS para desarrollo. No usar en producción.
-- Las fechas son relativas al día de hoy para que haya cuotas al día,
-- por vencer y vencidas.
-- =====================================================================

INSERT INTO `classes` (`id_class`, `name_class`, `price_class`) VALUES
(1, 'Kick Boxing', 10000),
(2, 'Boxeo', 12000),
(3, 'Muay Thai', 11000);

INSERT INTO `users` (`id_user`, `rfid`, `dni`, `user_name`, `user_surname`, `password`, `birth_day`, `email`, `phone_number`, `asset`, `type_user`) VALUES
(1, 'SIN LLAVERO', 30111222, 'Ana', 'Profesora', NULL, '1990-05-10', 'ana.profe@example.com', '1100000001', 1, 1),
(2, 'SIN LLAVERO', 30222333, 'Bruno', 'Instructor', NULL, '1988-11-02', NULL, NULL, 1, 1),
(3, 'A1B2C3D4', 40111222, 'Carla', 'Alumna', NULL, CURDATE() - INTERVAL 25 YEAR, 'carla@example.com', '1100000002', 1, 2),
(4, 'B2C3D4E5', 40222333, 'Diego', 'Moroso', NULL, '2001-03-15', NULL, NULL, 1, 2),
(5, 'C3D4E5F6', 40333444, 'Elena', 'Sinclase', NULL, '1999-07-21', NULL, NULL, 1, 2),
(6, 'D4E5F6A7', 40444555, 'Fede', 'Inactivo', NULL, '1995-01-30', NULL, NULL, 0, 2);

INSERT INTO `teacher_class` (`id_user`, `id_class`) VALUES
(1, 1),
(2, 2);

INSERT INTO `user_class` (`id_user`, `id_class`) VALUES
(3, 1),
(3, 2),
(4, 1),
(6, 3);

INSERT INTO `payments` (`id_user`, `discharge_date`, `date_of_renovation`) VALUES
(3, CURDATE() - INTERVAL 28 DAY, CURDATE() + INTERVAL 3 DAY),
(4, CURDATE() - INTERVAL 45 DAY, CURDATE() - INTERVAL 14 DAY),
(5, CURDATE(), CURDATE() + INTERVAL 1 MONTH);

INSERT INTO `incomes` (`id_user`, `addmission_date`) VALUES
(3, NOW() - INTERVAL 1 DAY),
(3, NOW() - INTERVAL 2 HOUR);
