-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 18/07/2025 às 05:11
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `escorts`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cities`
--

INSERT INTO `cities` (`id`, `title`, `slug`, `image_url`, `created_at`) VALUES
(1, 'Coruña', 'coruna', 'https://i.ibb.co/HfMrfH8R/coruna.jpg', '2025-07-17 21:37:51'),
(3, 'Gijon', 'gijon', 'https://i.ibb.co/svTzK4kD/gijon.jpg', '2025-07-17 21:37:51'),
(8, 'Bilbao', 'bilbao', 'https://i.ibb.co/3YFDgd2D/bilbao.jpg', '2025-07-18 02:30:21'),
(10, 'Burgos', 'burgos', 'https://i.ibb.co/zT433J4c/burgos.jpg', '2025-07-18 02:31:33'),
(11, 'Figueres', 'figueres', 'https://i.ibb.co/C54KW3Kc/figueres.jpg', '2025-07-18 02:32:27'),
(17, 'Logroño', 'logrono', 'https://i.ibb.co/mCcZ0tVF/logrono.jpg', '2025-07-18 02:35:57'),
(18, 'Ourense', 'ourense', 'https://i.ibb.co/5gqzqjFq/ourense.jpg', '2025-07-18 02:36:38'),
(19, 'Oviedo', 'oviedo', 'https://i.ibb.co/Vp27V13K/oviedo.jpg', '2025-07-18 02:37:53'),
(20, 'Pamplona', 'pamplona', 'https://i.ibb.co/qLmdZtYg/pamplona.jpg', '2025-07-18 02:38:35'),
(21, 'Pontevedra', 'pontevedra', 'https://i.ibb.co/RGH0JgV9/pontevedra.jpg', '2025-07-18 02:39:44'),
(22, 'Salamanca', 'salamanca', 'https://i.ibb.co/ZRkfmD1b/salamanca.jpg', '2025-07-18 02:40:14'),
(23, 'San Sebastián', 'sansebastian', 'https://i.ibb.co/7JngRYF2/sansebastian.jpg', '2025-07-18 02:40:52'),
(24, 'Santander', 'santader', 'https://i.ibb.co/spY9gX6X/santander.jpg', '2025-07-18 02:41:26'),
(25, 'Santiago', 'santiago', 'https://i.ibb.co/hFXvxGYS/santiago.jpg', '2025-07-18 02:42:45'),
(26, 'Valencia', 'valencia', 'https://i.ibb.co/YBRVMQmC/valencia.jpg', '2025-07-18 02:43:39'),
(27, 'Valladolid', 'valladolid', 'https://i.ibb.co/RT6y9tWj/valladolid.jpg', '2025-07-18 02:44:12'),
(28, 'Vigo', 'vigo', 'https://i.ibb.co/BHnf3WGD/vigo.jpg', '2025-07-18 02:45:00'),
(29, 'Vitoria', 'vitoria', 'https://i.ibb.co/F4vD8kW2/vitoria.jpg', '2025-07-18 02:45:26'),
(35, 'Zaragoza', 'zaragoza', 'https://i.ibb.co/FqzygWHG/zaragoza.jpg', '2025-07-18 02:49:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `escorts`
--

CREATE TABLE `escorts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `nationality` varchar(10) DEFAULT NULL,
  `vip` tinyint(1) DEFAULT 0,
  `top` tinyint(1) DEFAULT 0,
  `super_vip` tinyint(1) DEFAULT 0,
  `verificado` tinyint(1) DEFAULT 0,
  `telegram` tinyint(1) DEFAULT 0,
  `city_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('chica','trans') DEFAULT 'chica'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `escorts`
--

INSERT INTO `escorts` (`id`, `name`, `age`, `image_url`, `location`, `zone`, `description`, `phone`, `nationality`, `vip`, `top`, `super_vip`, `verificado`, `telegram`, `city_id`, `created_at`, `type`) VALUES
(3, 'Nicole', 25, 'https://media.emasex.com/404710/conversions/WhatsApp-Image-2025-07-16-at-17.47.32-medium-size.jpg', 'Madrid', 'Zona Moncloa', 'Chica colombiana hermosa', '611334077', 'COL', 1, 0, 0, 1, 1, 3, '2025-07-17 21:38:13', 'trans'),
(4, 'Tabata', 33, 'https://media.emasex.com/404623/conversions/20241107_154126-medium-size.jpg', 'Santander', 'Zona Centro', 'Brasileña simpática y encantadora', '654465916', 'BRA', 0, 1, 0, 0, 1, 1, '2025-07-17 21:38:13', 'chica'),
(5, 'Adri', 44, 'https://media.emasex.com/404612/conversions/20250609_165051(1)-medium-size.jpg', 'Madrid', 'Zona Carabanchel', 'Me encanta viajar y leer', '642677531', 'ROU', 0, 0, 1, 0, 1, 1, '2025-07-17 21:38:13', 'chica'),
(6, 'Alicia', 25, 'https://media.emasex.com/401565/conversions/1165009-medium-size.jpg', 'Coruña', 'Zona Centro', 'Auténtica y con muchas ganas de conocer gente', '667581203', 'PAN', 0, 0, 0, 0, 1, 1, '2025-07-17 21:38:13', 'chica');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `escorts`
--
ALTER TABLE `escorts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `escorts`
--
ALTER TABLE `escorts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `escorts`
--
ALTER TABLE `escorts`
  ADD CONSTRAINT `escorts_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
