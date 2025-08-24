# Domos Kost Web - Frontend

Frontend aplikasi untuk Sistem Informasi Pengelolaan Pembayaran Kost & Tracking Laundry Domos Kost Group.

## 🚀 Fitur Aplikasi

### Landing Page
- Hero section dengan tagline yang menarik
- Feature highlights dan testimoni
- Design responsive dan modern

### Multi-Role Dashboard
- **Admin Dashboard**: Statistik lengkap, chart pendapatan, aktivitas terbaru
- **Penghuni Dashboard**: Info kamar, tagihan, tracking laundry real-time
- **Petugas Laundry Dashboard**: Kelola order, update status, performance metrics

### Sistem Autentikasi
- Login multi-role (Admin, Penghuni, Petugas Laundry)
- Protected routes berdasarkan role
- Token-based authentication dengan refresh

### UI/UX Modern
- Design minimalis dan clean
- Color scheme: Blue (#1E3A8A, #3B82F6), White, Gray
- Typography: Poppins/Montserrat (headings), Inter/Open Sans (body)
- Responsive mobile-first design
- Loading states dan animasi

## 🛠️ Teknologi

- **Framework**: React 18 + Vite
- **Styling**: Tailwind CSS
- **Routing**: React Router DOM
- **Charts**: Recharts
- **Icons**: Lucide React
- **HTTP Client**: Axios
- **State Management**: Context API

## 📋 Prasyarat

- Node.js 16.0 atau lebih tinggi
- npm atau yarn
- Backend API (Laravel) berjalan di `http://localhost:8000`

## 🔧 Instalasi

1. Clone repository
```bash
git clone https://github.com/yourusername/domos-kost-web.git
cd domos-kost-web
```

2. Install dependencies
```bash
npm install
```

3. Setup environment variables
```bash
# Buat file .env di root project
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Domos Kost Group
```

4. Run development server
```bash
npm run dev
```

Aplikasi akan berjalan di `http://localhost:3000`

## 🏗️ Build Production

```bash
npm run build
```

File production akan berada di folder `dist/`

## 📁 Struktur Project

```
domos-kost-web/
├── public/                 # Static assets
├── src/
│   ├── components/         # Reusable components
│   │   ├── LoadingSpinner.jsx
│   │   ├── ProtectedRoute.jsx
│   │   ├── Sidebar.jsx
│   │   ├── DashboardNavbar.jsx
│   │   ├── PublicNavbar.jsx
│   │   └── Footer.jsx
│   ├── contexts/           # React contexts
│   │   └── AuthContext.jsx
│   ├── layouts/            # Layout components
│   │   ├── PublicLayout.jsx
│   │   └── DashboardLayout.jsx
│   ├── pages/              # Page components
│   │   ├── LandingPage.jsx
│   │   ├── LoginPage.jsx
│   │   ├── admin/
│   │   │   └── AdminDashboard.jsx
│   │   ├── penghuni/
│   │   │   └── PenghuniDashboard.jsx
│   │   └── laundry/
│   │       └── LaundryDashboard.jsx
│   ├── services/           # API services
│   │   ├── api.js
│   │   ├── authService.js
│   │   └── dashboardService.js
│   ├── App.jsx             # Root component
│   ├── main.jsx            # Entry point
│   └── index.css           # Global styles
├── package.json
├── tailwind.config.js      # Tailwind configuration
├── vite.config.js          # Vite configuration
└── README.md
```

## 🔐 Authentication Flow

1. User mengakses halaman login
2. Input username/email dan password
3. Frontend mengirim request ke `/api/auth/login`
4. Backend mengembalikan token dan data user
5. Token disimpan di localStorage
6. User diarahkan ke dashboard sesuai role
7. Setiap API request menyertakan token di header

## 📊 API Integration

### Base URL
```javascript
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
```

### Authentication Headers
```javascript
headers: {
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
}
```

### Main Endpoints
- `POST /auth/login` - Login
- `POST /auth/logout` - Logout
- `GET /auth/profile` - Get user profile
- `GET /dashboard/admin` - Admin dashboard data
- `GET /dashboard/penghuni` - Penghuni dashboard data
- `GET /dashboard/laundry` - Laundry dashboard data

## 🎨 Design System

### Colors
- **Primary Blue**: #1E3A8A (Biru tua)
- **Secondary Blue**: #3B82F6 (Biru muda)
- **Success**: #10B981 (Hijau)
- **Warning**: #F59E0B (Kuning)
- **Error**: #EF4444 (Merah)
- **Neutral**: #F3F4F6 (Abu-abu)

### Typography
- **Headings**: Poppins/Montserrat
- **Body**: Inter/Open Sans
- **Monospace**: Courier New (untuk angka)

### Components
- Buttons dengan hover states
- Cards dengan shadow dan border radius
- Forms dengan validation styling
- Tables dengan zebra striping
- Loading spinners
- Progress bars untuk laundry tracking

## 🔒 Role-based Access

### Admin Role
- Access ke AdminDashboard
- Kelola penghuni, kamar, tagihan
- Lihat laporan lengkap
- Generate tagihan bulanan

### Penghuni Role
- Access ke PenghuniDashboard
- Lihat tagihan dan riwayat pembayaran
- Upload bukti pembayaran
- Track status laundry real-time
- Request laundry order

### Petugas Laundry Role
- Access ke LaundryDashboard
- Kelola order laundry
- Update status cucian
- Lihat performance metrics

## 📱 Responsive Design

- **Mobile First**: Design dimulai dari mobile
- **Breakpoints**:
  - `sm`: 640px
  - `md`: 768px
  - `lg`: 1024px
  - `xl`: 1280px
- **Grid System**: 12 kolom dengan gap yang konsisten
- **Navigation**: Sidebar collapsible di mobile

## 🧪 Testing

```bash
# Run tests
npm run test

# Run tests with coverage
npm run test:coverage
```

## 📈 Performance

- **Lazy Loading**: Route-based code splitting
- **Image Optimization**: WebP format untuk gambar
- **Bundling**: Vite untuk build yang cepat
- **Caching**: Service Worker untuk cache assets
- **Minification**: CSS dan JS yang diminified

## 🚀 Deployment

### Vercel (Recommended)
1. Connect repository ke Vercel
2. Set environment variables di dashboard
3. Deploy otomatis dari git push

### Manual Deployment
```bash
npm run build
# Upload folder dist/ ke web server
```

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 Lisensi

Copyright © 2025 Domos Kost Group. All rights reserved.

---

**Developed by**: Mustika Sari Sinulingga (210810065)  
**Program Studi**: Sistem Informasi  
**Universitas**: Katolik Santo Thomas Medan