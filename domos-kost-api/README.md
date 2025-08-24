# Domos Kost API - Backend System

Backend API untuk Sistem Informasi Pengelolaan Pembayaran Kost & Tracking Laundry Domos Kost Group.

## 🚀 Fitur API

### Authentication
- **POST** `/api/auth/login` - Login pengguna
- **POST** `/api/auth/logout` - Logout pengguna
- **GET** `/api/auth/profile` - Get profil pengguna
- **PUT** `/api/auth/profile` - Update profil pengguna
- **PUT** `/api/auth/change-password` - Ubah password
- **POST** `/api/auth/refresh` - Refresh token

### Dashboard
- **GET** `/api/dashboard/admin` - Dashboard admin dengan statistik
- **GET** `/api/dashboard/penghuni` - Dashboard penghuni
- **GET** `/api/dashboard/laundry` - Dashboard petugas laundry

### Penghuni Management (Admin Only)
- **GET** `/api/penghuni` - List semua penghuni dengan filter & search
- **POST** `/api/penghuni` - Tambah penghuni baru
- **GET** `/api/penghuni/{id}` - Detail penghuni
- **PUT** `/api/penghuni/{id}` - Update penghuni
- **DELETE** `/api/penghuni/{id}` - Hapus/nonaktifkan penghuni
- **POST** `/api/penghuni/{id}/activate` - Aktifkan penghuni
- **POST** `/api/penghuni/{id}/deactivate` - Nonaktifkan penghuni

### Kamar Management (Admin Only)
- **GET** `/api/kamar` - List semua kamar dengan filter
- **POST** `/api/kamar` - Tambah kamar baru
- **GET** `/api/kamar/{id}` - Detail kamar
- **PUT** `/api/kamar/{id}` - Update kamar
- **DELETE** `/api/kamar/{id}` - Hapus kamar
- **GET** `/api/kamar/status/summary` - Ringkasan status kamar

### Tagihan & Pembayaran (Admin Only)
- **GET** `/api/tagihan` - List tagihan
- **POST** `/api/tagihan` - Buat tagihan
- **GET** `/api/tagihan/{id}` - Detail tagihan
- **PUT** `/api/tagihan/{id}` - Update tagihan
- **POST** `/api/tagihan/generate/{bulan}/{tahun}` - Generate tagihan bulanan
- **GET** `/api/tagihan/penghuni/{penghuniId}` - Tagihan per penghuni

- **GET** `/api/pembayaran` - List pembayaran
- **POST** `/api/pembayaran` - Catat pembayaran
- **GET** `/api/pembayaran/{id}` - Detail pembayaran
- **POST** `/api/pembayaran/{id}/verify` - Verifikasi pembayaran
- **POST** `/api/pembayaran/{id}/reject` - Tolak pembayaran

### Laundry Management
- **GET** `/api/laundry` - List order laundry
- **POST** `/api/laundry` - Buat order laundry
- **GET** `/api/laundry/{id}` - Detail order
- **PUT** `/api/laundry/{id}` - Update order
- **POST** `/api/laundry/{id}/update-status` - Update status order
- **GET** `/api/laundry/status/{status}` - Filter by status
- **GET** `/api/jenis-layanan` - List jenis layanan laundry

### Penghuni Self-Service
- **GET** `/api/my/tagihan` - Tagihan penghuni login
- **GET** `/api/my/tagihan/{id}` - Detail tagihan
- **POST** `/api/my/pembayaran` - Upload bukti pembayaran
- **GET** `/api/my/pembayaran` - Riwayat pembayaran
- **GET** `/api/my/laundry` - Riwayat laundry
- **POST** `/api/my/laundry` - Buat order laundry

### Notifikasi
- **GET** `/api/notifikasi` - List notifikasi
- **GET** `/api/notifikasi/unread-count` - Jumlah notifikasi belum dibaca
- **POST** `/api/notifikasi/{id}/mark-read` - Tandai sudah dibaca
- **POST** `/api/notifikasi/mark-all-read` - Tandai semua sudah dibaca
- **DELETE** `/api/notifikasi/{id}` - Hapus notifikasi

### Laporan (Admin Only)
- **GET** `/api/laporan/pendapatan` - Laporan pendapatan
- **GET** `/api/laporan/pendapatan/export` - Export laporan ke PDF
- **GET** `/api/laporan/penghuni` - Laporan penghuni
- **GET** `/api/laporan/laundry` - Laporan laundry

## 🛠️ Teknologi

- **Framework**: Laravel 10
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Documentation**: API Endpoints
- **PDF Export**: DomPDF

## 📋 Prasyarat

- PHP 8.1 atau lebih tinggi
- Composer
- MySQL 5.7 atau lebih tinggi
- Node.js (untuk asset compilation - optional)

## 🔧 Instalasi

1. Clone repository
```bash
git clone https://github.com/yourusername/domos-kost-api.git
cd domos-kost-api
```

2. Install dependencies
```bash
composer install
```

3. Copy environment file
```bash
cp .env.example .env
```

4. Setup database configuration di `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domos_kost
DB_USERNAME=root
DB_PASSWORD=
```

5. Import database
```bash
mysql -u root -p domos_kost < ../domos_kost_database.sql
```

6. Generate application key
```bash
php artisan key:generate
```

7. Run server
```bash
php artisan serve
```

API akan berjalan di `http://localhost:8000`

## 🔐 Authentication

API menggunakan Laravel Sanctum untuk authentication. Setelah login, gunakan token di header:

```
Authorization: Bearer {your-token}
```

### Login Credentials Demo

| Role | Username | Password |
|------|----------|----------|
| Admin | pelita | password123 |
| Penghuni | thika | password123 |
| Petugas Laundry | diana | password123 |

## 📝 API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors (optional)
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success message",
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  }
}
```

## 🚦 Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## 🔒 Role-based Access Control

API menggunakan middleware role untuk mengontrol akses:

- **Admin, Koordinator, Pengawas**: Full access
- **Petugas Laundry**: Laundry management only
- **Penghuni**: Self-service endpoints only

## 📊 Database Schema

Database terdiri dari 12 tabel utama:
- `users` - Data pengguna
- `roles` - Role/jabatan
- `kamar` - Data kamar
- `penghuni` - Data penghuni
- `tagihan` - Tagihan bulanan
- `detail_tagihan` - Detail item tagihan
- `pembayaran` - Transaksi pembayaran
- `order_laundry` - Order laundry
- `jenis_layanan_laundry` - Master layanan
- `status_laundry_log` - Log perubahan status
- `notifikasi` - Notifikasi sistem
- `pengaturan` - Settings aplikasi

## 🧪 Testing

Untuk testing API, gunakan tools seperti:
- Postman
- Insomnia
- curl
- HTTPie

## 📈 Performance

- Menggunakan index database untuk optimasi query
- Pagination untuk data besar
- Lazy loading relationships
- Caching untuk data statis

## 🔧 Configuration

### CORS Settings
CORS dikonfigurasi untuk frontend di `localhost:3000` dan `localhost:5173`

### Sanctum Configuration
Token tidak expire secara default. Bisa dikonfigurasi di `config/sanctum.php`

## 👥 Tim Pengembang

**Mustika Sari Sinulingga**  
NIM: 210810065  
Program Studi Sistem Informasi  
Universitas Katolik Santo Thomas Medan

## 📄 Lisensi

Copyright © 2025 Domos Kost Group. All rights reserved.
