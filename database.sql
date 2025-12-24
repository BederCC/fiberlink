-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-12-2025 a las 06:16:26
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `fiberlink`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `dni_ruc` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `coordinates` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `installations`
--

CREATE TABLE `installations` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `completed_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `installation_details`
--

CREATE TABLE `installation_details` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_at_moment` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid','overdue','cancelled') DEFAULT 'unpaid',
  `type` enum('monthly','installation','product_sale') DEFAULT 'monthly',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('cash','bank_transfer','yape','plin','credit_card') DEFAULT 'cash',
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_metrics`
--

CREATE TABLE `payment_metrics` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `search_timestamp` datetime NOT NULL,
  `payment_timestamp` datetime NOT NULL,
  `duration_seconds` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `speed_mbps` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plans`
--

INSERT INTO `plans` (`id`, `name`, `speed_mbps`, `price`, `description`, `created_at`) VALUES
(1, 'Fibra 100Mbps', 100, 59.00, 'Ideal para 1-2 personas. Navegación, streaming HD simple y teletrabajo ligero. (Tu plan actual de referencia).', '2025-12-09 07:03:30'),
(2, 'Fibra 500 Mbps', 500, 79.90, 'Para hogares con 3-4 usuarios. Soporta streaming en 4K simultáneo y gaming casual.', '2025-12-10 07:09:54'),
(3, 'Fibra 600 Mbps', 600, 89.90, 'El punto dulce para la mayoría de las familias. Velocidad simétrica alta para home office intensivo y e-learning sin interrupciones.', '2025-12-10 07:12:27'),
(4, 'Fibra 800 Mbps', 800, 109.90, 'Diseñado para gamers y creadores de contenido. Mínima latencia y carga de archivos grandes muy rápida.', '2025-12-10 07:12:57'),
(5, 'Fibra 1000 Mbps', 1000, 149.90, 'La máxima velocidad. Para hogares o PYMEs con más de 10 dispositivos conectados, streaming múltiple y servicios en la nube.', '2025-12-10 07:13:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `created_at`) VALUES
(1, 'Router WiFi 6 TP-Link', 'Dispositivo que termina la línea de fibra óptica y convierte la señal en Wi-Fi y Ethernet.', 80.00, 9, '2025-12-09 07:03:13'),
(2, 'Repetidor / Extensor de Rango Wi-Fi', 'Dispositivo que se conecta al router principal para ampliar la cobertura de la señal Wi-Fi.', 60.00, 9, '2025-12-10 07:03:10'),
(3, 'Sistema Wi-Fi MESH (Pack de 2 o 3)', 'Conjunto de dispositivos que trabajan juntos para crear una única red Wi-Fi', 220.00, 5, '2025-12-10 07:05:53'),
(4, 'Cable de Fibra Óptica (Drop Cable)', 'El cable de fibra óptica de la empresa que entra a la instalación.', 20.00, 30, '2025-12-10 07:06:34'),
(5, 'Conector Mecánico (SC/APC o SC/UPC)', 'Componente clave utilizado por el técnico para finalizar el cable de fibra óptica dentro de la roseta', 6.00, 25, '2025-12-10 07:07:01'),
(6, 'Roseta Óptica (Caja Terminal)', 'Pequeña caja de plástico instalada en la pared donde termina el cable de fibra óptica', 12.00, 8, '2025-12-10 07:07:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `router_model` varchar(100) DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `billing_day` int(11) DEFAULT 1,
  `service_status` enum('active','suspended','cut') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','technician','sales') DEFAULT 'admin',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$Y3/lGaGEo2WxAFqXqxjZqOfoSnS504V4sRdVL6ofSiVzv54GWcJxe', 'Administrador Sistema', 'admin', 1, '2025-12-09 07:02:18');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni_ruc` (`dni_ruc`);

--
-- Indices de la tabla `installations`
--
ALTER TABLE `installations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indices de la tabla `installation_details`
--
ALTER TABLE `installation_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `client_id` (`client_id`);

--
-- Indices de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `payment_metrics`
--
ALTER TABLE `payment_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indices de la tabla `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `installations`
--
ALTER TABLE `installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `installation_details`
--
ALTER TABLE `installation_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `payment_metrics`
--
ALTER TABLE `payment_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `installations`
--
ALTER TABLE `installations`
  ADD CONSTRAINT `installations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `installations_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `installation_details`
--
ALTER TABLE `installation_details`
  ADD CONSTRAINT `installation_details_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `installation_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Filtros para la tabla `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `payment_metrics`
--
ALTER TABLE `payment_metrics`
  ADD CONSTRAINT `payment_metrics_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`);

--
-- Filtros para la tabla `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);
COMMIT;
