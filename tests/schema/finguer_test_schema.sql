
--
-- Base de dades: `epgylzqu_parking_finguer_v2_test`
--

-- --------------------------------------------------------

--
-- Estructura de la taula `usuarios`
--

CREATE TABLE `usuarios` (
  `uuid` binary(16) NOT NULL,
  `email` varchar(255) NOT NULL,
  `estado` enum('pendiente','activo','bloqueado','eliminado') NOT NULL DEFAULT 'pendiente',
  `password` text DEFAULT NULL,
  `tipo_rol` enum('cliente','cliente_anual','admin','trabajador') NOT NULL DEFAULT 'cliente',
  `locale` enum('ca','es','fr','en','it') NOT NULL DEFAULT 'es',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `usuarios_abonos`
--

CREATE TABLE `usuarios_abonos` (
  `id` binary(16) NOT NULL,
  `usuario_uuid` binary(16) NOT NULL,
  `estado` enum('activo','caducado','cancelado','suspendido') NOT NULL DEFAULT 'activo',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `limite_reservas` int(2) NOT NULL DEFAULT 10,
  `vehiculo` varchar(100) DEFAULT NULL,
  `matricula` varchar(20) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `usuarios_perfil`
--

CREATE TABLE `usuarios_perfil` (
  `usuario_uuid` binary(16) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `nif` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(20) DEFAULT 'ES',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de la taula `usuarios_sesiones`
--

CREATE TABLE `usuarios_sesiones` (
  `id` int(11) NOT NULL,
  `usuario_uuid` binary(16) NOT NULL,
  `dispositiu` varchar(100) DEFAULT NULL,
  `navegador` varchar(100) DEFAULT NULL,
  `sistema_operatiu` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Estructura de la taula `parking_servicios_catalogo`
--

CREATE TABLE `parking_servicios_catalogo` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('parking','extra','seguro') NOT NULL DEFAULT 'extra',
  `iva_percent` decimal(5,2) NOT NULL DEFAULT 21.00,
  `precio_base` decimal(10,2) DEFAULT NULL,
  `dias_incluidos` int(11) DEFAULT NULL,
  `min_con_iva` decimal(10,2) DEFAULT NULL,
  `extra_dia_con_iva` decimal(10,2) DEFAULT NULL,
  `modo_precio` enum('FIJO','PORCENTAJE_CONDICIONAL') NOT NULL DEFAULT 'FIJO',
  `seg_base` enum('TOTAL_CON_IVA') NOT NULL DEFAULT 'TOTAL_CON_IVA',
  `seg_umbral_con_iva` decimal(10,2) DEFAULT NULL,
  `seg_min_con_iva` decimal(10,2) DEFAULT NULL,
  `seg_factor` decimal(6,4) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Estructura de la taula `parking_reservas`
--

CREATE TABLE `parking_reservas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_uuid` binary(16) NOT NULL,
  `localizador` varchar(50) NOT NULL,
  `estado` enum('pendiente','procesando_pago','pago_oficina','pagada','cancelada','anual') NOT NULL DEFAULT 'pendiente',
  `estado_vehiculo` enum('pendiente_entrada','dentro','salido') NOT NULL DEFAULT 'pendiente_entrada',
  `fecha_reserva` datetime NOT NULL,
  `entrada_prevista` datetime NOT NULL,
  `salida_prevista` datetime NOT NULL,
  `subtotal_calculado` decimal(10,2) DEFAULT NULL,
  `iva_calculado` decimal(10,2) DEFAULT NULL,
  `total_calculado` decimal(10,2) DEFAULT NULL,
  `vehiculo` varchar(100) DEFAULT NULL,
  `matricula` varchar(20) DEFAULT NULL,
  `personas` tinyint(3) UNSIGNED DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `vuelo` varchar(30) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `canal` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `parking_reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ix_parking_reservas_usuario_uuid` (`usuario_uuid`);

ALTER TABLE `parking_reservas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Bolcament de dades per a la taula `parking_servicios_catalogo`
--

INSERT INTO `parking_servicios_catalogo` (`id`, `codigo`, `nombre`, `descripcion`, `tipo`, `iva_percent`, `precio_base`, `dias_incluidos`, `min_con_iva`, `extra_dia_con_iva`, `modo_precio`, `seg_base`, `seg_umbral_con_iva`, `seg_min_con_iva`, `seg_factor`, `activo`) VALUES
(1, 'RESERVA_FINGUER', 'Reserva Finguer Class', 'Reserva estándar de plaza de aparcamiento Finguer.', 'parking', 21.00, NULL, 10, 100.00, 5.00, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1),
(2, 'RESERVA_FINGUER_GOLD', 'Reserva Gold Finguer Class', 'Reserva premium de plaza de aparcamiento Finguer con servicios mejorados.', 'parking', 21.00, NULL, 10, 140.00, 5.00, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1),
(3, 'LIMPIEZA_EXT', 'Limpieza exterior', 'Servicio de limpieza exterior del vehículo durante la estancia.', 'extra', 21.00, 12.40, NULL, NULL, NULL, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1),
(4, 'LIMPIEZA_EXT_INT', 'Limpieza exterior + interior', 'Servicio de limpieza exterior e interior del vehículo durante la estancia.', 'extra', 21.00, 28.93, NULL, NULL, NULL, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1),
(5, 'LIMPIEZA_PRO', 'Limpieza PRO', 'Servicio de limpieza PRO con mayor nivel de detalle y acabado.', 'extra', 21.00, 78.51, NULL, NULL, NULL, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1),
(6, 'SEGURO_CANCELACION', 'Seguro de cancelación', 'Seguro opcional de cancelación de la reserva.', 'seguro', 21.00, NULL, NULL, NULL, NULL, 'PORCENTAJE_CONDICIONAL', 'TOTAL_CON_IVA', 100.00, 30.00, 0.1000, 1),
(7, 'RESERVA_CLIENTE_ANUAL', 'Reserva Finguer Anual', 'Reserva clientes con bono anual.', 'parking', 21.00, NULL, NULL, NULL, NULL, 'FIJO', 'TOTAL_CON_IVA', NULL, NULL, NULL, 1);


--
-- Índexs per a la taula `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`uuid`);

--
-- Índexs per a la taula `usuarios_abonos`
--
ALTER TABLE `usuarios_abonos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_uuid` (`usuario_uuid`),
  ADD KEY `idx_matricula` (`matricula`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_fin` (`fecha_fin`);

--
-- Índexs per a la taula `usuarios_perfil`
--
ALTER TABLE `usuarios_perfil`
  ADD PRIMARY KEY (`usuario_uuid`);

--
-- Índexs per a la taula `usuarios_sesiones`
--
ALTER TABLE `usuarios_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesiones_usuario` (`usuario_uuid`);

--
-- AUTO_INCREMENT per la taula `usuarios_sesiones`
--
ALTER TABLE `usuarios_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Índexs per a les taules bolcades
--

--
-- Índexs per a la taula `parking_servicios_catalogo`
--
ALTER TABLE `parking_servicios_catalogo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_codigo` (`codigo`);

--
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `parking_servicios_catalogo`
--
ALTER TABLE `parking_servicios_catalogo`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;
