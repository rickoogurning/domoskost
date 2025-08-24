import React, { useState, useEffect } from 'react'
import { 
  Home, 
  Calendar, 
  Clock, 
  Package, 
  AlertCircle,
  CheckCircle,
  DollarSign,
  FileText,
  Truck
} from 'lucide-react'
import { dashboardService, tagihan, laundryService } from '../../services/dashboardService'
import LoadingSpinner from '../../components/LoadingSpinner'

const PenghuniDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      setLoading(true)
      const response = await dashboardService.getPenghuniDashboard()
      
      if (response.success) {
        setDashboardData(response.data)
      } else {
        setError(response.message)
      }
    } catch (error) {
      setError('Gagal memuat data dashboard')
      console.error('Dashboard error:', error)
    } finally {
      setLoading(false)
    }
  }

  if (loading) return <LoadingSpinner />

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <AlertCircle className="mx-auto h-12 w-12 text-red-500 mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">Terjadi Kesalahan</h3>
          <p className="text-gray-500 mb-4">{error}</p>
          <button
            onClick={loadDashboardData}
            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
          >
            Coba Lagi
          </button>
        </div>
      </div>
    )
  }

  if (!dashboardData) return null

  const { kamar, tagihan: tagihanData, laundry } = dashboardData

  // Format currency
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value)
  }

  // Format date
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    })
  }

  // Get status color for tagihan
  const getTagihanStatusColor = (status) => {
    switch (status) {
      case 'Lunas': return 'text-green-600 bg-green-100'
      case 'Terlambat': return 'text-red-600 bg-red-100'
      case 'Dibayar Sebagian': return 'text-yellow-600 bg-yellow-100'
      default: return 'text-gray-600 bg-gray-100'
    }
  }

  // Get laundry progress color
  const getLaundryProgressColor = (progress) => {
    if (progress === 100) return 'bg-green-500'
    if (progress >= 80) return 'bg-blue-500'
    if (progress >= 50) return 'bg-yellow-500'
    return 'bg-gray-300'
  }

  // Get laundry status color
  const getLaundryStatusColor = (status) => {
    switch (status) {
      case 'Selesai': return 'text-green-600 bg-green-100'
      case 'Siap Diambil': return 'text-blue-600 bg-blue-100'
      case 'Disetrika': return 'text-purple-600 bg-purple-100'
      case 'Dikeringkan': return 'text-yellow-600 bg-yellow-100'
      case 'Dicuci': return 'text-indigo-600 bg-indigo-100'
      case 'Diterima': return 'text-gray-600 bg-gray-100'
      default: return 'text-gray-600 bg-gray-100'
    }
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard Penghuni</h1>
          <p className="text-gray-600">Selamat datang di dashboard Anda</p>
        </div>
        <button 
          onClick={loadDashboardData}
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2"
        >
          <Clock className="h-4 w-4" />
          Refresh
        </button>
      </div>

      {/* Room Info */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-gray-900">Informasi Kamar</h2>
          <Home className="h-5 w-5 text-blue-600" />
        </div>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p className="text-sm text-gray-500">Kode Kamar</p>
            <p className="text-lg font-semibold text-gray-900">{kamar.kode}</p>
          </div>
          <div>
            <p className="text-sm text-gray-500">Lantai</p>
            <p className="text-lg font-semibold text-gray-900">{kamar.lantai}</p>
          </div>
          <div>
            <p className="text-sm text-gray-500">Tipe</p>
            <p className="text-lg font-semibold text-gray-900">{kamar.tipe}</p>
          </div>
          <div>
            <p className="text-sm text-gray-500">Tarif Bulanan</p>
            <p className="text-lg font-semibold text-blue-600">{formatCurrency(kamar.tarif)}</p>
          </div>
        </div>
        <div className="mt-4 pt-4 border-t border-gray-200">
          <p className="text-sm text-gray-500">Tanggal Masuk</p>
          <p className="text-sm font-medium text-gray-900">{formatDate(kamar.tanggal_masuk)}</p>
        </div>
      </div>

      {/* Current Bill */}
      {tagihanData.bulan_ini && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Tagihan Bulan Ini</h2>
            <FileText className="h-5 w-5 text-blue-600" />
          </div>
          <div className="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
            <div className="flex items-center justify-between mb-2">
              <h3 className="text-lg font-semibold text-blue-900">
                Tagihan {tagihanData.bulan_ini.periode}
              </h3>
              <span className={`px-3 py-1 rounded-full text-sm font-medium ${getTagihanStatusColor(tagihanData.bulan_ini.status)}`}>
                {tagihanData.bulan_ini.status}
              </span>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
              <div>
                <p className="text-blue-700">Total Tagihan</p>
                <p className="font-semibold text-blue-900">{formatCurrency(tagihanData.bulan_ini.total)}</p>
              </div>
              <div>
                <p className="text-blue-700">Jatuh Tempo</p>
                <p className="font-semibold text-blue-900">{formatDate(tagihanData.bulan_ini.jatuh_tempo)}</p>
              </div>
              {tagihanData.bulan_ini.denda > 0 && (
                <div>
                  <p className="text-red-600">Denda</p>
                  <p className="font-semibold text-red-800">{formatCurrency(tagihanData.bulan_ini.denda)}</p>
                </div>
              )}
            </div>
            {tagihanData.bulan_ini.status !== 'Lunas' && (
              <div className="mt-4">
                <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                  <DollarSign className="h-4 w-4 inline mr-1" />
                  Bayar Sekarang
                </button>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Active Laundry */}
      {laundry.active && laundry.active.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Laundry Aktif</h2>
            <Package className="h-5 w-5 text-blue-600" />
          </div>
          <div className="space-y-4">
            {laundry.active.map((order, index) => (
              <div key={index} className="border border-gray-200 rounded-lg p-4">
                <div className="flex justify-between items-start mb-3">
                  <div>
                    <h3 className="font-semibold text-gray-900">Order #{order.id}</h3>
                    <p className="text-sm text-gray-600">{order.jenis} • {order.berat} kg</p>
                    <p className="text-sm text-gray-500">
                      Diterima: {formatDate(order.tanggal_order)}
                    </p>
                  </div>
                  <div className="text-right">
                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${getLaundryStatusColor(order.status)}`}>
                      {order.status}
                    </span>
                    <p className="text-sm font-semibold text-gray-900 mt-1">
                      {formatCurrency(order.biaya)}
                    </p>
                  </div>
                </div>
                
                {/* Progress Bar */}
                <div className="mb-3">
                  <div className="flex justify-between items-center mb-1">
                    <span className="text-sm text-gray-600">Progress</span>
                    <span className="text-sm font-medium text-gray-900">{order.progress}%</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className={`h-2 rounded-full transition-all duration-300 ${getLaundryProgressColor(order.progress)}`}
                      style={{ width: `${order.progress}%` }}
                    ></div>
                  </div>
                </div>

                {order.estimasi_selesai && (
                  <div className="flex items-center text-sm text-gray-600">
                    <Clock className="h-4 w-4 mr-1" />
                    Estimasi selesai: {formatDate(order.estimasi_selesai)}
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Payment History */}
      {tagihanData.history && tagihanData.history.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Riwayat Pembayaran</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Periode
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tanggal Bayar
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {tagihanData.history.slice(0, 5).map((bill, index) => (
                  <tr key={index} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {bill.periode}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {formatCurrency(bill.total)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getTagihanStatusColor(bill.status)}`}>
                        {bill.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {bill.tanggal_bayar ? formatDate(bill.tanggal_bayar) : '-'}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Laundry History */}
      {laundry.history && laundry.history.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Riwayat Laundry</h2>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Order ID
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Jenis
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Berat
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Biaya
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tanggal
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {laundry.history.slice(0, 5).map((order, index) => (
                  <tr key={index} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {order.id}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {order.jenis}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {order.berat} kg
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {formatCurrency(order.biaya)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatDate(order.tanggal_selesai)}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h2>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <DollarSign className="mx-auto h-6 w-6 text-green-600 mb-2" />
            <span className="text-sm font-medium">Upload Pembayaran</span>
          </button>
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <Package className="mx-auto h-6 w-6 text-blue-600 mb-2" />
            <span className="text-sm font-medium">Order Laundry</span>
          </button>
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <FileText className="mx-auto h-6 w-6 text-purple-600 mb-2" />
            <span className="text-sm font-medium">Lihat Tagihan</span>
          </button>
        </div>
      </div>
    </div>
  )
}

export default PenghuniDashboard