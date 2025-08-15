-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 25/07/2025 às 21:05
-- Versão do servidor: 10.11.10-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u333528817_escorts`
--

DELIMITER $$
--
-- Funções
--
CREATE DEFINER=`u333528817_escorts`@`127.0.0.1` FUNCTION `user_likes_count` (`user_id` INT, `target_type_param` ENUM('escort','user')) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE like_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO like_count 
    FROM likes 
    WHERE user_id = user_id AND target_type = target_type_param;
    
    RETURN like_count;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `active_users`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `active_users` (
`id` int(11)
,`username` varchar(50)
,`email` varchar(100)
,`last_login` datetime
,`created_at` timestamp
);

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
-- Estrutura para tabela `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_type` enum('escort','user') NOT NULL,
  `target_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `is_hidden` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comment_stats`
--

CREATE TABLE `comment_stats` (
  `target_type` enum('escort','user') NOT NULL,
  `target_id` int(11) NOT NULL,
  `total_comments` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `map_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('chica','trans') DEFAULT 'chica',
  `whatsapp` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `escorts`
--

INSERT INTO `escorts` (`id`, `name`, `age`, `image_url`, `location`, `zone`, `description`, `phone`, `nationality`, `vip`, `top`, `super_vip`, `verificado`, `telegram`, `city_id`, `latitude`, `longitude`, `map_address`, `created_at`, `type`, `whatsapp`) VALUES
(3, 'Nicole', 27, 'https://media.emasex.com/404710/conversions/WhatsApp-Image-2025-07-16-at-17.47.32-medium-size.jpg', 'Madrid', 'Zona Moncloa', 'Chica colombiana hermosa', '611334077', 'br', 1, 0, 0, 1, 1, 3, 40.41680000, -3.70380000, 'Madrid, España', '2025-07-17 21:38:13', 'trans', 1),
(4, 'Tabata', 33, 'https://media.emasex.com/404623/conversions/20241107_154126-medium-size.jpg', 'Santander', 'Zona Centro', 'Brasileña simpática y encantadora', '654465916', 'br', 0, 0, 0, 0, 1, 1, 43.46230000, -3.80990000, 'Santander, Cantabria, España', '2025-07-17 21:38:13', 'chica', 1),
(5, 'Adri', 44, 'https://media.emasex.com/404612/conversions/20250609_165051(1)-medium-size.jpg', 'Madrid', 'Zona Carabanchel', 'Me encanta viajar y leer', '642677531', 'br', 0, 0, 1, 0, 1, 1, 40.17183719, -3.61925773, '', '2025-07-17 21:38:13', 'chica', 1),
(6, 'Alicia', 25, 'https://media.emasex.com/401565/conversions/1165009-medium-size.jpg', 'Coruña', 'Zona Centro', 'Auténtica y con muchas ganas de conocer gente', '667581203', 'br', 0, 0, 1, 0, 1, 1, 43.36230000, -8.41150000, 'A Coruña, Galicia, España', '2025-07-17 21:38:13', 'chica', 1),
(7, 'Manuela Gomes', 20, 'https://i.pinimg.com/736x/51/15/89/5115897cce1387828b64784dc9e294e6.jpg', 'San Andreas - Madrid', 'Madrid', 'Sou brasileira e tal', '2199999', 'br', 0, 0, 1, 1, 1, 29, NULL, NULL, NULL, '2025-07-19 11:11:33', 'trans', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `escort_gallery`
--

CREATE TABLE `escort_gallery` (
  `id` int(11) NOT NULL,
  `escort_id` int(11) NOT NULL,
  `image_url` text NOT NULL,
  `image_title` varchar(100) DEFAULT NULL,
  `image_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `escort_gallery`
--

INSERT INTO `escort_gallery` (`id`, `escort_id`, `image_url`, `image_title`, `image_order`, `created_at`) VALUES
(1, 3, 'https://media.emasex.com/404710/conversions/WhatsApp-Image-2025-07-16-at-17.47.32-medium-size.jpg', 'Foto Principal', 1, '2025-07-23 16:39:56'),
(2, 3, 'https://via.placeholder.com/400x600/ff6b6b/ffffff?text=Foto+2', 'Foto Glamour', 2, '2025-07-23 16:39:56'),
(3, 3, 'https://via.placeholder.com/400x600/feca57/ffffff?text=Foto+3', 'Foto Casual', 3, '2025-07-23 16:39:56'),
(4, 4, 'https://media.emasex.com/404623/conversions/20241107_154126-medium-size.jpg', 'Foto Principal', 1, '2025-07-23 16:39:56'),
(7, 5, 'https://media.emasex.com/406872/responsive-images/WhatsApp-Image-2025-07-18-at-10.32.56-%282%29___large_1024_1365.jpg', '', 0, '2025-07-23 16:45:58'),
(8, 5, 'https://media.emasex.com/406873/WhatsApp-Image-2025-07-18-at-10.32.56.jpeg', '', 0, '2025-07-23 16:46:12'),
(9, 5, 'https://media.emasex.com/406874/WhatsApp-Image-2025-07-18-at-10.32.56-(3).jpeg', '', 0, '2025-07-23 16:46:23'),
(10, 5, 'https://media.emasex.com/406875/WhatsApp-Image-2025-07-18-at-10.32.56-(1).jpeg', '', 0, '2025-07-23 16:46:31');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `escort_like_stats`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `escort_like_stats` (
`id` int(11)
,`name` varchar(100)
,`image_url` text
,`location` varchar(100)
,`type` enum('chica','trans')
,`total_likes` bigint(21)
,`likes_this_week` bigint(21)
,`likes_today` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_type` enum('escort','user','comment') NOT NULL,
  `target_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `like_stats`
--

CREATE TABLE `like_stats` (
  `target_type` enum('escort','user','comment') NOT NULL,
  `target_id` int(11) NOT NULL,
  `total_likes` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `popular_escorts`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `popular_escorts` (
`id` int(11)
,`name` varchar(100)
,`image_url` text
,`location` varchar(100)
,`total_likes` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `popular_users`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `popular_users` (
`id` int(11)
,`username` varchar(50)
,`email` varchar(100)
,`total_likes` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `recent_activity`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `recent_activity` (
`id` int(11)
,`username` varchar(50)
,`email` varchar(100)
,`action` varchar(50)
,`description` text
,`ip_address` varchar(45)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `avatar` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`, `active`, `avatar`, `bio`, `phone`, `telegram`, `reset_token`, `reset_expires`, `email_verified`, `verification_token`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@citasnortes.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-07-24 19:16:01', '2025-07-24 04:49:54', '2025-07-24 19:16:01'),
(2, 'teste', 'admin1@gmail.com', '$2y$10$bKjYGHZm4NmaZwixdxS/e.gv.efBaMH0bwgXdDNIk9mdBaEBXzOHC', 0, 1, NULL, 'Teste', '21993645756', '@teste', NULL, NULL, 0, NULL, '2025-07-24 17:53:51', '2025-07-24 04:52:15', '2025-07-24 17:53:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 04:56:23'),
(2, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 05:09:09'),
(3, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 05:20:48'),
(4, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 05:22:56'),
(5, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 14:41:28'),
(6, 1, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 17:43:42'),
(7, 1, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 17:45:39'),
(8, 2, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 19:15:55'),
(9, 1, 'escort_created', 'Criou escort: Teste', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 19:57:24'),
(10, 1, 'escort_deleted', 'Deletou escort ID: 11', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 20:09:20'),
(11, 1, 'logout', 'Usuário fez logout', '192.141.146.202', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 OPR/120.0.0.0', '2025-07-24 20:09:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para view `active_users`
--
DROP TABLE IF EXISTS `active_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u333528817_escorts`@`127.0.0.1` SQL SECURITY DEFINER VIEW `active_users`  AS SELECT `users`.`id` AS `id`, `users`.`username` AS `username`, `users`.`email` AS `email`, `users`.`last_login` AS `last_login`, `users`.`created_at` AS `created_at` FROM `users` WHERE `users`.`active` = 1 ;

-- --------------------------------------------------------

--
-- Estrutura para view `escort_like_stats`
--
DROP TABLE IF EXISTS `escort_like_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u333528817_escorts`@`127.0.0.1` SQL SECURITY DEFINER VIEW `escort_like_stats`  AS SELECT `e`.`id` AS `id`, `e`.`name` AS `name`, `e`.`image_url` AS `image_url`, `e`.`location` AS `location`, `e`.`type` AS `type`, count(`l`.`id`) AS `total_likes`, count(case when `l`.`created_at` >= current_timestamp() - interval 7 day then 1 end) AS `likes_this_week`, count(case when `l`.`created_at` >= current_timestamp() - interval 1 day then 1 end) AS `likes_today` FROM (`escorts` `e` left join `likes` `l` on(`l`.`target_type` = 'escort' and `l`.`target_id` = `e`.`id`)) GROUP BY `e`.`id`, `e`.`name`, `e`.`image_url`, `e`.`location`, `e`.`type` ;

-- --------------------------------------------------------

--
-- Estrutura para view `popular_escorts`
--
DROP TABLE IF EXISTS `popular_escorts`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u333528817_escorts`@`127.0.0.1` SQL SECURITY DEFINER VIEW `popular_escorts`  AS SELECT `e`.`id` AS `id`, `e`.`name` AS `name`, `e`.`image_url` AS `image_url`, `e`.`location` AS `location`, coalesce(`ls`.`total_likes`,0) AS `total_likes` FROM (`escorts` `e` left join `like_stats` `ls` on(`ls`.`target_type` = 'escort' and `ls`.`target_id` = `e`.`id`)) ORDER BY coalesce(`ls`.`total_likes`,0) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `popular_users`
--
DROP TABLE IF EXISTS `popular_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u333528817_escorts`@`127.0.0.1` SQL SECURITY DEFINER VIEW `popular_users`  AS SELECT `u`.`id` AS `id`, `u`.`username` AS `username`, `u`.`email` AS `email`, coalesce(`ls`.`total_likes`,0) AS `total_likes` FROM (`users` `u` left join `like_stats` `ls` on(`ls`.`target_type` = 'user' and `ls`.`target_id` = `u`.`id`)) WHERE `u`.`active` = 1 ORDER BY coalesce(`ls`.`total_likes`,0) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `recent_activity`
--
DROP TABLE IF EXISTS `recent_activity`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u333528817_escorts`@`127.0.0.1` SQL SECURITY DEFINER VIEW `recent_activity`  AS SELECT `ual`.`id` AS `id`, `u`.`username` AS `username`, `u`.`email` AS `email`, `ual`.`action` AS `action`, `ual`.`description` AS `description`, `ual`.`ip_address` AS `ip_address`, `ual`.`created_at` AS `created_at` FROM (`user_activity_logs` `ual` left join `users` `u` on(`ual`.`user_id` = `u`.`id`)) ORDER BY `ual`.`created_at` DESC LIMIT 0, 100 ;

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
-- Índices de tabela `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_type_id` (`target_type`,`target_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Índices de tabela `comment_stats`
--
ALTER TABLE `comment_stats`
  ADD PRIMARY KEY (`target_type`,`target_id`);

--
-- Índices de tabela `escorts`
--
ALTER TABLE `escorts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `city_id` (`city_id`);

--
-- Índices de tabela `escort_gallery`
--
ALTER TABLE `escort_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escort_id` (`escort_id`),
  ADD KEY `image_order` (`image_order`);

--
-- Índices de tabela `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`target_type`,`target_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_type_id` (`target_type`,`target_id`);

--
-- Índices de tabela `like_stats`
--
ALTER TABLE `like_stats`
  ADD PRIMARY KEY (`target_type`,`target_id`);

--
-- Índices de tabela `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `from_user_id` (`from_user_id`),
  ADD KEY `read_at` (`read_at`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`read_at`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `reset_token` (`reset_token`),
  ADD KEY `verification_token` (`verification_token`),
  ADD KEY `idx_users_email_active` (`email`,`active`),
  ADD KEY `idx_users_username_active` (`username`,`active`),
  ADD KEY `idx_users_last_login` (`last_login`);

--
-- Índices de tabela `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_activity_logs_user_action` (`user_id`,`action`);

--
-- Índices de tabela `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_token` (`session_token`),
  ADD KEY `expires_at` (`expires_at`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `escorts`
--
ALTER TABLE `escorts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `escort_gallery`
--
ALTER TABLE `escort_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `escorts`
--
ALTER TABLE `escorts`
  ADD CONSTRAINT `escorts_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`);

--
-- Restrições para tabelas `escort_gallery`
--
ALTER TABLE `escort_gallery`
  ADD CONSTRAINT `escort_gallery_ibfk_1` FOREIGN KEY (`escort_id`) REFERENCES `escorts` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Tabela de planos de assinatura
CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 30,
  `features` JSON NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir os planos
INSERT INTO `subscription_plans` (`name`, `slug`, `price`, `duration_days`, `features`, `color`, `icon`, `sort_order`) VALUES
('Bronze', 'bronze', 5.99, 30, '{"likes": true, "comments": false, "private_photos": false, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#CD7F32', 'fas fa-medal', 1),
('Silver', 'silver', 9.99, 30, '{"likes": true, "comments": true, "private_photos": false, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#C0C0C0', 'fas fa-gem', 2),
('Silver+', 'silver_plus', 14.99, 30, '{"likes": true, "comments": true, "private_photos": true, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#E6E6FA', 'fas fa-star', 3),
('Gold', 'gold', 19.99, 30, '{"likes": true, "comments": true, "private_photos": true, "private_videos": true, "emoji_comments": false, "highlighted_comments": false}', '#FFD700', 'fas fa-crown', 4),
('Platinum', 'platinum', 29.99, 30, '{"likes": true, "comments": true, "private_photos": true, "private_videos": true, "emoji_comments": true, "highlighted_comments": true}', '#E5E4E2', 'fas fa-diamond', 5);

-- Tabela de assinaturas dos usuários
CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled','pending') DEFAULT 'pending',
  `starts_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  KEY `expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de transações de pagamento
CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'EUR',
  `payment_method` varchar(50) NOT NULL,
  `payment_provider` varchar(50) NOT NULL,
  `provider_transaction_id` varchar(200) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
  `payment_data` JSON DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adicionar coluna de plano atual na tabela users
ALTER TABLE `users` ADD COLUMN `current_plan` varchar(50) DEFAULT 'free' AFTER `is_admin`;
ALTER TABLE `users` ADD COLUMN `plan_expires_at` datetime DEFAULT NULL AFTER `current_plan`;

-- Tabela para fotos/vídeos privados
CREATE TABLE `escort_private_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `escort_id` int(11) NOT NULL,
  `media_type` enum('photo','video') NOT NULL,
  `media_url` text NOT NULL,
  `thumbnail_url` text DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `required_plan` varchar(50) NOT NULL DEFAULT 'silver_plus',
  `media_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `escort_id` (`escort_id`),
  KEY `media_type` (`media_type`),
  KEY `required_plan` (`required_plan`),
  FOREIGN KEY (`escort_id`) REFERENCES `escorts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Atualizar tabela de comentários para suportar emojis e destaque
ALTER TABLE `comments` ADD COLUMN `has_emoji` tinyint(1) DEFAULT 0 AFTER `content`;
ALTER TABLE `comments` ADD COLUMN `is_highlighted` tinyint(1) DEFAULT 0 AFTER `has_emoji`;
ALTER TABLE `comments` ADD COLUMN `user_plan` varchar(50) DEFAULT 'free' AFTER `is_highlighted`;

-- Função para verificar se usuário tem permissão
DELIMITER $$
CREATE FUNCTION `user_has_permission`(user_id INT, permission_type VARCHAR(50)) 
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE user_plan VARCHAR(50) DEFAULT 'free';
    DECLARE plan_features JSON;
    DECLARE has_permission BOOLEAN DEFAULT FALSE;
    
    -- Buscar plano atual do usuário
    SELECT u.current_plan INTO user_plan
    FROM users u 
    WHERE u.id = user_id 
    AND (u.plan_expires_at IS NULL OR u.plan_expires_at > NOW());
    
    -- Se não tem plano ativo, usar free
    IF user_plan IS NULL THEN
        SET user_plan = 'free';
    END IF;
    
    -- Se for free, só pode curtir (se implementarmos isso)
    IF user_plan = 'free' THEN
        SET has_permission = FALSE;
    ELSE
        -- Buscar features do plano
        SELECT sp.features INTO plan_features
        FROM subscription_plans sp
        WHERE sp.slug = user_plan;
        
        -- Verificar se tem a permissão específica
        IF plan_features IS NOT NULL THEN
            SET has_permission = JSON_EXTRACT(plan_features, CONCAT('$.', permission_type)) = true;
        END IF;
    END IF;
    
    RETURN has_permission;
END$$
DELIMITER ;

-- View para assinaturas ativas
CREATE VIEW `active_subscriptions` AS
SELECT 
    us.*,
    u.username,
    u.email,
    sp.name as plan_name,
    sp.features as plan_features,
    sp.color as plan_color,
    sp.icon as plan_icon
FROM user_subscriptions us
JOIN users u ON us.user_id = u.id
JOIN subscription_plans sp ON us.plan_id = sp.id
WHERE us.status = 'active' 
AND us.expires_at > NOW();

-- View para estatísticas de planos
CREATE VIEW `plan_statistics` AS
SELECT 
    sp.name as plan_name,
    sp.slug as plan_slug,
    sp.price,
    COUNT(us.id) as total_subscribers,
    COUNT(CASE WHEN us.status = 'active' THEN 1 END) as active_subscribers,
    SUM(CASE WHEN us.status = 'active' THEN sp.price ELSE 0 END) as monthly_revenue
FROM subscription_plans sp
LEFT JOIN user_subscriptions us ON sp.id = us.plan_id
GROUP BY sp.id, sp.name, sp.slug, sp.price
ORDER BY sp.sort_order;