-- =====================================================================
-- Esquema de la base de datos del sistema de fichajes (solo estructura).
-- Para datos de prueba ejecutar luego: database/seed.sql
-- Para crear el primer administrador: php bin/crear-admin.php
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pfc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `a_class`
--

CREATE TABLE `a_class` (
  `id_a_class` int(255) NOT NULL,
  `id_class` int(255) NOT NULL,
  `date_class` datetime NOT NULL,
  `id_user` int(255) NOT NULL,
  `id_user_teacher` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `classes`
--

CREATE TABLE `classes` (
  `id_class` int(255) NOT NULL,
  `name_class` text NOT NULL COMMENT 'Nombre de la clase',
  `price_class` int(255) NOT NULL COMMENT 'Precio de la clase'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incomes`
--

CREATE TABLE `incomes` (
  `id_income` int(255) NOT NULL,
  `id_user` int(255) NOT NULL COMMENT 'Id unico del Usuario para saber sus ingresos',
  `addmission_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha de ingreso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id_payment` int(255) NOT NULL,
  `id_user` int(255) NOT NULL COMMENT 'ID unico de cada usuario',
  `discharge_date` date NOT NULL COMMENT 'Fecha de pago de servicio',
  `date_of_renovation` date NOT NULL COMMENT 'Fecha de renovación de servicio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teacher_class`
--

CREATE TABLE `teacher_class` (
  `id_teacher_class` int(255) NOT NULL,
  `id_user` int(255) NOT NULL,
  `id_class` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `types_users`
--

CREATE TABLE `types_users` (
  `id_type_user` int(255) NOT NULL,
  `description` text NOT NULL COMMENT 'Tipo de Usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `types_users` (`id_type_user`, `description`) VALUES
(1, 'PROFESOR'),
(2, 'ALUMNO'),
(3, 'ADMINISTRADOR');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uid_incomes`
--

CREATE TABLE `uid_incomes` (
  `id_uid_incomes` int(255) NOT NULL,
  `uid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int(255) NOT NULL,
  `rfid` longtext NOT NULL COMMENT 'Serial tarjeta de acceso',
  `dni` int(8) NOT NULL,
  `user_name` varchar(75) NOT NULL,
  `user_surname` varchar(75) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `email` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `asset` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Define si el Usuario\r\nse encuentra activo\r\n1 = Activo; 0 = Inactivo',
  `type_user` int(255) NOT NULL DEFAULT 2 COMMENT '1 - PROFESOR\r\n2 - ALUMNO\r\n3 - ADMINISTRADOR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_class`
--

CREATE TABLE `user_class` (
  `id_user_class` int(255) NOT NULL,
  `id_user` int(255) NOT NULL,
  `id_class` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `a_class`
--
ALTER TABLE `a_class`
  ADD PRIMARY KEY (`id_a_class`),
  ADD KEY `id_class` (`id_class`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_user_teacher` (`id_user_teacher`);

--
-- Indices de la tabla `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id_class`);

--
-- Indices de la tabla `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id_income`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD PRIMARY KEY (`id_teacher_class`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_class` (`id_class`);

--
-- Indices de la tabla `types_users`
--
ALTER TABLE `types_users`
  ADD PRIMARY KEY (`id_type_user`);

--
-- Indices de la tabla `uid_incomes`
--
ALTER TABLE `uid_incomes`
  ADD PRIMARY KEY (`id_uid_incomes`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `type_user` (`type_user`);

--
-- Indices de la tabla `user_class`
--
ALTER TABLE `user_class`
  ADD PRIMARY KEY (`id_user_class`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_class` (`id_class`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `a_class`
--
ALTER TABLE `a_class`
  MODIFY `id_a_class` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `classes`
--
ALTER TABLE `classes`
  MODIFY `id_class` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id_income` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `teacher_class`
--
ALTER TABLE `teacher_class`
  MODIFY `id_teacher_class` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `types_users`
--
ALTER TABLE `types_users`
  MODIFY `id_type_user` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `uid_incomes`
--
ALTER TABLE `uid_incomes`
  MODIFY `id_uid_incomes` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_class`
--
ALTER TABLE `user_class`
  MODIFY `id_user_class` int(255) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
