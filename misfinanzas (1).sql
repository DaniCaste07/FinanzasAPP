-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2026 a las 13:41:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `misfinanzas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inversiones`
--

CREATE TABLE `inversiones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `activo` varchar(255) NOT NULL,
  `cantidad_invertida` decimal(15,2) NOT NULL,
  `valor_actual` decimal(15,2) NOT NULL,
  `fecha_compra` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inversiones`
--

INSERT INTO `inversiones` (`id`, `usuario_id`, `activo`, `cantidad_invertida`, `valor_actual`, `fecha_compra`) VALUES
(1, 1, 'BTC', 5000.00, 70200.00, '2023-10-01'),
(3, 1, 'ETH', 2000.00, 1800.00, '2023-12-20'),
(4, 1, 'SOL', 1000.00, 70.53, '2026-01-29'),
(10, 1, 'BTC', 5000.00, 58041.07, '2026-02-10'),
(13, 1, 'DOGE', 200.00, 0.08, '2026-02-10'),
(14, 1, 'ADA', 1000.00, 0.23, '2026-03-03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `moneda` varchar(3) DEFAULT 'EUR',
  `refresco_api` int(11) DEFAULT 15,
  `modo_privacidad` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `avatar`, `moneda`, `refresco_api`, `modo_privacidad`) VALUES
(1, 'marcos acedo', 'marcos@gmail.com', '$2y$10$OAItLD70G5rJ3IxhqRBvC.V1uESGiy0TDlvWoWEopeIJ4Y2Prbhxm', 'user_1_1778758782.jpg', 'EUR', 15, 0),
(2, 'imane', 'imane@gmail.com', '$2y$10$z5frOd2Q9XMkqMcPCWprFe3z1/uPZtValcoWV9J6pgpBnnX0/waWa', 'default.png', 'EUR', 15, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `inversiones`
--
ALTER TABLE `inversiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuario` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `inversiones`
--
ALTER TABLE `inversiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `inversiones`
--
ALTER TABLE `inversiones`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
