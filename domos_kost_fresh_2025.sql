-- =============================================
-- DATABASE FRESH DOMOS KOST 2025
-- Password Plain Text & Data Terbaru
-- =============================================

-- Drop dan buat database baru
DROP DATABASE IF EXISTS domos_kost;
CREATE DATABASE domos_kost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE domos_kost;

-- =============================================
-- TABEL LARAVEL SANCTUM (WAJIB)
-- =============================================

-- Table untuk Laravel Sanctum Auth
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Sessions
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABEL SISTEM DOMOS KOST
-- =============================================

-- ROLES
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USERS (Password Plain Text)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telp VARCHAR(20),
    alamat TEXT,
    foto_profil VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- KAMAR
CREATE TABLE kamar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_kamar VARCHAR(10) NOT NULL UNIQUE,
    lantai INT NOT NULL,
    tipe_kamar ENUM('Single', 'Double', 'Triple') DEFAULT 'Single',
    tarif_bulanan DECIMAL(10,2) NOT NULL,
    fasilitas TEXT,
    status_kamar ENUM('Tersedia', 'Terisi', 'Maintenance') DEFAULT 'Tersedia',
    foto_kamar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_kode_kamar (kode_kamar),
    INDEX idx_status (status_kamar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PENGHUNI
CREATE TABLE penghuni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    kamar_id INT,
    no_ktp VARCHAR(20) NOT NULL UNIQUE,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    pekerjaan VARCHAR(100),
    nama_kontak_darurat VARCHAR(100),
    telp_kontak_darurat VARCHAR(20),
    tanggal_masuk DATE NOT NULL,
    tanggal_keluar DATE,
    status_penghuni ENUM('Aktif', 'Non-Aktif', 'Pending') DEFAULT 'Aktif',
    foto_ktp VARCHAR(255),
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kamar_id) REFERENCES kamar(id) ON DELETE SET NULL,
    INDEX idx_status_penghuni (status_penghuni),
    INDEX idx_tanggal_masuk (tanggal_masuk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TAGIHAN
CREATE TABLE tagihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    penghuni_id INT NOT NULL,
    periode_bulan INT NOT NULL,
    periode_tahun INT NOT NULL,
    tanggal_terbit DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    total_tagihan DECIMAL(10,2) NOT NULL DEFAULT 0,
    denda DECIMAL(10,2) DEFAULT 0,
    status_tagihan ENUM('Belum Dibayar', 'Dibayar Sebagian', 'Lunas', 'Terlambat') DEFAULT 'Belum Dibayar',
    catatan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (penghuni_id) REFERENCES penghuni(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_periode (periode_bulan, periode_tahun),
    INDEX idx_status_tagihan (status_tagihan),
    INDEX idx_jatuh_tempo (tanggal_jatuh_tempo),
    UNIQUE KEY uk_tagihan_periode (penghuni_id, periode_bulan, periode_tahun)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- DETAIL TAGIHAN
CREATE TABLE detail_tagihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tagihan_id INT NOT NULL,
    jenis_tagihan ENUM('Sewa Kamar', 'Laundry', 'Denda', 'Lainnya') NOT NULL,
    deskripsi VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tagihan_id) REFERENCES tagihan(id) ON DELETE CASCADE,
    INDEX idx_jenis_tagihan (jenis_tagihan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PEMBAYARAN
CREATE TABLE pembayaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tagihan_id INT NOT NULL,
    tanggal_bayar DATETIME NOT NULL,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    metode_bayar ENUM('Tunai', 'Transfer Bank', 'E-Wallet') NOT NULL,
    bukti_bayar VARCHAR(255),
    status_verifikasi ENUM('Menunggu', 'Terverifikasi', 'Ditolak') DEFAULT 'Menunggu',
    verifikasi_oleh INT,
    tanggal_verifikasi DATETIME,
    catatan_verifikasi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tagihan_id) REFERENCES tagihan(id) ON DELETE CASCADE,
    FOREIGN KEY (verifikasi_oleh) REFERENCES users(id),
    INDEX idx_tanggal_bayar (tanggal_bayar),
    INDEX idx_status_verifikasi (status_verifikasi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- JENIS LAYANAN LAUNDRY
CREATE TABLE jenis_layanan_laundry (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_layanan VARCHAR(100) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    estimasi_hari INT NOT NULL DEFAULT 1,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ORDER LAUNDRY
CREATE TABLE order_laundry (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_order VARCHAR(20) NOT NULL UNIQUE,
    penghuni_id INT NOT NULL,
    jenis_layanan_id INT NOT NULL,
    petugas_terima_id INT,
    petugas_selesai_id INT,
    tanggal_terima DATETIME NOT NULL,
    tanggal_estimasi_selesai DATE,
    tanggal_selesai DATETIME,
    berat_kg DECIMAL(5,2) NOT NULL,
    total_biaya DECIMAL(10,2) NOT NULL,
    status_order ENUM('Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika', 'Siap Diambil', 'Selesai', 'Dibatalkan') DEFAULT 'Diterima',
    status_bayar ENUM('Belum Dibayar', 'Sudah Dibayar') DEFAULT 'Belum Dibayar',
    catatan_order TEXT,
    foto_bukti_terima VARCHAR(255),
    foto_bukti_selesai VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (penghuni_id) REFERENCES penghuni(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_layanan_id) REFERENCES jenis_layanan_laundry(id),
    FOREIGN KEY (petugas_terima_id) REFERENCES users(id),
    FOREIGN KEY (petugas_selesai_id) REFERENCES users(id),
    INDEX idx_kode_order (kode_order),
    INDEX idx_status_order (status_order),
    INDEX idx_tanggal_terima (tanggal_terima)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- STATUS LAUNDRY LOG
CREATE TABLE status_laundry_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_laundry_id INT NOT NULL,
    status_sebelum VARCHAR(50),
    status_sesudah VARCHAR(50) NOT NULL,
    diubah_oleh INT NOT NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_laundry_id) REFERENCES order_laundry(id) ON DELETE CASCADE,
    FOREIGN KEY (diubah_oleh) REFERENCES users(id),
    INDEX idx_order_id (order_laundry_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- NOTIFIKASI
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi_notifikasi TEXT NOT NULL,
    tipe_notifikasi ENUM('Tagihan', 'Pembayaran', 'Laundry', 'Umum') NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    tanggal_baca DATETIME,
    link_terkait VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_tipe (tipe_notifikasi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PENGATURAN
CREATE TABLE pengaturan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- INSERT DATA 2025 - PASSWORD PLAIN TEXT
-- =============================================

-- Insert Roles
INSERT INTO roles (nama_role, deskripsi) VALUES
('Admin', 'Pemilik kost dengan akses penuh ke sistem'),
('Koordinator', 'Koordinator operasional kost dan laundry'),
('Pengawas', 'Pengawas operasional harian kost'),
('Petugas Laundry', 'Petugas yang menangani layanan laundry'),
('Petugas Kebersihan', 'Petugas yang menangani kebersihan kost'),
('Petugas Keamanan', 'Petugas yang menjaga keamanan kost'),
('Penghuni', 'Penyewa kamar kost');

-- Insert Users (PASSWORD PLAIN TEXT)
-- Admin & Staff
INSERT INTO users (role_id, username, email, password, nama_lengkap, no_telp, alamat) VALUES
(1, 'admin', 'admin@domoskost.com', 'admin123', 'Admin Domos Kost', '081234567890', 'Jl. Parang III Gg. Pekan Jaya No. 88, Medan'),
(1, 'pelita', 'pelita.ginting@domoskost.com', 'admin123', 'Pelita Ginting', '081234567891', 'Jl. Parang III Gg. Pekan Jaya No. 88, Medan'),
(2, 'jhon', 'jhon.sembiring@domoskost.com', 'staff123', 'Jhon Sembiring', '081234567892', 'Medan Johor'),
(3, 'dedi', 'dedi.wirawan@domoskost.com', 'staff123', 'Dedi Wirawan', '081234567893', 'Medan Johor'),
(4, 'diana', 'diana@domoskost.com', 'staff123', 'Diana', '081234567894', 'Medan'),
(4, 'desri', 'desri@domoskost.com', 'staff123', 'Desri', '081234567895', 'Medan');

-- Penghuni Kost
INSERT INTO users (role_id, username, email, password, nama_lengkap, no_telp, alamat) VALUES
(7, 'thika', 'thika@gmail.com', 'user123', 'Thika', '081345678901', 'Medan'),
(7, 'togi', 'togi@gmail.com', 'user123', 'Togi', '081345678902', 'Medan'),
(7, 'beto', 'beto@gmail.com', 'user123', 'Beto', '081345678903', 'Medan'),
(7, 'christine', 'christine@gmail.com', 'user123', 'Christine', '081345678904', 'Medan'),
(7, 'desy', 'desy@gmail.com', 'user123', 'Desy', '081345678905', 'Medan'),
(7, 'elya', 'elya@gmail.com', 'user123', 'Elya', '081345678906', 'Medan');

-- Insert Kamar
INSERT INTO kamar (kode_kamar, lantai, tipe_kamar, tarif_bulanan, fasilitas, status_kamar) VALUES
('L1-01', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-02', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-03', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-04', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-05', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-06', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L2-01', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Tersedia'),
('L2-02', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Tersedia');

-- Insert Penghuni
INSERT INTO penghuni (user_id, kamar_id, no_ktp, tempat_lahir, tanggal_lahir, jenis_kelamin, pekerjaan, tanggal_masuk, status_penghuni) VALUES
(7, 1, '1271045678901234', 'Medan', '2001-05-15', 'Perempuan', 'Mahasiswa', '2025-01-15', 'Aktif'),
(8, 2, '1271045678901235', 'Pematang Siantar', '2000-08-22', 'Laki-laki', 'Mahasiswa', '2025-01-20', 'Aktif'),
(9, 3, '1271045678901236', 'Binjai', '1999-12-10', 'Laki-laki', 'Karyawan Swasta', '2025-01-25', 'Aktif'),
(10, 4, '1271045678901237', 'Medan', '2002-03-18', 'Perempuan', 'Mahasiswa', '2025-02-01', 'Aktif'),
(11, 5, '1271045678901238', 'Tebing Tinggi', '2001-07-25', 'Perempuan', 'Karyawan Swasta', '2025-02-05', 'Aktif'),
(12, 6, '1271045678901239', 'Medan', '2000-09-30', 'Perempuan', 'Mahasiswa', '2025-02-10', 'Aktif');

-- Insert Jenis Layanan Laundry
INSERT INTO jenis_layanan_laundry (nama_layanan, harga_per_kg, estimasi_hari, deskripsi) VALUES
('Cuci Regular', 6000, 2, 'Cuci dan keringkan, lipat rapi'),
('Cuci Express', 10000, 1, 'Cuci kilat 1 hari selesai'),
('Cuci Setrika', 8000, 2, 'Cuci, keringkan, dan setrika'),
('Cuci Setrika Express', 12000, 1, 'Cuci setrika kilat 1 hari'),
('Setrika Saja', 4000, 1, 'Hanya setrika tanpa cuci');

-- Insert Tagihan Februari 2025
INSERT INTO tagihan (penghuni_id, periode_bulan, periode_tahun, tanggal_terbit, tanggal_jatuh_tempo, total_tagihan, status_tagihan, created_by) VALUES
(1, 2, 2025, '2025-02-01', '2025-02-10', 800000, 'Lunas', 1),
(2, 2, 2025, '2025-02-01', '2025-02-10', 800000, 'Lunas', 1),
(3, 2, 2025, '2025-02-01', '2025-02-10', 850000, 'Belum Dibayar', 1),
(4, 2, 2025, '2025-02-01', '2025-02-10', 850000, 'Dibayar Sebagian', 1),
(5, 2, 2025, '2025-02-01', '2025-02-10', 800000, 'Belum Dibayar', 1),
(6, 2, 2025, '2025-02-01', '2025-02-10', 800000, 'Lunas', 1);

-- Insert Detail Tagihan
INSERT INTO detail_tagihan (tagihan_id, jenis_tagihan, deskripsi, quantity, harga_satuan, subtotal) VALUES
(1, 'Sewa Kamar', 'Sewa Kamar L1-01 Februari 2025', 1, 800000, 800000),
(2, 'Sewa Kamar', 'Sewa Kamar L1-02 Februari 2025', 1, 800000, 800000),
(3, 'Sewa Kamar', 'Sewa Kamar L1-03 Februari 2025', 1, 850000, 850000),
(4, 'Sewa Kamar', 'Sewa Kamar L1-04 Februari 2025', 1, 850000, 850000),
(5, 'Sewa Kamar', 'Sewa Kamar L1-05 Februari 2025', 1, 800000, 800000),
(6, 'Sewa Kamar', 'Sewa Kamar L1-06 Februari 2025', 1, 800000, 800000);

-- Insert Pembayaran untuk yang sudah lunas
INSERT INTO pembayaran (tagihan_id, tanggal_bayar, jumlah_bayar, metode_bayar, status_verifikasi, verifikasi_oleh, tanggal_verifikasi) VALUES
(1, '2025-02-05 10:30:00', 800000, 'Transfer Bank', 'Terverifikasi', 1, '2025-02-05 11:00:00'),
(2, '2025-02-06 14:20:00', 800000, 'Tunai', 'Terverifikasi', 1, '2025-02-06 14:30:00'),
(4, '2025-02-08 13:20:00', 500000, 'Tunai', 'Terverifikasi', 1, '2025-02-08 13:30:00'),
(6, '2025-02-07 09:30:00', 800000, 'E-Wallet', 'Terverifikasi', 1, '2025-02-07 09:45:00');

-- Insert Order Laundry
INSERT INTO order_laundry (kode_order, penghuni_id, jenis_layanan_id, petugas_terima_id, tanggal_terima, tanggal_estimasi_selesai, berat_kg, total_biaya, status_order, status_bayar) VALUES
('LD-202502-001', 1, 1, 5, '2025-02-15 08:30:00', '2025-02-17', 3.5, 21000, 'Selesai', 'Sudah Dibayar'),
('LD-202502-002', 3, 3, 5, '2025-02-16 09:15:00', '2025-02-18', 4.0, 32000, 'Siap Diambil', 'Belum Dibayar'),
('LD-202502-003', 2, 2, 6, '2025-02-17 10:00:00', '2025-02-18', 2.5, 25000, 'Dicuci', 'Belum Dibayar');

-- Insert Notifikasi
INSERT INTO notifikasi (user_id, judul, isi_notifikasi, tipe_notifikasi, is_read) VALUES
(7, 'Tagihan Februari 2025', 'Tagihan sewa kamar bulan Februari 2025 sebesar Rp 800.000 telah diterbitkan. Jatuh tempo: 10 Februari 2025', 'Tagihan', TRUE),
(9, 'Tagihan Februari 2025', 'Tagihan sewa kamar bulan Februari 2025 sebesar Rp 850.000 telah diterbitkan. Jatuh tempo: 10 Februari 2025', 'Tagihan', FALSE),
(7, 'Laundry Selesai', 'Cucian Anda dengan kode LD-202502-001 sudah selesai dan telah diserahkan', 'Laundry', TRUE),
(9, 'Laundry Siap Diambil', 'Cucian Anda dengan kode LD-202502-002 sudah siap diambil', 'Laundry', FALSE);

-- Insert Pengaturan
INSERT INTO pengaturan (setting_key, setting_value, setting_type, deskripsi) VALUES
('nama_kost', 'Domos Kost Group', 'text', 'Nama kost yang akan ditampilkan di sistem'),
('alamat_kost', 'Jl. Parang III Gg. Pekan Jaya No. 88, Kelurahan Kwala Bekala, Kecamatan Medan Johor, P. Bulan, 20142', 'text', 'Alamat lengkap kost'),
('no_telp_kost', '081234567890', 'text', 'Nomor telepon kost'),
('email_kost', 'info@domoskost.com', 'text', 'Email resmi kost'),
('batas_hari_jatuh_tempo', '10', 'number', 'Hari jatuh tempo pembayaran setiap bulan'),
('denda_per_hari', '10000', 'number', 'Denda keterlambatan per hari');

-- =============================================
-- DATA SUMMARY 2025
-- =============================================
-- Total Users: 12 (2 Admin, 1 Koordinator, 1 Pengawas, 2 Petugas Laundry, 6 Penghuni)
-- Total Kamar: 8 (6 terisi, 2 tersedia) 
-- Total Penghuni Aktif: 6
-- Total Tagihan Februari 2025: 6 (3 Lunas, 1 Dibayar Sebagian, 2 Belum Dibayar)
-- Total Order Laundry: 3 (1 Selesai, 1 Siap Diambil, 1 Dalam Proses)
-- =============================================

SELECT 'DATABASE FRESH DOMOS KOST 2025 BERHASIL DIBUAT!' as status;
