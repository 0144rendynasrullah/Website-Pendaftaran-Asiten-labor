-- ============================================
-- DATABASE: db_pendaftaran_aslab
-- Sistem Informasi Pendaftaran Asisten Laboratorium
-- Universitas Putra Indonesia YPTK Padang
-- ============================================

CREATE DATABASE IF NOT EXISTS db_pendaftaran_aslab;
USE db_pendaftaran_aslab;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    nim VARCHAR(20) UNIQUE NOT NULL,
    role ENUM('pendaftar', 'admin', 'kepala_lab') DEFAULT 'pendaftar',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_nim (nim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: periode_pendaftaran
-- ============================================
CREATE TABLE IF NOT EXISTS periode_pendaftaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_periode VARCHAR(50) NOT NULL,
    tanggal_buka DATE NOT NULL,
    tanggal_tutup DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_periode (tanggal_buka, tanggal_tutup)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: pendaftaran
-- ============================================
CREATE TABLE IF NOT EXISTS pendaftaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    no_pendaftaran VARCHAR(50) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim VARCHAR(20) NOT NULL,
    prodi VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    ipk DECIMAL(3,2) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    alamat TEXT,
    cv_path VARCHAR(255),
    ktp_path VARCHAR(255),
    transkrip_path VARCHAR(255),
    foto_path VARCHAR(255),
    status_berkas ENUM('pending', 'diverifikasi', 'ditolak') DEFAULT 'pending',
    status_seleksi ENUM(
        'pending', 
        'lulus_administrasi', 
        'tes_program', 
        'wawancara', 
        'lulus', 
        'tidak_lulus'
    ) DEFAULT 'pending',
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_no_pendaftaran (no_pendaftaran),
    INDEX idx_status (status_berkas, status_seleksi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: jadwal_tes
-- ============================================
CREATE TABLE IF NOT EXISTS jadwal_tes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_tes ENUM('program', 'wawancara') NOT NULL,
    tanggal DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_jadwal (tanggal, jenis_tes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: nilai_tes
-- ============================================
CREATE TABLE IF NOT EXISTS nilai_tes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pendaftaran_id INT NOT NULL,
    jenis_tes ENUM('program', 'wawancara') NOT NULL,
    nilai INT NOT NULL CHECK (nilai >= 0 AND nilai <= 100),
    catatan TEXT,
    input_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id) ON DELETE CASCADE,
    FOREIGN KEY (input_by) REFERENCES users(id),
    UNIQUE KEY unique_nilai (pendaftaran_id, jenis_tes),
    INDEX idx_pendaftaran (pendaftaran_id),
    INDEX idx_nilai (nilai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: validasi_kepala_lab
-- ============================================
CREATE TABLE IF NOT EXISTS validasi_kepala_lab (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pendaftaran_id INT NOT NULL,
    status ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
    catatan TEXT,
    validated_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id) ON DELETE CASCADE,
    FOREIGN KEY (validated_by) REFERENCES users(id),
    INDEX idx_pendaftaran (pendaftaran_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: logs_aktivitas (Optional)
-- ============================================
CREATE TABLE IF NOT EXISTS logs_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATA DEFAULT
-- ============================================

-- Admin default (password: admin123)
INSERT INTO users (username, email, password, full_name, nim, role) VALUES
('admin', 'admin@aslab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin System', 'ADMIN001', 'admin'),
('kepala_lab', 'kepalab@aslab.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kepala Laboratorium', 'KLAB001', 'kepala_lab');

-- Periode Pendaftaran
INSERT INTO periode_pendaftaran (nama_periode, tanggal_buka, tanggal_tutup, is_active) VALUES
('Periode 2026', '2026-01-01', '2026-01-31', 1);

-- ============================================
-- VIEWS (Optional untuk laporan)
-- ============================================

-- View untuk laporan lengkap
CREATE OR REPLACE VIEW v_laporan_pendaftaran AS
SELECT 
    p.id,
    p.no_pendaftaran,
    p.nama_lengkap,
    p.nim,
    p.prodi,
    p.semester,
    p.ipk,
    p.status_berkas,
    p.status_seleksi,
    p.catatan_admin,
    p.created_at as tanggal_daftar,
    u.email,
    u.role,
    n_program.nilai as nilai_program,
    n_wawancara.nilai as nilai_wawancara,
    v.status as validasi_status,
    v.catatan as validasi_catatan
FROM pendaftaran p
JOIN users u ON p.user_id = u.id
LEFT JOIN nilai_tes n_program ON p.id = n_program.pendaftaran_id AND n_program.jenis_tes = 'program'
LEFT JOIN nilai_tes n_wawancara ON p.id = n_wawancara.pendaftaran_id AND n_wawancara.jenis_tes = 'wawancara'
LEFT JOIN validasi_kepala_lab v ON p.id = v.pendaftaran_id;

-- ============================================
-- TRIGGER: Generate no_pendaftaran
-- ============================================
DELIMITER //
CREATE TRIGGER tr_generate_no_pendaftaran 
BEFORE INSERT ON pendaftaran
FOR EACH ROW
BEGIN
    SET NEW.no_pendaftaran = CONCAT('REG-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 9999), 4, '0'));
END//
DELIMITER ;

-- ============================================
-- END OF FILE
-- ============================================