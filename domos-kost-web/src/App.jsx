import { Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import { AuthProvider } from './contexts/AuthContext'

// Layouts
import PublicLayout from './layouts/PublicLayout'
import DashboardLayout from './layouts/DashboardLayout'

// Pages
import LandingPage from './pages/LandingPage'
import LoginPage from './pages/LoginPage'
import AdminDashboard from './pages/admin/AdminDashboard'
import DataPenghuni from './pages/admin/DataPenghuni'
import DataKamar from './pages/admin/DataKamar'
import TagihanPembayaran from './pages/admin/TagihanPembayaran'
import PenghuniDashboard from './pages/penghuni/PenghuniDashboard'
import LaundryDashboard from './pages/laundry/LaundryDashboard'

// Protected Route Component
import ProtectedRoute from './components/ProtectedRoute'
import DebugAuth from './components/DebugAuth'

function App() {
  return (
    <AuthProvider>
      <Toaster 
        position="top-right"
        toastOptions={{
          duration: 4000,
          style: {
            background: '#363636',
            color: '#fff',
          },
          success: {
            duration: 3000,
            iconTheme: {
              primary: '#10b981',
              secondary: '#fff',
            },
          },
          error: {
            duration: 4000,
            iconTheme: {
              primary: '#ef4444',
              secondary: '#fff',
            },
          },
        }}
      />
      <DebugAuth />
      <Routes>
        {/* Public Routes */}
        <Route element={<PublicLayout />}>
          <Route path="/" element={<LandingPage />} />
          <Route path="/login" element={<LoginPage />} />
        </Route>

        {/* Protected Routes - Admin */}
        <Route element={
          <ProtectedRoute allowedRoles={['pemilik', 'koordinator', 'pengawas_kost', 'petugas_kebersihan', 'petugas_keamanan']}>
            <DashboardLayout />
          </ProtectedRoute>
        }>
          <Route path="/admin" element={<AdminDashboard />} />
          <Route path="/admin/penghuni" element={<DataPenghuni />} />
          <Route path="/admin/kamar" element={<DataKamar />} />
          <Route path="/admin/tagihan" element={<TagihanPembayaran />} />
          <Route path="/admin/laundry" element={<div className="p-6"><h1 className="text-2xl font-bold">Order Laundry</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/admin/laporan" element={<div className="p-6"><h1 className="text-2xl font-bold">Laporan Keuangan</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/admin/notifikasi" element={<div className="p-6"><h1 className="text-2xl font-bold">Notifikasi</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/admin/pengaturan" element={<div className="p-6"><h1 className="text-2xl font-bold">Pengaturan</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
        </Route>

        {/* Protected Routes - Penghuni */}
        <Route element={
          <ProtectedRoute allowedRoles={['penghuni']}>
            <DashboardLayout />
          </ProtectedRoute>
        }>
          <Route path="/penghuni" element={<PenghuniDashboard />} />
          <Route path="/penghuni/tagihan" element={<div className="p-6"><h1 className="text-2xl font-bold">Tagihan Saya</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/penghuni/laundry" element={<div className="p-6"><h1 className="text-2xl font-bold">Laundry Saya</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/penghuni/riwayat" element={<div className="p-6"><h1 className="text-2xl font-bold">Riwayat Pembayaran</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/penghuni/profil" element={<div className="p-6"><h1 className="text-2xl font-bold">Profil Saya</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
        </Route>

        {/* Protected Routes - Petugas Laundry */}
        <Route element={
          <ProtectedRoute allowedRoles={['petugas_laundry']}>
            <DashboardLayout />
          </ProtectedRoute>
        }>
          <Route path="/laundry" element={<LaundryDashboard />} />
          <Route path="/laundry/order-baru" element={<div className="p-6"><h1 className="text-2xl font-bold">Order Baru</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/laundry/proses" element={<div className="p-6"><h1 className="text-2xl font-bold">Dalam Proses</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
          <Route path="/laundry/selesai" element={<div className="p-6"><h1 className="text-2xl font-bold">Riwayat Selesai</h1><p className="text-gray-600">Halaman ini sedang dalam pengembangan.</p></div>} />
        </Route>

        {/* Catch all - redirect to home */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </AuthProvider>
  )
}

export default App
