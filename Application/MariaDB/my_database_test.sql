-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jun 23, 2025 at 08:03 PM
-- Server version: 11.5.2-MariaDB-ubu2404
-- PHP Version: 8.2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `my_database_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `hourly_control`
--

CREATE TABLE `hourly_control` (
  `id` int(11) NOT NULL COMMENT 'Primary Key',
  `id_user` int(11) NOT NULL COMMENT 'User ID',
  `project_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `date_in` datetime DEFAULT NULL COMMENT 'Get in to work',
  `date_out` datetime DEFAULT NULL COMMENT 'Get out of work',
  `total_time_worked` time DEFAULT NULL COMMENT 'Total time worked',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `hourly_control`
--

INSERT INTO `hourly_control` (`id`, `id_user`, `project_id`, `task_id`, `date_in`, `date_out`, `total_time_worked`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '2024-10-06 21:54:38', '2024-10-06 21:54:43', '00:00:05', '2025-06-14 15:04:18', '2025-06-21 19:24:00'),
(237, 1, 3, 6, '2025-06-21 21:39:23', '2025-06-22 11:10:19', NULL, '2025-06-21 19:39:23', '2025-06-22 09:10:59'),
(238, 1, 3, 6, '2025-06-22 11:21:36', '2025-06-22 11:21:45', '00:00:09', '2025-06-22 09:21:36', '2025-06-22 09:23:24'),
(239, 1, 3, 6, '2025-06-22 11:33:49', '2025-06-22 11:33:58', '00:00:09', '2025-06-22 09:33:49', '2025-06-22 09:34:12'),
(240, 1, 3, 6, '2025-06-22 11:40:26', '2025-06-22 11:40:42', '00:00:16', '2025-06-22 09:40:26', '2025-06-22 09:43:54'),
(241, 1, 3, 6, '2025-06-22 11:44:40', '2025-06-22 11:45:11', '00:00:31', '2025-06-22 09:44:40', '2025-06-22 09:45:21');

-- --------------------------------------------------------

--
-- Table structure for table `hourly_control_backup`
--

CREATE TABLE `hourly_control_backup` (
  `id` int(11) NOT NULL COMMENT 'Primary Key',
  `id_user` int(11) NOT NULL COMMENT 'User ID',
  `project_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `date_in` datetime DEFAULT NULL COMMENT 'Get in to work',
  `date_out` datetime DEFAULT NULL COMMENT 'Get out of work',
  `total_time_worked` time DEFAULT NULL COMMENT 'Total time worked',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `hourly_control_backup`
--

INSERT INTO `hourly_control_backup` (`id`, `id_user`, `project_id`, `task_id`, `date_in`, `date_out`, `total_time_worked`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '2024-10-06 21:54:38', '2024-10-06 21:54:43', NULL, '2025-06-14 15:04:18', '2025-06-14 15:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `active`) VALUES
(1, 'Example Project', 1),
(2, 'Main Project', 1),
(3, 'Hourly Control', 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id_role` tinyint(11) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id_role`, `role`) VALUES
(1, 'ROLE_ADMIN'),
(2, 'ROLE_USER');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `task_name` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_name`, `active`) VALUES
(1, 'Refactor HomeController', 1),
(2, 'Refactor AdminController', 1),
(3, 'Create Relationships between tables', 1),
(4, 'Improve select elements in main view', 1),
(5, 'Add tests', 1),
(6, 'Refactor HourlyController', 1),
(7, 'Create DB my_database_test', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_role` tinyint(4) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `email`, `password`, `id_role`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$ogfCYy6rVto2lawPtHCONuYgHsVDjYvBMqk6KXY/EdTkGddW7kmJ.', 1),
(2, 'mario', 'mario@mario.com', '$2y$10$W8t/Qcv3NgXk9XN.yIJfzOJso5lOUtimt.8qpqpRlOF/VtZ3xotrK', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hourly_control`
--
ALTER TABLE `hourly_control`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hourly_control_project` (`project_id`),
  ADD KEY `fk_hourly_control_task` (`task_id`),
  ADD KEY `idx_id_user` (`id_user`),
  ADD KEY `idx_user_project` (`id_user`,`project_id`);

--
-- Indexes for table `hourly_control_backup`
--
ALTER TABLE `hourly_control_backup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hourly_control_project` (`project_id`),
  ADD KEY `fk_hourly_control_task` (`task_id`),
  ADD KEY `idx_id_user` (`id_user`),
  ADD KEY `idx_user_project` (`id_user`,`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hourly_control`
--
ALTER TABLE `hourly_control`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `hourly_control_backup`
--
ALTER TABLE `hourly_control_backup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` tinyint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
