-- =====================================================
-- DATABASE DOMOS KOST FIXED 2025
-- Struktur Sederhana Tanpa Foreign Key Conflict
-- =====================================================

-- Drop database if exists and create new
DROP DATABASE IF EXISTS domos_kost;
CREATE DATABASE domos_kost;
USE domos_kost;

-- =====================================================
-- 1. USERS TABLE (Simplified)
-- =====================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    no_telp VARCHAR(20),
    role VARCHAR(50) NOT NULL,
    foto_profil VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert ALL USERS (Staff + Penghuni)
INSERT INTO users (username, email, password, nama_lengkap, no_telp, role) VALUES
-- PEMILIK
('pelita', 'pelita.ginting@domoskost.com', 'admin123', 'Pelita Ginting', '081234567890', 'pemilik'),
-- KOORDINATOR
('jhon', 'jhon.sembiring@domoskost.com', 'staff123', 'Jhon Sembiring', '081234567891', 'koordinator'),
-- PENGAWAS KOST
('dedi', 'dedi.wirawan@domoskost.com', 'staff123', 'Dedi Wirawan', '081234567892', 'pengawas_kost'),
-- PETUGAS LAUNDRY
('diana', 'diana@domoskost.com', 'laundry123', 'Diana', '081234567893', 'petugas_laundry'),
('desri', 'desri@domoskost.com', 'laundry123', 'Desri', '081234567894', 'petugas_laundry'),
-- PETUGAS KEBERSIHAN
('kristiani', 'kristiani@domoskost.com', 'kebersihan123', 'Kristiani', '081234567895', 'petugas_kebersihan'),
('rika', 'rika.ginting@domoskost.com', 'kebersihan123', 'Rika br Ginting', '081234567896', 'petugas_kebersihan'),
-- PETUGAS KEAMANAN
('arman', 'arman.pane@domoskost.com', 'keamanan123', 'Arman Pane', '081234567897', 'petugas_keamanan'),
('satria', 'satria.barus@domoskost.com', 'keamanan123', 'Satria Barus', '081234567898', 'petugas_keamanan'),
-- PENGHUNI (17 orang sesuai gambar struktur)
('thika', 'thika@student.com', 'penghuni123', 'Thika', '082111000001', 'penghuni'),
('togi', 'togi@student.com', 'penghuni123', 'Togi', '082111000002', 'penghuni'),
('beto', 'beto@student.com', 'penghuni123', 'Beto', '082111000003', 'penghuni'),
('christine', 'christine@student.com', 'penghuni123', 'Christine', '082111000004', 'penghuni'),
('desy', 'desy@student.com', 'penghuni123', 'Desy', '082111000005', 'penghuni'),
('elya', 'elya@student.com', 'penghuni123', 'Elya', '082111000006', 'penghuni'),
('kristiany', 'kristiany@student.com', 'penghuni123', 'Kristiany', '082111000007', 'penghuni'),
('naca', 'naca@student.com', 'penghuni123', 'Naca', '082111000008', 'penghuni'),
('riki', 'riki.ananda@student.com', 'penghuni123', 'Riki Ananda', '082111000009', 'penghuni'),
('rivaldo', 'rivaldo@student.com', 'penghuni123', 'Rivaldo', '082111000010', 'penghuni'),
('theresia', 'theresia@student.com', 'penghuni123', 'Theresia', '082111000011', 'penghuni'),
('john', 'john@student.com', 'penghuni123', 'John', '082111000012', 'penghuni'),
('joy', 'joy@student.com', 'penghuni123', 'Joy', '082111000013', 'penghuni'),
('avin', 'avin@student.com', 'penghuni123', 'Avin', '082111000014', 'penghuni'),
('aryandi', 'aryandi@student.com', 'penghuni123', 'Aryandi', '082111000015', 'penghuni'),
('agri', 'agri@student.com', 'penghuni123', 'Agri', '082111000016', 'penghuni'),
('josef', 'josef@student.com', 'penghuni123', 'Josef', '082111000017', 'penghuni');

-- =====================================================
-- 2. KAMAR TABLE
-- =====================================================
CREATE TABLE kamar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_kamar VARCHAR(20) NOT NULL UNIQUE,
    lantai INT NOT NULL,
    tipe_kamar VARCHAR(20) NOT NULL,
    tarif_bulanan DECIMAL(10,2) NOT NULL,
    fasilitas TEXT,
    status_kamar VARCHAR(20) DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert 24 kamar (2 lantai, 12 kamar per lantai)
INSERT INTO kamar (kode_kamar, lantai, tipe_kamar, tarif_bulanan, fasilitas, status_kamar) VALUES
-- Lantai 1 (12 kamar)
('K101', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K102', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K103', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K104', 1, 'VIP', 1200000.00, 'Tempat tidur, lemari, meja belajar, AC, kulkas mini, WiFi', 'Terisi'),
('K105', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K106', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K107', 1, 'Double', 950000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Terisi'),
('K108', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K109', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K110', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K111', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),
('K112', 1, 'Single', 800000.00, 'Tempat tidur, lemari, meja belajar, kipas angin, WiFi', 'Terisi'),

-- Lantai 2 (12 kamar)
('K201', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Terisi'),
('K202', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Terisi'),
('K203', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Terisi'),
('K204', 2, 'VIP', 1300000.00, 'Tempat tidur, lemari, meja belajar, AC, kulkas mini, TV, WiFi', 'Terisi'),
('K205', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Terisi'),
('K206', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Tersedia'),
('K207', 2, 'Double', 1000000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Tersedia'),
('K208', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Tersedia'),
('K209', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Tersedia'),
('K210', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Maintenance'),
('K211', 2, 'Single', 850000.00, 'Tempat tidur, lemari, meja belajar, AC, WiFi', 'Tersedia'),
('K212', 2, 'VIP', 1300000.00, 'Tempat tidur, lemari, meja belajar, AC, kulkas mini, TV, WiFi', 'Tersedia');

-- =====================================================
-- 3. PENGHUNI TABLE
-- =====================================================
CREATE TABLE penghuni (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    kamar_id INT,
    no_ktp VARCHAR(20) UNIQUE,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin VARCHAR(1) NOT NULL,
    alamat_asal TEXT,
    pekerjaan_pendidikan VARCHAR(100),
    kontak_darurat VARCHAR(100),
    no_telp_darurat VARCHAR(20),
    tanggal_masuk DATE NOT NULL,
    tanggal_keluar DATE NULL,
    status_penghuni VARCHAR(20) DEFAULT 'Aktif',
    deposit DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert penghuni sesuai dengan users penghuni (ID 10-26)
INSERT INTO penghuni (user_id, kamar_id, no_ktp, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat_asal, pekerjaan_pendidikan, kontak_darurat, no_telp_darurat, tanggal_masuk, deposit) VALUES
(10, 1, '1271234567890001', 'Medan', '2003-05-15', 'P', 'Jl. Sei Wampu No. 10, Medan', 'Mahasiswa USU', 'Bapak Thika', '081234500001', '2024-08-01', 800000.00),
(11, 2, '1271234567890002', 'Medan', '2002-12-20', 'L', 'Jl. Gajah Mada No. 25, Medan', 'Mahasiswa UNIMED', 'Ibu Togi', '081234500002', '2024-08-01', 800000.00),
(12, 3, '1271234567890003', 'Binjai', '2003-03-10', 'L', 'Jl. Binjai Km 8, Binjai', 'Mahasiswa USU', 'Bapak Beto', '081234500003', '2024-08-15', 800000.00),
(13, 4, '1271234567890004', 'Medan', '2002-07-25', 'P', 'Jl. Imam Bonjol No. 15, Medan', 'Mahasiswa UMA', 'Ibu Christine', '081234500004', '2024-08-15', 1200000.00),
(14, 5, '1271234567890005', 'Deli Serdang', '2003-11-08', 'P', 'Jl. Raya Lubuk Pakam, Deli Serdang', 'Mahasiswa USU', 'Bapak Desy', '081234500005', '2024-09-01', 800000.00),
(15, 6, '1271234567890006', 'Medan', '2003-01-18', 'P', 'Jl. Karya Jaya No. 30, Medan', 'Mahasiswa UINSU', 'Ibu Elya', '081234500006', '2024-09-01', 800000.00),
(16, 7, '1271234567890007', 'Medan', '2002-09-12', 'P', 'Jl. Veteran No. 45, Medan', 'Mahasiswa USU', 'Bapak Kristiany', '081234500007', '2024-09-15', 950000.00),
(17, 8, '1271234567890008', 'Medan', '2003-04-22', 'P', 'Jl. Mongonsidi No. 12, Medan', 'Mahasiswa UNIMED', 'Ibu Naca', '081234500008', '2024-09-15', 800000.00),
(18, 9, '1271234567890009', 'Medan', '2002-06-30', 'L', 'Jl. Pancing No. 88, Medan', 'Mahasiswa USU', 'Bapak Riki', '081234500009', '2024-10-01', 800000.00),
(19, 10, '1271234567890010', 'Tebing Tinggi', '2003-02-14', 'L', 'Jl. Tebing Tinggi Raya No. 5', 'Mahasiswa UINSU', 'Ibu Rivaldo', '081234500010', '2024-10-01', 800000.00),
(20, 11, '1271234567890011', 'Medan', '2002-10-05', 'P', 'Jl. Marelan No. 20, Medan', 'Mahasiswa UMA', 'Bapak Theresia', '081234500011', '2024-10-15', 800000.00),
(21, 12, '1271234567890012', 'Medan', '2003-08-17', 'L', 'Jl. Ampera No. 33, Medan', 'Mahasiswa USU', 'Ibu John', '081234500012', '2024-10-15', 800000.00),
(22, 13, '1271234567890013', 'Medan', '2002-12-03', 'L', 'Jl. Sisingamangaraja No. 77, Medan', 'Mahasiswa UNIMED', 'Bapak Joy', '081234500013', '2024-11-01', 850000.00),
(23, 14, '1271234567890014', 'Medan', '2003-07-28', 'L', 'Jl. Gatot Subroto No. 55, Medan', 'Mahasiswa USU', 'Ibu Avin', '081234500014', '2024-11-01', 850000.00),
(24, 15, '1271234567890015', 'Pematang Siantar', '2002-11-19', 'L', 'Jl. Siantar No. 8, P. Siantar', 'Mahasiswa UINSU', 'Bapak Aryandi', '081234500015', '2024-11-15', 850000.00),
(25, 16, '1271234567890016', 'Medan', '2003-05-07', 'L', 'Jl. Bromo No. 22, Medan', 'Mahasiswa UMA', 'Ibu Agri', '081234500016', '2024-11-15', 1300000.00),
(26, 17, '1271234567890017', 'Medan', '2002-03-24', 'L', 'Jl. Flamboyan No. 66, Medan', 'Mahasiswa USU', 'Bapak Josef', '081234500017', '2024-12-01', 850000.00);

-- =====================================================
-- 4. JENIS LAYANAN LAUNDRY TABLE
-- =====================================================
CREATE TABLE jenis_layanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_layanan VARCHAR(50) NOT NULL,
    harga_per_kg DECIMAL(8,2) NOT NULL,
    estimasi_hari INT NOT NULL,
    deskripsi TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO jenis_layanan (nama_layanan, harga_per_kg, estimasi_hari, deskripsi) VALUES
('Cuci Kering', 8000.00, 2, 'Cuci dan kering saja'),
('Cuci Setrika', 12000.00, 3, 'Cuci, kering, dan setrika rapi'),
('Cuci Sepatu', 15000.00, 2, 'Cuci sepatu khusus'),
('Express 1 Hari', 20000.00, 1, 'Layanan cepat selesai 1 hari'),
('Bed Cover', 25000.00, 3, 'Cuci bed cover, sprei, dan selimut');

-- =====================================================
-- 5. ORDER LAUNDRY TABLE
-- =====================================================
CREATE TABLE order_laundry (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_order VARCHAR(20) NOT NULL UNIQUE,
    penghuni_id INT NOT NULL,
    petugas_id INT,
    jenis_layanan_id INT NOT NULL,
    berat_kg DECIMAL(5,2) NOT NULL,
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal_terima DATE NOT NULL,
    tanggal_estimasi DATE NOT NULL,
    tanggal_selesai DATE NULL,
    status_order VARCHAR(20) DEFAULT 'Diterima',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert beberapa order laundry untuk simulasi
INSERT INTO order_laundry (kode_order, penghuni_id, petugas_id, jenis_layanan_id, berat_kg, total_harga, tanggal_terima, tanggal_estimasi, status_order, catatan) VALUES
('LND250120001', 1, 4, 2, 3.5, 42000.00, '2025-01-20', '2025-01-23', 'Dicuci', 'Pakaian sehari-hari'),
('LND250120002', 3, 5, 1, 2.0, 16000.00, '2025-01-20', '2025-01-22', 'Siap Diambil', NULL),
('LND250119001', 5, 4, 4, 1.5, 30000.00, '2025-01-19', '2025-01-20', 'Selesai', 'Express order'),
('LND250119002', 8, 5, 2, 4.0, 48000.00, '2025-01-19', '2025-01-22', 'Dikeringkan', 'Termasuk jaket tebal'),
('LND250118001', 2, 4, 3, 1.0, 15000.00, '2025-01-18', '2025-01-20', 'Selesai', 'Sepatu olahraga');

-- =====================================================
-- 6. TAGIHAN TABLE
-- =====================================================
CREATE TABLE tagihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_tagihan VARCHAR(20) NOT NULL UNIQUE,
    penghuni_id INT NOT NULL,
    periode_bulan INT NOT NULL,
    periode_tahun INT NOT NULL,
    total_tagihan DECIMAL(10,2) NOT NULL,
    tanggal_terbit DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    status_tagihan VARCHAR(20) DEFAULT 'Belum Bayar',
    denda DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert tagihan untuk bulan Januari 2025
INSERT INTO tagihan (kode_tagihan, penghuni_id, periode_bulan, periode_tahun, total_tagihan, tanggal_terbit, tanggal_jatuh_tempo, status_tagihan) VALUES
('TGH250101001', 1, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101002', 2, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101003', 3, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101004', 4, 1, 2025, 1200000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101005', 5, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Belum Bayar'),
('TGH250101006', 6, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Belum Bayar'),
('TGH250101007', 7, 1, 2025, 950000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101008', 8, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Belum Bayar'),
('TGH250101009', 9, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101010', 10, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Belum Bayar'),
('TGH250101011', 11, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101012', 12, 1, 2025, 800000.00, '2025-01-01', '2025-01-10', 'Belum Bayar'),
('TGH250101013', 13, 1, 2025, 850000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101014', 14, 1, 2025, 850000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101015', 15, 1, 2025, 850000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101016', 16, 1, 2025, 1300000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar'),
('TGH250101017', 17, 1, 2025, 850000.00, '2025-01-01', '2025-01-10', 'Sudah Bayar');

-- =====================================================
-- 7. DETAIL TAGIHAN TABLE
-- =====================================================
CREATE TABLE detail_tagihan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tagihan_id INT NOT NULL,
    jenis_tagihan VARCHAR(50) NOT NULL,
    deskripsi VARCHAR(255) NOT NULL,
    jumlah INT DEFAULT 1,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert detail tagihan untuk tagihan yang ada
INSERT INTO detail_tagihan (tagihan_id, jenis_tagihan, deskripsi, jumlah, harga_satuan, subtotal) VALUES
-- Thika (K101)
(1, 'Sewa Kost', 'Sewa Kamar K101 - Januari 2025', 1, 800000.00, 800000.00),
-- Togi (K102)
(2, 'Sewa Kost', 'Sewa Kamar K102 - Januari 2025', 1, 800000.00, 800000.00),
-- Beto (K103)
(3, 'Sewa Kost', 'Sewa Kamar K103 - Januari 2025', 1, 800000.00, 800000.00),
-- Christine (K104 VIP)
(4, 'Sewa Kost', 'Sewa Kamar K104 VIP - Januari 2025', 1, 1200000.00, 1200000.00),
-- Desy (K105)
(5, 'Sewa Kost', 'Sewa Kamar K105 - Januari 2025', 1, 800000.00, 800000.00),
-- Elya (K106)
(6, 'Sewa Kost', 'Sewa Kamar K106 - Januari 2025', 1, 800000.00, 800000.00),
-- Kristiany (K107 Double)
(7, 'Sewa Kost', 'Sewa Kamar K107 Double - Januari 2025', 1, 950000.00, 950000.00),
-- Naca (K108)
(8, 'Sewa Kost', 'Sewa Kamar K108 - Januari 2025', 1, 800000.00, 800000.00),
-- Riki (K109)
(9, 'Sewa Kost', 'Sewa Kamar K109 - Januari 2025', 1, 800000.00, 800000.00),
-- Rivaldo (K110)
(10, 'Sewa Kost', 'Sewa Kamar K110 - Januari 2025', 1, 800000.00, 800000.00),
-- Theresia (K111)
(11, 'Sewa Kost', 'Sewa Kamar K111 - Januari 2025', 1, 800000.00, 800000.00),
-- John (K112)
(12, 'Sewa Kost', 'Sewa Kamar K112 - Januari 2025', 1, 800000.00, 800000.00),
-- Joy (K201)
(13, 'Sewa Kost', 'Sewa Kamar K201 - Januari 2025', 1, 850000.00, 850000.00),
-- Avin (K202)
(14, 'Sewa Kost', 'Sewa Kamar K202 - Januari 2025', 1, 850000.00, 850000.00),
-- Aryandi (K203)
(15, 'Sewa Kost', 'Sewa Kamar K203 - Januari 2025', 1, 850000.00, 850000.00),
-- Agri (K204 VIP)
(16, 'Sewa Kost', 'Sewa Kamar K204 VIP - Januari 2025', 1, 1300000.00, 1300000.00),
-- Josef (K205)
(17, 'Sewa Kost', 'Sewa Kamar K205 - Januari 2025', 1, 850000.00, 850000.00);

-- =====================================================
-- 8. PEMBAYARAN TABLE
-- =====================================================
CREATE TABLE pembayaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_pembayaran VARCHAR(20) NOT NULL UNIQUE,
    tagihan_id INT NOT NULL,
    tanggal_bayar DATE NOT NULL,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    metode_bayar VARCHAR(50) NOT NULL,
    bukti_bayar VARCHAR(255),
    catatan TEXT,
    status_verifikasi VARCHAR(20) DEFAULT 'Verified',
    verified_by INT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert pembayaran untuk tagihan yang sudah bayar
INSERT INTO pembayaran (kode_pembayaran, tagihan_id, tanggal_bayar, jumlah_bayar, metode_bayar, status_verifikasi, verified_by) VALUES
('PAY250105001', 1, '2025-01-05', 800000.00, 'Transfer Bank', 'Verified', 3),
('PAY250105002', 2, '2025-01-05', 800000.00, 'Tunai', 'Verified', 3),
('PAY250106001', 3, '2025-01-06', 800000.00, 'Transfer Bank', 'Verified', 3),
('PAY250106002', 4, '2025-01-06', 1200000.00, 'Transfer Bank', 'Verified', 3),
('PAY250107001', 7, '2025-01-07', 950000.00, 'E-wallet', 'Verified', 3),
('PAY250108001', 9, '2025-01-08', 800000.00, 'Tunai', 'Verified', 3),
('PAY250109001', 11, '2025-01-09', 800000.00, 'Transfer Bank', 'Verified', 3),
('PAY250110001', 13, '2025-01-10', 850000.00, 'Transfer Bank', 'Verified', 3),
('PAY250110002', 14, '2025-01-10', 850000.00, 'Tunai', 'Verified', 3),
('PAY250111001', 15, '2025-01-11', 850000.00, 'Transfer Bank', 'Verified', 3),
('PAY250111002', 16, '2025-01-11', 1300000.00, 'Transfer Bank', 'Verified', 3),
('PAY250112001', 17, '2025-01-12', 850000.00, 'E-wallet', 'Verified', 3);

-- =====================================================
-- 9. NOTIFIKASI TABLE
-- =====================================================
CREATE TABLE notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    pesan TEXT NOT NULL,
    jenis VARCHAR(20) DEFAULT 'Info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert beberapa notifikasi
INSERT INTO notifikasi (user_id, judul, pesan, jenis) VALUES
-- Untuk penghuni yang belum bayar
(14, 'Tagihan Belum Dibayar', 'Tagihan kost bulan Januari 2025 belum dibayar. Jatuh tempo: 10 Januari 2025', 'Warning'),
(15, 'Tagihan Belum Dibayar', 'Tagihan kost bulan Januari 2025 belum dibayar. Jatuh tempo: 10 Januari 2025', 'Warning'),
(17, 'Tagihan Belum Dibayar', 'Tagihan kost bulan Januari 2025 belum dibayar. Jatuh tempo: 10 Januari 2025', 'Warning'),
(19, 'Tagihan Belum Dibayar', 'Tagihan kost bulan Januari 2025 belum dibayar. Jatuh tempo: 10 Januari 2025', 'Warning'),
(21, 'Tagihan Belum Dibayar', 'Tagihan kost bulan Januari 2025 belum dibayar. Jatuh tempo: 10 Januari 2025', 'Warning'),

-- Untuk laundry siap diambil
(12, 'Laundry Siap Diambil', 'Order laundry LND250120002 sudah selesai dan siap diambil.', 'Success'),

-- Info umum
(10, 'Selamat Datang', 'Selamat datang di Domos Kost Group! Terima kasih telah memilih kami.', 'Info'),
(11, 'Jadwal Pembersihan', 'Pembersihan kamar akan dilakukan setiap hari Sabtu. Mohon kerjasamanya.', 'Info');

-- =====================================================
-- 10. LARAVEL SANCTUM TABLES
-- =====================================================
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
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);

-- =====================================================
-- 11. INDEXES FOR PERFORMANCE
-- =====================================================
CREATE INDEX idx_penghuni_status ON penghuni(status_penghuni);
CREATE INDEX idx_kamar_status ON kamar(status_kamar);
CREATE INDEX idx_tagihan_status ON tagihan(status_tagihan);
CREATE INDEX idx_tagihan_periode ON tagihan(periode_bulan, periode_tahun);
CREATE INDEX idx_order_status ON order_laundry(status_order);
CREATE INDEX idx_notifikasi_user ON notifikasi(user_id, is_read);
CREATE INDEX idx_users_role ON users(role);

-- =====================================================
-- DATABASE SETUP COMPLETE! ✅
-- =====================================================
-- ✅ NO FOREIGN KEY CONSTRAINTS (Avoid MySQL version conflicts)
-- ✅ All VARCHAR instead of ENUM (Better compatibility)
-- ✅ Simplified structure for stability
-- ✅ Real data sesuai struktur organisasi
-- ✅ 26 users total (9 staff + 17 penghuni)
-- ✅ 24 kamar dengan occupancy realistic
-- ✅ Tagihan & pembayaran January 2025
-- ✅ Order laundry dengan status bervariasi
-- =====================================================
