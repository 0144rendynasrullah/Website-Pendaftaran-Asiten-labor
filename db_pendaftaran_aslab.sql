-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jul 2026 pada 13.24
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pendaftaran_aslab`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal_tes`
--

CREATE TABLE `jadwal_tes` (
  `id` int(11) NOT NULL,
  `jenis_tes` enum('program','wawancara') NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal_tes`
--

INSERT INTO `jadwal_tes` (`id`, `jenis_tes`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `lokasi`, `keterangan`, `created_at`) VALUES
(1, 'program', '2026-06-20', '08:00:00', '10:30:00', 'lab 4', 'jangan terlambat', '2026-06-19 03:14:19'),
(2, 'program', '2026-06-21', '10:30:00', '12:30:00', 'lab 4', 'Jangan terlambat', '2026-06-23 13:22:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `nilai_tes`
--

CREATE TABLE `nilai_tes` (
  `id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `jenis_tes` enum('program','wawancara') NOT NULL,
  `nilai` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `input_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `nilai_tes`
--

INSERT INTO `nilai_tes` (`id`, `pendaftaran_id`, `jenis_tes`, `nilai`, `catatan`, `input_by`, `created_at`) VALUES
(1, 1, 'program', 80, 'kurang kreatif', 1, '2026-06-19 13:58:33'),
(2, 1, 'wawancara', 100, '', 1, '2026-06-19 14:04:55'),
(3, 2, 'program', 40, 'bodoh', 1, '2026-06-21 07:05:00'),
(4, 2, 'wawancara', 35, 'no atitude', 1, '2026-06-21 07:05:20'),
(5, 3, 'program', 100, 'sempurna\r\n', 1, '2026-06-24 01:49:20'),
(6, 3, 'wawancara', 80, 'baik', 1, '2026-06-24 01:49:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `no_pendaftaran` varchar(50) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `prodi` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `ipk` decimal(3,2) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `ktp_path` varchar(255) DEFAULT NULL,
  `transkrip_path` varchar(255) DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL,
  `status_berkas` enum('pending','diverifikasi','ditolak') DEFAULT 'pending',
  `status_seleksi` enum('pending','lulus_administrasi','tes_program','wawancara','lulus','tidak_lulus') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `user_id`, `no_pendaftaran`, `nama_lengkap`, `nim`, `prodi`, `semester`, `ipk`, `no_hp`, `alamat`, `cv_path`, `ktp_path`, `transkrip_path`, `foto_path`, `status_berkas`, `status_seleksi`, `catatan_admin`, `created_at`, `updated_at`) VALUES
(1, 2, 'REG-20260619-4611', 'Rendy Nasrullah', '24101152610144', 'Sistem Informasi', 4, 3.90, '083121965134', 'jambi', NULL, NULL, NULL, NULL, 'diverifikasi', 'lulus', '', '2026-06-19 03:00:42', '2026-06-19 14:44:21'),
(2, 4, 'REG-20260619-7644', 'ade', '24101152610115', 'Sistem Informasi', 4, 3.50, '083121965134', 'jln.kampung tanjung', 'uploads/cv_24101152610115_1781884591.pdf', 'uploads/surat_24101152610115_1781884591.pdf', 'uploads/transkrip_24101152610115_1781884591.pdf', 'uploads/foto_24101152610115_1781884591.jpeg', 'diverifikasi', 'tidak_lulus', '', '2026-06-19 15:56:31', '2026-06-21 07:07:08'),
(3, 5, 'REG-20260624-4048', 'rendyy', '24101152610145', 'Sistem Informasi', 4, 4.00, '083121965134', 'jambi', 'uploads/cv_24101152610145_1782265622.pdf', 'uploads/surat_24101152610145_1782265622.pdf', 'uploads/transkrip_24101152610145_1782265622.pdf', 'uploads/foto_24101152610145_1782265622.jpeg', 'diverifikasi', 'lulus', '', '2026-06-24 01:47:02', '2026-06-24 01:50:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode_pendaftaran`
--

CREATE TABLE `periode_pendaftaran` (
  `id` int(11) NOT NULL,
  `nama_periode` varchar(50) NOT NULL,
  `tanggal_buka` date NOT NULL,
  `tanggal_tutup` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `role` enum('pendaftar','admin','kepala_lab') DEFAULT 'pendaftar',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `nim`, `role`, `status`, `created_at`) VALUES
(1, 'admin', 'admin@aslab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin System', 'ADMIN001', 'admin', 'aktif', '2026-06-19 00:49:06'),
(2, 'rey', 'rendynasrullah24@gmail.com', '$2y$10$JyE7CTHxAPLmRjU3W9kGr.Oyu0dYi3BvBCcRc0gJbprL6x8FWWoO6', 'Rendy Nasrullah', '24101152610144', 'pendaftar', 'aktif', '2026-06-19 01:37:49'),
(3, 'kepala_lab', 'kepalab@aslab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kepala Laboratorium', 'KLAB001', 'kepala_lab', 'aktif', '2026-06-19 14:42:46'),
(4, 'ade', 'rendynasrullah2427@gmail.com', '$2y$10$RdYFETYt0R14hysdxBYbR.N.jSdCiEKY90iuRtfHI9Sybq.tHTbIi', 'ade', '24101152610115', 'pendaftar', 'aktif', '2026-06-19 15:34:46'),
(5, 'reysumimasenn', 'rendysumimasen2427@gmail.com', '$2y$10$W3CCbhkYnz4GpueDsf1HreATO/PhJpb8wz6l5kACaJ/v62Irkbu7i', 'rendyy', '24101152610145', 'pendaftar', 'aktif', '2026-06-24 01:45:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `validasi_kepala_lab`
--

CREATE TABLE `validasi_kepala_lab` (
  `id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `validated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `validasi_kepala_lab`
--

INSERT INTO `validasi_kepala_lab` (`id`, `pendaftaran_id`, `status`, `catatan`, `validated_by`, `created_at`) VALUES
(1, 1, 'disetujui', '', 3, '2026-06-19 14:44:21'),
(2, 2, 'ditolak', 'bodoh coba tahun depan', 3, '2026-06-21 07:06:20'),
(3, 3, 'disetujui', 'baik', 3, '2026-06-24 01:50:19');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `jadwal_tes`
--
ALTER TABLE `jadwal_tes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `nilai_tes`
--
ALTER TABLE `nilai_tes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`),
  ADD KEY `input_by` (`input_by`);

--
-- Indeks untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `periode_pendaftaran`
--
ALTER TABLE `periode_pendaftaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- Indeks untuk tabel `validasi_kepala_lab`
--
ALTER TABLE `validasi_kepala_lab`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftaran_id` (`pendaftaran_id`),
  ADD KEY `validated_by` (`validated_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jadwal_tes`
--
ALTER TABLE `jadwal_tes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `nilai_tes`
--
ALTER TABLE `nilai_tes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `periode_pendaftaran`
--
ALTER TABLE `periode_pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `validasi_kepala_lab`
--
ALTER TABLE `validasi_kepala_lab`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `nilai_tes`
--
ALTER TABLE `nilai_tes`
  ADD CONSTRAINT `nilai_tes_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`),
  ADD CONSTRAINT `nilai_tes_ibfk_2` FOREIGN KEY (`input_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `validasi_kepala_lab`
--
ALTER TABLE `validasi_kepala_lab`
  ADD CONSTRAINT `validasi_kepala_lab_ibfk_1` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran` (`id`),
  ADD CONSTRAINT `validasi_kepala_lab_ibfk_2` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
