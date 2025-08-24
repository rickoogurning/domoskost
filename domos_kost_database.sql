45678-- =============================================
-- DATABASE SISTEM INFORMASI DOMOS KOST GROUP
-- Pengelolaan Pembayaran Kost & Tracking Laundry
-- Created by: Mustika Sari Sinulingga - 210810065
-- =============================================

-- Drop database jika sudah ada
DROP DATABASE IF EXISTS domos_kost;

-- Buat database baru
CREATE DATABASE domos_kost CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE domos_kost;

-- =============================================
-- TABEL 1: ROLES (Master Data Peran)
-- =============================================
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL UNIQUE,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABEL 2: USERS (Semua Pengguna Sistem)
-- =============================================
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

-- =============================================
-- TABEL 3: KAMAR (Master Data Kamar)
-- =============================================
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

-- =============================================
-- TABEL 4: PENGHUNI (Data Detail Penghuni)
-- =============================================
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

-- =============================================
-- TABEL 5: TAGIHAN (Tagihan Bulanan)
-- =============================================
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

-- =============================================
-- TABEL 6: DETAIL_TAGIHAN (Item dalam Tagihan)
-- =============================================
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

-- =============================================
-- TABEL 7: PEMBAYARAN (Transaksi Pembayaran)
-- =============================================
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

-- =============================================
-- TABEL 8: JENIS_LAYANAN_LAUNDRY (Master Layanan)
-- =============================================
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

-- =============================================
-- TABEL 9: ORDER_LAUNDRY (Pesanan Laundry)
-- =============================================
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

-- =============================================
-- TABEL 10: STATUS_LAUNDRY_LOG (History Status)
-- =============================================
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

-- =============================================
-- TABEL 11: NOTIFIKASI (Sistem Notifikasi)
-- =============================================
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

-- =============================================
-- TABEL 12: PENGATURAN (Settings Sistem)
-- =============================================
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
-- INSERT DATA AWAL
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

-- Insert Users (Password default: password123 - hashed with bcrypt)
-- Admin & Staff
INSERT INTO users (role_id, username, email, password, nama_lengkap, no_telp, alamat) VALUES
(1, 'pelita', 'pelita.ginting@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Pelita Ginting', '081234567890', 'Jl. Parang III Gg. Pekan Jaya No. 88, Medan'),
(2, 'jhon', 'jhon.sembiring@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Jhon Sembiring', '081234567891', 'Medan Johor'),
(3, 'dedi', 'dedi.wirawan@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Dedi Wirawan', '081234567892', 'Medan Johor'),
(4, 'diana', 'diana@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Diana', '081234567893', 'Medan'),
(4, 'desri', 'desri@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Desri', '081234567894', 'Medan'),
(5, 'kristiani', 'kristiani@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Kristiani', '081234567895', 'Medan'),
(5, 'rika', 'rika.ginting@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Rika br Ginting', '081234567896', 'Medan'),
(6, 'arman', 'arman.pane@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Arman Pane', '081234567897', 'Medan'),
(6, 'satria', 'satria.barus@domoskost.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Satria Barus', '081234567898', 'Medan');

-- Penghuni Kost
INSERT INTO users (role_id, username, email, password, nama_lengkap, no_telp, alamat) VALUES
(7, 'thika', 'thika@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Thika', '081345678901', 'Medan'),
(7, 'togi', 'togi@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Togi', '081345678902', 'Medan'),
(7, 'beto', 'beto@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Beto', '081345678903', 'Medan'),
(7, 'christine', 'christine@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Christine', '081345678904', 'Medan'),
(7, 'desy', 'desy@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Desy', '081345678905', 'Medan'),
(7, 'elya', 'elya@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Elya', '081345678906', 'Medan'),
(7, 'kristiany', 'kristiany@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Kristiany', '081345678907', 'Medan'),
(7, 'naca', 'naca@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Naca', '081345678908', 'Medan'),
(7, 'riki', 'riki.ananda@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Riki Ananda', '081345678909', 'Medan'),
(7, 'rivaldo', 'rivaldo@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Rivaldo', '081345678910', 'Medan'),
(7, 'theresia', 'theresia@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Theresia', '081345678911', 'Medan'),
(7, 'john', 'john@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'John', '081345678912', 'Medan'),
(7, 'joy', 'joy@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Joy', '081345678913', 'Medan'),
(7, 'avin', 'avin@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Avin', '081345678914', 'Medan'),
(7, 'aryandi', 'aryandi@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Aryandi', '081345678915', 'Medan'),
(7, 'agri', 'agri@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Agri', '081345678916', 'Medan'),
(7, 'josef', 'josef@gmail.com', '$2y$10$YmY4ZDU3ODk4NzY1NGY0N.qVqQoFGhBa2W0e7mXm6uEW2dRNkpXOi', 'Josef', '081345678917', 'Medan');

-- Insert Kamar (24 kamar total)
INSERT INTO kamar (kode_kamar, lantai, tipe_kamar, tarif_bulanan, fasilitas, status_kamar) VALUES
-- Lantai 1 (12 kamar)
('L1-01', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-02', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-03', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-04', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-05', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-06', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-07', 1, 'Double', 1200000, 'Kasur Double, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-08', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-09', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-10', 1, 'Single', 800000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L1-11', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L1-12', 1, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
-- Lantai 2 (12 kamar)
('L2-01', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L2-02', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L2-03', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L2-04', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Terisi'),
('L2-05', 2, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Terisi'),
('L2-06', 2, 'Double', 1300000, 'Kasur Double, Lemari, Meja, Kursi, AC', 'Tersedia'),
('L2-07', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Tersedia'),
('L2-08', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Tersedia'),
('L2-09', 2, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Tersedia'),
('L2-10', 2, 'Single', 850000, 'Kasur, Lemari, Meja, Kursi, Kipas Angin', 'Tersedia'),
('L2-11', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Maintenance'),
('L2-12', 2, 'Single', 900000, 'Kasur, Lemari, Meja, Kursi, AC', 'Tersedia');

-- Insert Penghuni
INSERT INTO penghuni (user_id, kamar_id, no_ktp, tempat_lahir, tanggal_lahir, jenis_kelamin, pekerjaan, tanggal_masuk, status_penghuni) VALUES
(10, 1, '1271045678901234', 'Medan', '2001-05-15', 'Perempuan', 'Mahasiswa', '2024-01-10', 'Aktif'),
(11, 2, '1271045678901235', 'Pematang Siantar', '2000-08-22', 'Laki-laki', 'Mahasiswa', '2024-02-05', 'Aktif'),
(12, 3, '1271045678901236', 'Binjai', '1999-12-10', 'Laki-laki', 'Karyawan Swasta', '2023-11-20', 'Aktif'),
(13, 4, '1271045678901237', 'Medan', '2002-03-18', 'Perempuan', 'Mahasiswa', '2024-01-15', 'Aktif'),
(14, 5, '1271045678901238', 'Tebing Tinggi', '2001-07-25', 'Perempuan', 'Karyawan Swasta', '2023-12-01', 'Aktif'),
(15, 6, '1271045678901239', 'Medan', '2000-09-30', 'Perempuan', 'Mahasiswa', '2024-03-10', 'Aktif'),
(16, 7, '1271045678901240', 'Kisaran', '2001-01-12', 'Perempuan', 'Karyawan Swasta', '2023-10-15', 'Aktif'),
(17, 8, '1271045678901241', 'Medan', '2002-04-20', 'Perempuan', 'Mahasiswa', '2024-02-20', 'Aktif'),
(18, 9, '1271045678901242', 'Padang Sidempuan', '1999-11-05', 'Laki-laki', 'Karyawan Swasta', '2023-09-10', 'Aktif'),
(19, 10, '1271045678901243', 'Medan', '2000-06-15', 'Laki-laki', 'Karyawan Swasta', '2023-08-25', 'Aktif'),
(20, 11, '1271045678901244', 'Sibolga', '2001-02-28', 'Perempuan', 'Mahasiswa', '2024-01-20', 'Aktif'),
(21, 12, '1271045678901245', 'Medan', '2000-10-10', 'Laki-laki', 'Karyawan Swasta', '2023-07-15', 'Aktif'),
(22, 13, '1271045678901246', 'Rantau Prapat', '2002-05-22', 'Perempuan', 'Mahasiswa', '2024-03-05', 'Aktif'),
(23, 14, '1271045678901247', 'Medan', '2001-08-18', 'Laki-laki', 'Mahasiswa', '2024-02-15', 'Aktif'),
(24, 15, '1271045678901248', 'Lubuk Pakam', '2000-12-25', 'Laki-laki', 'Karyawan Swasta', '2023-11-10', 'Aktif'),
(25, 16, '1271045678901249', 'Medan', '2001-04-30', 'Laki-laki', 'Karyawan Swasta', '2023-12-20', 'Aktif'),
(26, 17, '1271045678901250', 'Stabat', '2002-01-15', 'Laki-laki', 'Mahasiswa', '2024-01-25', 'Aktif');

-- Insert Jenis Layanan Laundry
INSERT INTO jenis_layanan_laundry (nama_layanan, harga_per_kg, estimasi_hari, deskripsi) VALUES
('Cuci Regular', 6000, 2, 'Cuci dan keringkan, lipat rapi'),
('Cuci Express', 10000, 1, 'Cuci kilat 1 hari selesai'),
('Cuci Setrika', 8000, 2, 'Cuci, keringkan, dan setrika'),
('Cuci Setrika Express', 12000, 1, 'Cuci setrika kilat 1 hari'),
('Setrika Saja', 4000, 1, 'Hanya setrika tanpa cuci');

-- Insert Tagihan untuk Bulan Desember 2024
INSERT INTO tagihan (penghuni_id, periode_bulan, periode_tahun, tanggal_terbit, tanggal_jatuh_tempo, total_tagihan, status_tagihan, created_by) VALUES
(1, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Lunas', 1),
(2, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Lunas', 1),
(3, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Lunas', 1),
(4, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Belum Dibayar', 1),
(5, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Lunas', 1),
(6, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Belum Dibayar', 1),
(7, 12, 2024, '2024-12-01', '2024-12-10', 1200000, 'Lunas', 1),
(8, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Dibayar Sebagian', 1),
(9, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Lunas', 1),
(10, 12, 2024, '2024-12-01', '2024-12-10', 800000, 'Belum Dibayar', 1),
(11, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Lunas', 1),
(12, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Lunas', 1),
(13, 12, 2024, '2024-12-01', '2024-12-10', 900000, 'Lunas', 1),
(14, 12, 2024, '2024-12-01', '2024-12-10', 900000, 'Belum Dibayar', 1),
(15, 12, 2024, '2024-12-01', '2024-12-10', 900000, 'Lunas', 1),
(16, 12, 2024, '2024-12-01', '2024-12-10', 900000, 'Lunas', 1),
(17, 12, 2024, '2024-12-01', '2024-12-10', 850000, 'Belum Dibayar', 1);

-- Insert Detail Tagihan (Sewa Kamar)
INSERT INTO detail_tagihan (tagihan_id, jenis_tagihan, deskripsi, quantity, harga_satuan, subtotal) VALUES
(1, 'Sewa Kamar', 'Sewa Kamar L1-01 Desember 2024', 1, 800000, 800000),
(2, 'Sewa Kamar', 'Sewa Kamar L1-02 Desember 2024', 1, 800000, 800000),
(3, 'Sewa Kamar', 'Sewa Kamar L1-03 Desember 2024', 1, 850000, 850000),
(4, 'Sewa Kamar', 'Sewa Kamar L1-04 Desember 2024', 1, 850000, 850000),
(5, 'Sewa Kamar', 'Sewa Kamar L1-05 Desember 2024', 1, 800000, 800000),
(6, 'Sewa Kamar', 'Sewa Kamar L1-06 Desember 2024', 1, 800000, 800000),
(7, 'Sewa Kamar', 'Sewa Kamar L1-07 Desember 2024', 1, 1200000, 1200000),
(8, 'Sewa Kamar', 'Sewa Kamar L1-08 Desember 2024', 1, 850000, 850000),
(9, 'Sewa Kamar', 'Sewa Kamar L1-09 Desember 2024', 1, 800000, 800000),
(10, 'Sewa Kamar', 'Sewa Kamar L1-10 Desember 2024', 1, 800000, 800000),
(11, 'Sewa Kamar', 'Sewa Kamar L1-11 Desember 2024', 1, 850000, 850000),
(12, 'Sewa Kamar', 'Sewa Kamar L1-12 Desember 2024', 1, 850000, 850000),
(13, 'Sewa Kamar', 'Sewa Kamar L2-01 Desember 2024', 1, 900000, 900000),
(14, 'Sewa Kamar', 'Sewa Kamar L2-02 Desember 2024', 1, 900000, 900000),
(15, 'Sewa Kamar', 'Sewa Kamar L2-03 Desember 2024', 1, 900000, 900000),
(16, 'Sewa Kamar', 'Sewa Kamar L2-04 Desember 2024', 1, 900000, 900000),
(17, 'Sewa Kamar', 'Sewa Kamar L2-05 Desember 2024', 1, 850000, 850000);

-- Insert Pembayaran untuk yang sudah lunas
INSERT INTO pembayaran (tagihan_id, tanggal_bayar, jumlah_bayar, metode_bayar, status_verifikasi, verifikasi_oleh, tanggal_verifikasi) VALUES
(1, '2024-12-05 10:30:00', 800000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-05 11:00:00'),
(2, '2024-12-06 14:20:00', 800000, 'Tunai', 'Terverifikasi', 1, '2024-12-06 14:30:00'),
(3, '2024-12-04 09:15:00', 850000, 'E-Wallet', 'Terverifikasi', 1, '2024-12-04 10:00:00'),
(5, '2024-12-07 16:45:00', 800000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-07 17:00:00'),
(7, '2024-12-03 11:30:00', 1200000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-03 12:00:00'),
(8, '2024-12-08 13:20:00', 500000, 'Tunai', 'Terverifikasi', 1, '2024-12-08 13:30:00'), -- Dibayar sebagian
(9, '2024-12-05 15:40:00', 800000, 'E-Wallet', 'Terverifikasi', 1, '2024-12-05 16:00:00'),
(11, '2024-12-06 10:10:00', 850000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-06 10:30:00'),
(12, '2024-12-07 09:30:00', 850000, 'Tunai', 'Terverifikasi', 1, '2024-12-07 09:45:00'),
(13, '2024-12-04 14:50:00', 900000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-04 15:00:00'),
(15, '2024-12-05 11:20:00', 900000, 'E-Wallet', 'Terverifikasi', 1, '2024-12-05 11:30:00'),
(16, '2024-12-06 08:45:00', 900000, 'Transfer Bank', 'Terverifikasi', 1, '2024-12-06 09:00:00');

-- Insert Order Laundry
INSERT INTO order_laundry (kode_order, penghuni_id, jenis_layanan_id, petugas_terima_id, tanggal_terima, tanggal_estimasi_selesai, berat_kg, total_biaya, status_order, status_bayar) VALUES
('LD-202412-001', 1, 1, 4, '2024-12-01 08:30:00', '2024-12-03', 3.5, 21000, 'Selesai', 'Sudah Dibayar'),
('LD-202412-002', 3, 3, 4, '2024-12-02 09:15:00', '2024-12-04', 4.0, 32000, 'Selesai', 'Sudah Dibayar'),
('LD-202412-003', 5, 2, 5, '2024-12-03 10:00:00', '2024-12-04', 2.5, 25000, 'Selesai', 'Sudah Dibayar'),
('LD-202412-004', 7, 1, 4, '2024-12-04 11:30:00', '2024-12-06', 5.0, 30000, 'Selesai', 'Sudah Dibayar'),
('LD-202412-005', 9, 4, 5, '2024-12-05 08:45:00', '2024-12-06', 3.0, 36000, 'Selesai', 'Sudah Dibayar'),
('LD-202412-006', 2, 1, 4, '2024-12-06 09:00:00', '2024-12-08', 4.5, 27000, 'Siap Diambil', 'Belum Dibayar'),
('LD-202412-007', 4, 3, 5, '2024-12-07 10:15:00', '2024-12-09', 3.5, 28000, 'Disetrika', 'Belum Dibayar'),
('LD-202412-008', 6, 2, 4, '2024-12-08 08:30:00', '2024-12-09', 2.0, 20000, 'Dikeringkan', 'Belum Dibayar'),
('LD-202412-009', 8, 1, 5, '2024-12-08 14:00:00', '2024-12-10', 3.0, 18000, 'Dicuci', 'Belum Dibayar'),
('LD-202412-010', 10, 5, 4, '2024-12-09 09:30:00', '2024-12-10', 2.5, 10000, 'Diterima', 'Belum Dibayar');

-- Insert Status Laundry Log untuk order yang sudah selesai
INSERT INTO status_laundry_log (order_laundry_id, status_sebelum, status_sesudah, diubah_oleh) VALUES
-- Order 1
(1, NULL, 'Diterima', 4),
(1, 'Diterima', 'Dicuci', 4),
(1, 'Dicuci', 'Dikeringkan', 4),
(1, 'Dikeringkan', 'Siap Diambil', 4),
(1, 'Siap Diambil', 'Selesai', 4),
-- Order 2
(2, NULL, 'Diterima', 4),
(2, 'Diterima', 'Dicuci', 4),
(2, 'Dicuci', 'Dikeringkan', 5),
(2, 'Dikeringkan', 'Disetrika', 5),
(2, 'Disetrika', 'Siap Diambil', 5),
(2, 'Siap Diambil', 'Selesai', 5);

-- Insert Notifikasi
INSERT INTO notifikasi (user_id, judul, isi_notifikasi, tipe_notifikasi, is_read) VALUES
(10, 'Tagihan Desember 2024', 'Tagihan sewa kamar bulan Desember 2024 sebesar Rp 800.000 telah diterbitkan. Jatuh tempo: 10 Desember 2024', 'Tagihan', TRUE),
(13, 'Tagihan Desember 2024', 'Tagihan sewa kamar bulan Desember 2024 sebesar Rp 850.000 telah diterbitkan. Jatuh tempo: 10 Desember 2024', 'Tagihan', FALSE),
(10, 'Laundry Siap Diambil', 'Cucian Anda dengan kode LD-202412-001 sudah selesai dan siap diambil', 'Laundry', TRUE),
(11, 'Laundry Sedang Diproses', 'Cucian Anda dengan kode LD-202412-006 sedang dalam proses pencucian', 'Laundry', FALSE);

-- Insert Pengaturan
INSERT INTO pengaturan (setting_key, setting_value, setting_type, deskripsi) VALUES
('nama_kost', 'Domos Kost Group', 'text', 'Nama kost yang akan ditampilkan di sistem'),
('alamat_kost', 'Jl. Parang III Gg. Pekan Jaya No. 88, Kelurahan Kwala Bekala, Kecamatan Medan Johor, P. Bulan, 20142', 'text', 'Alamat lengkap kost'),
('no_telp_kost', '081234567890', 'text', 'Nomor telepon kost'),
('email_kost', 'info@domoskost.com', 'text', 'Email resmi kost'),
('batas_hari_jatuh_tempo', '10', 'number', 'Hari jatuh tempo pembayaran setiap bulan'),
('denda_per_hari', '10000', 'number', 'Denda keterlambatan per hari'),
('maksimal_hari_denda', '30', 'number', 'Maksimal hari denda dihitung'),
('notifikasi_pengingat_hari', '3', 'number', 'Kirim notifikasi pengingat X hari sebelum jatuh tempo'),
('enable_whatsapp_notif', 'false', 'boolean', 'Aktifkan notifikasi WhatsApp'),
('enable_email_notif', 'true', 'boolean', 'Aktifkan notifikasi email');

-- =============================================
-- VIEWS UNTUK LAPORAN
-- =============================================

-- View Laporan Pendapatan Bulanan
CREATE VIEW v_laporan_pendapatan_bulanan AS
SELECT 
    t.periode_bulan,
    t.periode_tahun,
    COUNT(DISTINCT t.penghuni_id) as jumlah_penghuni,
    SUM(CASE WHEN dt.jenis_tagihan = 'Sewa Kamar' THEN dt.subtotal ELSE 0 END) as total_sewa,
    SUM(CASE WHEN dt.jenis_tagihan = 'Laundry' THEN dt.subtotal ELSE 0 END) as total_laundry,
    SUM(CASE WHEN dt.jenis_tagihan = 'Denda' THEN dt.subtotal ELSE 0 END) as total_denda,
    SUM(dt.subtotal) as total_pendapatan,
    SUM(CASE WHEN t.status_tagihan = 'Lunas' THEN t.total_tagihan ELSE 0 END) as total_terbayar,
    SUM(CASE WHEN t.status_tagihan != 'Lunas' THEN t.total_tagihan ELSE 0 END) as total_piutang
FROM tagihan t
LEFT JOIN detail_tagihan dt ON t.id = dt.tagihan_id
GROUP BY t.periode_tahun, t.periode_bulan
ORDER BY t.periode_tahun DESC, t.periode_bulan DESC;

-- View Status Kamar
CREATE VIEW v_status_kamar AS
SELECT 
    k.id,
    k.kode_kamar,
    k.lantai,
    k.tipe_kamar,
    k.tarif_bulanan,
    k.status_kamar,
    p.nama_lengkap as nama_penghuni,
    ph.tanggal_masuk,
    ph.status_penghuni
FROM kamar k
LEFT JOIN penghuni ph ON k.id = ph.kamar_id AND ph.status_penghuni = 'Aktif'
LEFT JOIN users p ON ph.user_id = p.id
ORDER BY k.lantai, k.kode_kamar;

-- View Tracking Laundry Real-time
CREATE VIEW v_tracking_laundry AS
SELECT 
    ol.id,
    ol.kode_order,
    u.nama_lengkap as nama_penghuni,
    jl.nama_layanan,
    ol.berat_kg,
    ol.total_biaya,
    ol.tanggal_terima,
    ol.tanggal_estimasi_selesai,
    ol.status_order,
    ol.status_bayar,
    pt.nama_lengkap as petugas_terima,
    ps.nama_lengkap as petugas_selesai
FROM order_laundry ol
JOIN penghuni p ON ol.penghuni_id = p.id
JOIN users u ON p.user_id = u.id
JOIN jenis_layanan_laundry jl ON ol.jenis_layanan_id = jl.id
LEFT JOIN users pt ON ol.petugas_terima_id = pt.id
LEFT JOIN users ps ON ol.petugas_selesai_id = ps.id
ORDER BY ol.tanggal_terima DESC;

-- =============================================
-- STORED PROCEDURES
-- =============================================

DELIMITER //

-- Procedure untuk generate tagihan bulanan
CREATE PROCEDURE sp_generate_tagihan_bulanan(
    IN p_bulan INT,
    IN p_tahun INT,
    IN p_created_by INT
)
BEGIN
    DECLARE v_penghuni_id INT;
    DECLARE v_kamar_id INT;
    DECLARE v_tarif DECIMAL(10,2);
    DECLARE v_tagihan_id INT;
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor untuk penghuni aktif
    DECLARE cur_penghuni CURSOR FOR
        SELECT p.id, p.kamar_id, k.tarif_bulanan
        FROM penghuni p
        JOIN kamar k ON p.kamar_id = k.id
        WHERE p.status_penghuni = 'Aktif';
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur_penghuni;
    
    read_loop: LOOP
        FETCH cur_penghuni INTO v_penghuni_id, v_kamar_id, v_tarif;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Cek apakah tagihan sudah ada
        IF NOT EXISTS (
            SELECT 1 FROM tagihan 
            WHERE penghuni_id = v_penghuni_id 
            AND periode_bulan = p_bulan 
            AND periode_tahun = p_tahun
        ) THEN
            -- Insert tagihan
            INSERT INTO tagihan (
                penghuni_id, periode_bulan, periode_tahun,
                tanggal_terbit, tanggal_jatuh_tempo, total_tagihan,
                status_tagihan, created_by
            ) VALUES (
                v_penghuni_id, p_bulan, p_tahun,
                CONCAT(p_tahun, '-', LPAD(p_bulan, 2, '0'), '-01'),
                CONCAT(p_tahun, '-', LPAD(p_bulan, 2, '0'), '-10'),
                v_tarif, 'Belum Dibayar', p_created_by
            );
            
            SET v_tagihan_id = LAST_INSERT_ID();
            
            -- Insert detail tagihan
            INSERT INTO detail_tagihan (
                tagihan_id, jenis_tagihan, deskripsi,
                quantity, harga_satuan, subtotal
            ) VALUES (
                v_tagihan_id, 'Sewa Kamar',
                CONCAT('Sewa Kamar Bulan ', p_bulan, '/', p_tahun),
                1, v_tarif, v_tarif
            );
        END IF;
    END LOOP;
    
    CLOSE cur_penghuni;
END//

-- Procedure untuk update status laundry dengan log
CREATE PROCEDURE sp_update_status_laundry(
    IN p_order_id INT,
    IN p_status_baru VARCHAR(50),
    IN p_user_id INT,
    IN p_catatan TEXT
)
BEGIN
    DECLARE v_status_lama VARCHAR(50);
    
    -- Get current status
    SELECT status_order INTO v_status_lama
    FROM order_laundry
    WHERE id = p_order_id;
    
    -- Update status
    UPDATE order_laundry
    SET status_order = p_status_baru,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_order_id;
    
    -- Insert log
    INSERT INTO status_laundry_log (
        order_laundry_id, status_sebelum, status_sesudah,
        diubah_oleh, catatan
    ) VALUES (
        p_order_id, v_status_lama, p_status_baru,
        p_user_id, p_catatan
    );
    
    -- Create notification for penghuni
    IF p_status_baru = 'Siap Diambil' THEN
        INSERT INTO notifikasi (user_id, judul, isi_notifikasi, tipe_notifikasi)
        SELECT 
            p.user_id,
            'Laundry Siap Diambil',
            CONCAT('Cucian Anda dengan kode ', ol.kode_order, ' sudah selesai dan siap diambil'),
            'Laundry'
        FROM order_laundry ol
        JOIN penghuni p ON ol.penghuni_id = p.id
        WHERE ol.id = p_order_id;
    END IF;
END//

DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

DELIMITER //

-- Trigger untuk update total tagihan saat insert detail
CREATE TRIGGER trg_update_total_tagihan_insert
AFTER INSERT ON detail_tagihan
FOR EACH ROW
BEGIN
    UPDATE tagihan 
    SET total_tagihan = (
        SELECT SUM(subtotal) 
        FROM detail_tagihan 
        WHERE tagihan_id = NEW.tagihan_id
    )
    WHERE id = NEW.tagihan_id;
END//

-- Trigger untuk update status pembayaran tagihan
CREATE TRIGGER trg_update_status_tagihan
AFTER INSERT ON pembayaran
FOR EACH ROW
BEGIN
    DECLARE v_total_tagihan DECIMAL(10,2);
    DECLARE v_total_bayar DECIMAL(10,2);
    
    -- Get total tagihan
    SELECT total_tagihan INTO v_total_tagihan
    FROM tagihan WHERE id = NEW.tagihan_id;
    
    -- Get total pembayaran
    SELECT SUM(jumlah_bayar) INTO v_total_bayar
    FROM pembayaran 
    WHERE tagihan_id = NEW.tagihan_id 
    AND status_verifikasi = 'Terverifikasi';
    
    -- Update status tagihan
    IF v_total_bayar >= v_total_tagihan THEN
        UPDATE tagihan 
        SET status_tagihan = 'Lunas' 
        WHERE id = NEW.tagihan_id;
    ELSEIF v_total_bayar > 0 THEN
        UPDATE tagihan 
        SET status_tagihan = 'Dibayar Sebagian' 
        WHERE id = NEW.tagihan_id;
    END IF;
END//

-- Trigger untuk update status kamar saat penghuni masuk
CREATE TRIGGER trg_update_status_kamar_masuk
AFTER UPDATE ON penghuni
FOR EACH ROW
BEGIN
    IF NEW.kamar_id IS NOT NULL AND NEW.status_penghuni = 'Aktif' THEN
        UPDATE kamar 
        SET status_kamar = 'Terisi' 
        WHERE id = NEW.kamar_id;
    END IF;
    
    IF OLD.kamar_id IS NOT NULL AND OLD.kamar_id != NEW.kamar_id THEN
        UPDATE kamar 
        SET status_kamar = 'Tersedia' 
        WHERE id = OLD.kamar_id
        AND NOT EXISTS (
            SELECT 1 FROM penghuni 
            WHERE kamar_id = OLD.kamar_id 
            AND status_penghuni = 'Aktif'
        );
    END IF;
END//

DELIMITER ;

-- =============================================
-- INDEXES UNTUK OPTIMASI QUERY
-- =============================================

-- Index untuk pencarian tagihan
CREATE INDEX idx_tagihan_penghuni_periode ON tagihan(penghuni_id, periode_tahun, periode_bulan);

-- Index untuk pencarian order laundry
CREATE INDEX idx_order_laundry_penghuni ON order_laundry(penghuni_id);
CREATE INDEX idx_order_laundry_tanggal ON order_laundry(tanggal_terima);

-- Index untuk notifikasi
CREATE INDEX idx_notifikasi_user_unread ON notifikasi(user_id, is_read);

-- =============================================
-- GRANT PERMISSIONS (Optional - sesuaikan dengan kebutuhan)
-- =============================================

-- Create user untuk aplikasi
-- CREATE USER 'domoskost_app'@'localhost' IDENTIFIED BY 'D0m0sK0st2024!';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON domos_kost.* TO 'domoskost_app'@'localhost';
-- GRANT EXECUTE ON domos_kost.* TO 'domoskost_app'@'localhost';

-- =============================================
-- DATA SUMMARY
-- =============================================
-- Total Users: 26 (1 Admin, 1 Koordinator, 1 Pengawas, 2 Petugas Laundry, 2 Petugas Kebersihan, 2 Petugas Keamanan, 17 Penghuni)
-- Total Kamar: 24 (17 terisi, 6 tersedia, 1 maintenance)
-- Total Penghuni Aktif: 17
-- Total Tagihan Desember 2024: 17 (10 Lunas, 1 Dibayar Sebagian, 6 Belum Dibayar)
-- Total Order Laundry: 10 (5 Selesai, 5 Dalam Proses)
-- =============================================
