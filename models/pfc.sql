-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-08-2024 a las 03:36:02
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
(46, 1, '2024-08-04 13:27:31'),
(47, 18, '2024-08-04 16:21:35'),
(48, 1, '2024-08-04 16:21:52');

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
(5, 16, '2024-07-16', '2024-08-15'),
(6, 17, '2024-07-17', '2024-08-16'),
(7, 18, '2024-07-17', '2024-08-16'),
(14, 19, '2024-07-31', '2024-08-30'),
(20, 24, '2024-08-03', '2024-09-02'),
(25, 1, '2024-02-01', '2024-03-02'),
(26, 1, '2024-08-03', '2024-07-02'),
(27, 1, '2024-08-03', '2024-08-02'),
(28, 1, '0245-01-01', '0245-01-31'),
(29, 1, '2024-08-03', '2024-09-02'),
(30, 29, '2024-08-04', '2024-09-03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` int(255) NOT NULL,
  `rfid` longtext NOT NULL COMMENT 'Serial tarjeta de acceso',
  `dni` int(8) NOT NULL,
  `name` varchar(75) NOT NULL,
  `surname` varchar(75) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `email` text DEFAULT NULL,
  `phone_number` int(13) DEFAULT NULL,
  `asset` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Define si el Usuario\r\nse encuentra activo\r\n1 = Activo; 0 = Inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `rfid`, `dni`, `name`, `surname`, `birth_day`, `email`, `phone_number`, `asset`) VALUES
(1, '73dcc413', 41550112, 'Cristian Ariel', 'Marquez', '1998-11-08', 'arielmolus25@gmail.com', 1162023318, 1),
(2, '3a5cf681', 21903130, 'Gustavo Alejandro', 'Marquez', '2010-07-01', 'pitu702010@gmail.com', 1182153615, 1),
(17, 'SIN LLAVERO', 15268365, 'Patricio Valentin', 'Marquez', '0000-00-00', 'algo@algo.com', 0, 1),
(18, 'SIN LLAVERO', 20771757, 'Maria Fabriana', 'Garcia de Jalon', '2015-02-01', 'email@email.com', 2147483647, 1),
(19, '2a1b9916', 12345678, 'Usuario', 'Prueba', '2000-02-02', 'prueba@prueba.com', 1155223366, 1);

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id_income` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
