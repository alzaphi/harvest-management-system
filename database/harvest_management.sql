-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 02:44 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `harvest_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `id` int(11) NOT NULL,
  `vendor_angkut_id` int(11) DEFAULT NULL,
  `kode_lambung` varchar(50) DEFAULT NULL,
  `plat_nomor` varchar(20) DEFAULT NULL,
  `jenis_unit` enum('Pickup','Truck') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_angkut`
--

CREATE TABLE `vendor_angkut` (
  `id` int(11) NOT NULL,
  `kode_vendor` varchar(20) NOT NULL,
  `nama_vendor` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `jenis_vendor` varchar(50) DEFAULT NULL,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `nomor_rekening` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor_angkut`
--

INSERT INTO `vendor_angkut` (`id`, `kode_vendor`, `nama_vendor`, `no_hp`, `jenis_vendor`, `status`, `nomor_rekening`, `nama_bank`, `created_at`, `updated_at`) VALUES
(7, 'VA00001', 'jonathan', '085676896234', 'Vendor Angkut', 'Aktif', '1234567890', 'BSI', '2025-05-25 05:22:10', '2025-05-25 10:17:42'),
(8, 'VA00002', 'ISMAN', '085381519904', 'Vendor Angkut', 'Aktif', '9876543210', 'BCA', '2025-05-25 05:22:55', '2025-05-25 10:15:46'),
(9, 'VA00003', 'WAHYU', '085368462892', 'Vendor Angkut', 'Aktif', '1122334455', 'BCA', '2025-05-25 05:23:09', '2025-05-25 10:15:46'),
(10, 'VA00004', 'JOKO ANWAR', '085677889902', 'Vendor Angkut', 'Aktif', '5566778890', 'BSI', '2025-05-25 06:37:09', '2025-05-25 12:39:39'),
(11, 'VA00005', 'MUHAMMAD BUDI', '081278996578', 'Vendor Angkut', 'Aktif', '1010101010', 'BCA', '2025-05-25 06:37:40', '2025-05-25 10:15:46'),
(12, 'VA00006', 'SAIFULLAH', '085767775432', 'Vendor Angkut', 'Aktif', '2020202020202', 'Mandiri', '2025-05-25 06:38:22', '2025-05-25 12:39:39'),
(13, 'VA00007', 'DEDI MULYANDI', '086754312331', 'Vendor Angkut', 'Aktif', '3030303030', 'BCA', '2025-05-25 06:38:54', '2025-05-25 10:15:46'),
(14, 'VA00008', 'HORIDAN MUSTOFA', '085672345657', 'Vendor Angkut', 'Aktif', '4040404040', 'BRI', '2025-05-25 06:39:21', '2025-05-25 12:39:39'),
(15, 'VA00009', 'KARIMUN JAYA', '08561234564', 'Vendor Angkut', 'Aktif', '5050505050', 'BNI', '2025-05-25 06:40:09', '2025-05-25 10:15:46'),
(16, 'VA00010', 'REZA SAPUTRA', '08129696877', 'Vendor Angkut', 'Aktif', '6060606060', 'BRI', '2025-05-25 06:40:42', '2025-05-25 12:39:39'),
(17, 'VA00011', 'ANDIKA', '081256795432', 'Vendor Angkut', 'Aktif', '7070707070', 'BCA', '2025-05-25 06:41:12', '2025-05-25 10:15:46'),
(18, 'VA00012', 'ISMAIL MARZUKI', '08387654321', 'Vendor Angkut', 'Aktif', '8080808080808', 'Mandiri', '2025-05-25 06:46:45', '2025-05-25 12:39:39'),
(19, 'VA00013', 'DEDI JEFRAN', '087381122445', 'Vendor Angkut', 'Aktif', '9090909090', 'BCA', '2025-05-25 06:47:19', '2025-05-25 10:15:46'),
(20, 'VA00014', 'EKO MULYA', '085643213467', 'Vendor Angkut', 'Aktif', '1111222233', 'BNI', '2025-05-25 06:47:36', '2025-05-25 10:15:46'),
(24, 'VA00015', 'GUNAWAN', '086754312212', 'Vendor Angkut', 'Aktif', '123451234512345', 'BRI', '2025-05-25 12:04:00', '2025-05-25 12:39:39'),
(26, 'VA00018', 'ARIF HIDAYAT', '082134567891', 'Vendor Angkut', 'Aktif', '2345678901234', 'Mandiri', '2025-05-25 12:40:40', '2025-05-25 12:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_tebang`
--

CREATE TABLE `vendor_tebang` (
  `id` int(11) NOT NULL,
  `kode_vendor` varchar(20) NOT NULL,
  `nama_vendor` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `jenis_vendor` enum('Tebang') DEFAULT 'Tebang',
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_angkut_id` (`vendor_angkut_id`);

--
-- Indexes for table `vendor_angkut`
--
ALTER TABLE `vendor_angkut`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_vendor` (`kode_vendor`);

--
-- Indexes for table `vendor_tebang`
--
ALTER TABLE `vendor_tebang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_vendor` (`kode_vendor`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendor_angkut`
--
ALTER TABLE `vendor_angkut`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vendor_tebang`
--
ALTER TABLE `vendor_tebang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`vendor_angkut_id`) REFERENCES `vendor_angkut` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
