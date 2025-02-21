-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-02-2025 a las 20:10:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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

--
-- Volcado de datos para la tabla `classes`
--

INSERT INTO `classes` (`id_class`, `name_class`, `price_class`) VALUES
(6, 'Clase Alguna', 589),
(7, 'Clase Alguno 2', 56),
(8, 'Clase de prueba 01', 560);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incomes`
--

CREATE TABLE `incomes` (
  `id_income` int(255) NOT NULL,
  `id_user` int(255) NOT NULL COMMENT 'Id unico del Usuario para saber sus ingresos',
  `addmission_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha de ingreso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `incomes`
--

INSERT INTO `incomes` (`id_income`, `id_user`, `addmission_date`) VALUES
(72, 1, '2024-08-07 02:11:50'),
(81, 1, '2024-08-22 01:20:18'),
(82, 1, '2024-08-27 22:31:23'),
(83, 1, '2024-08-27 22:40:57'),
(84, 1, '2024-08-27 22:47:02'),
(85, 1, '2024-12-16 00:39:30'),
(86, 1, '2024-12-16 01:24:49');

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

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id_payment`, `id_user`, `discharge_date`, `date_of_renovation`) VALUES
(2, 2, '2024-07-01', '2024-07-01'),
(29, 1, '2024-08-03', '2024-09-02'),
(85, 1, '2023-02-02', '2023-03-04'),
(86, 1, '2025-05-05', '2025-06-04'),
(87, 1, '2025-05-05', '2025-06-04'),
(88, 41, '2024-08-22', '2024-09-21'),
(89, 43, '2024-08-22', '2024-09-21'),
(90, 44, '2024-08-22', '2024-09-21'),
(91, 1, '0222-02-02', '0222-03-04'),
(92, 2, '2024-12-16', '2025-01-15'),
(93, 17, '2025-02-20', '2025-03-22'),
(94, 17, '2025-02-20', '2025-03-22'),
(95, 17, '2025-02-20', '2025-03-22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teacher_class`
--

CREATE TABLE `teacher_class` (
  `id_teacher_class` int(255) NOT NULL,
  `id_user` int(255) NOT NULL,
  `id_class` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `teacher_class`
--

INSERT INTO `teacher_class` (`id_teacher_class`, `id_user`, `id_class`) VALUES
(3, 19, 4),
(7, 1, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `types_users`
--

CREATE TABLE `types_users` (
  `id_type_user` int(255) NOT NULL,
  `description` text NOT NULL COMMENT 'Tipo de Usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `types_users`
--

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
  `phone_number` int(13) DEFAULT NULL,
  `asset` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Define si el Usuario\r\nse encuentra activo\r\n1 = Activo; 0 = Inactivo',
  `type_user` int(255) NOT NULL DEFAULT 2 COMMENT '1 - PROFESOR\r\n2 - ALUMNO\r\n3 - ADMINISTRADOR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `rfid`, `dni`, `user_name`, `user_surname`, `password`, `birth_day`, `email`, `phone_number`, `asset`, `type_user`) VALUES
(1, '73dcc413', 41550112, 'Cristian Ariel', 'Marquez', '123', '1998-11-08', 'arielmolus25@gmail.com', 1162023318, 1, 1),
(2, '3a5cf681', 21903130, 'Gustavo Alejandro', 'Marquez', NULL, '2010-07-01', 'pitu702010@gmail.com', 1182153615, 1, 0),
(17, 'SIN LLAVERO', 15268365, 'Patricio Valentin', 'Marquez', NULL, '0000-00-00', 'algo@algo.com', 0, 1, 2),
(18, '133FD513', 20771757, 'Maria Fabriana', 'Garcia de Jalon', NULL, '2015-02-01', 'email@email.com', 2147483647, 0, 2),
(19, '2a1b9916', 12345678, 'Usuario', 'Prueba', NULL, '2000-02-02', 'prueba@prueba.com', 1155223366, 1, 3),
(44, 'SIN LLAVERO', 15151515, 'Prueba', '', NULL, '0000-00-00', '', 0, 1, 2),
(45, 'SIN LLAVERO', 18181818, 'Profesor', '', NULL, '0000-00-00', '', 0, 1, 3);

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
-- Volcado de datos para la tabla `user_class`
--

INSERT INTO `user_class` (`id_user_class`, `id_user`, `id_class`) VALUES
(1, 17, 6),
(2, 18, 6),
(8, 17, 8);

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
  MODIFY `id_class` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id_income` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `teacher_class`
--
ALTER TABLE `teacher_class`
  MODIFY `id_teacher_class` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `types_users`
--
ALTER TABLE `types_users`
  MODIFY `id_type_user` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `uid_incomes`
--
ALTER TABLE `uid_incomes`
  MODIFY `id_uid_incomes` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `user_class`
--
ALTER TABLE `user_class`
  MODIFY `id_user_class` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
