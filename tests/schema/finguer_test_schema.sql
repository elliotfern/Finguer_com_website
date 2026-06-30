
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
-- Índexs per a les taules bolcades
--

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
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `usuarios_sesiones`
--
ALTER TABLE `usuarios_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
