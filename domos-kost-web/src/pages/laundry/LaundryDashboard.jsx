import React, { useState, useEffect } from 'react'
import { 
  Package, 
  Clock, 
  CheckCircle, 
  DollarSign,
  TrendingUp,
  Filter,
  Search,
  AlertTriangle,
  Calendar
} from 'lucide-react'
import { dashboardService, laundryService } from '../../services/dashboardService'
import LoadingSpinner from '../../components/LoadingSpinner'

const LaundryDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null)
  const [activeOrders, setActiveOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [filter, setFilter] = useState('all')
  const [searchTerm, setSearchTerm] = useState('')

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      setLoading(true)
      const response = await dashboardService.getLaundryDashboard()
      
      if (response.success) {
        setDashboardData(response.data)
        setActiveOrders(response.data.activeOrders || [])
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

  const updateOrderStatus = async (orderId, newStatus, catatan = '') => {
    try {
      const response = await laundryService.updateStatus(orderId, {
        status_baru: newStatus,
        catatan: catatan
      })

      if (response.success) {
        // Refresh data after successful update
        await loadDashboardData()
        
        // Show success message (you can implement toast notification here)
        console.log('Status berhasil diperbarui:', response.message)
      } else {
        console.error('Gagal memperbarui status:', response.message)
      }
    } catch (error) {
      console.error('Error updating status:', error)
    }
  }

  if (loading) return <LoadingSpinner />

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <AlertTriangle className="mx-auto h-12 w-12 text-red-500 mb-4" />
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

  const { stats } = dashboardData

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
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // Get status color
  const getStatusColor = (status) => {
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

  // Get next status for order
  const getNextStatus = (currentStatus) => {
    const statusFlow = {
      'Diterima': 'Dicuci',
      'Dicuci': 'Dikeringkan',
      'Dikeringkan': 'Disetrika',
      'Disetrika': 'Siap Diambil',
      'Siap Diambil': 'Selesai'
    }
    return statusFlow[currentStatus]
  }

  // Filter orders
  const filteredOrders = activeOrders.filter(order => {
    const matchesFilter = filter === 'all' || order.status_order === filter
    const matchesSearch = searchTerm === '' || 
      order.penghuni.nama_lengkap.toLowerCase().includes(searchTerm.toLowerCase()) ||
      order.penghuni.kamar.toLowerCase().includes(searchTerm.toLowerCase()) ||
      order.kode_order.toLowerCase().includes(searchTerm.toLowerCase())
    
    return matchesFilter && matchesSearch
  })

  const statsCards = [
    {
      title: 'Order Hari Ini',
      value: stats.orderHariIni,
      icon: Package,
      color: 'bg-blue-500'
    },
    {
      title: 'Sedang Diproses',
      value: stats.sedangDiproses,
      icon: Clock,
      color: 'bg-yellow-500'
    },
    {
      title: 'Siap Diambil',
      value: stats.siapDiambil,
      icon: CheckCircle,
      color: 'bg-green-500'
    },
    {
      title: 'Pendapatan Hari Ini',
      value: formatCurrency(stats.pendapatanHariIni),
      icon: DollarSign,
      color: 'bg-emerald-500'
    }
  ]

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard Laundry</h1>
          <p className="text-gray-600">Kelola order laundry dan update status</p>
        </div>
        <button 
          onClick={loadDashboardData}
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2"
        >
          <TrendingUp className="h-4 w-4" />
          Refresh
        </button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statsCards.map((stat, index) => (
          <div key={index} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">{stat.title}</p>
                <p className="text-2xl font-bold text-gray-900">{stat.value}</p>
              </div>
              <div className={`${stat.color} p-3 rounded-full`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Filters and Search */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex flex-col md:flex-row gap-4">
          {/* Search */}
          <div className="flex-1">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <input
                type="text"
                placeholder="Cari berdasarkan nama, kamar, atau kode order..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
          
          {/* Status Filter */}
          <div className="flex items-center gap-2">
            <Filter className="h-4 w-4 text-gray-400" />
            <select
              value={filter}
              onChange={(e) => setFilter(e.target.value)}
              className="border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Semua Status</option>
              <option value="Diterima">Diterima</option>
              <option value="Dicuci">Dicuci</option>
              <option value="Dikeringkan">Dikeringkan</option>
              <option value="Disetrika">Disetrika</option>
              <option value="Siap Diambil">Siap Diambil</option>
            </select>
          </div>
        </div>
      </div>

      {/* Active Orders Table */}
      <div className="bg-white rounded-lg shadow-md">
        <div className="px-6 py-4 border-b border-gray-200">
          <h2 className="text-lg font-semibold text-gray-900">Order Aktif</h2>
          <p className="text-sm text-gray-600">
            Menampilkan {filteredOrders.length} dari {activeOrders.length} order
          </p>
        </div>
        
        {filteredOrders.length === 0 ? (
          <div className="p-8 text-center">
            <Package className="mx-auto h-12 w-12 text-gray-400 mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">Tidak Ada Order</h3>
            <p className="text-gray-500">
              {searchTerm || filter !== 'all' 
                ? 'Tidak ada order yang sesuai dengan filter atau pencarian'
                : 'Belum ada order aktif untuk diproses'
              }
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Order Info
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Penghuni
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Layanan
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Estimasi
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredOrders.map((order) => (
                  <tr key={order.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{order.kode_order}</div>
                        <div className="text-sm text-gray-500">
                          {formatDate(order.tanggal_terima)}
                        </div>
                        {order.is_overdue && (
                          <div className="flex items-center text-xs text-red-600 mt-1">
                            <AlertTriangle className="h-3 w-3 mr-1" />
                            Terlambat
                          </div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">
                          {order.penghuni.nama_lengkap}
                        </div>
                        <div className="text-sm text-gray-500">
                          Kamar {order.penghuni.kamar}
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm text-gray-900">{order.jenis_layanan.nama_layanan}</div>
                        <div className="text-sm text-gray-500">
                          {order.berat_kg} kg • {formatCurrency(order.total_biaya)}
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(order.status_order)}`}>
                        {order.status_order}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div className="flex items-center">
                        <Calendar className="h-4 w-4 mr-1" />
                        {formatDate(order.tanggal_estimasi_selesai)}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      {order.next_status && (
                        <button
                          onClick={() => updateOrderStatus(order.id, order.next_status)}
                          className="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium"
                        >
                          {order.next_status}
                        </button>
                      )}
                      {order.status_order !== 'Selesai' && (
                        <button
                          onClick={() => updateOrderStatus(order.id, 'Dibatalkan', 'Dibatalkan oleh petugas')}
                          className="ml-2 bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-md text-xs font-medium"
                        >
                          Batal
                        </button>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Quick Stats */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Status Distribution</h3>
          <div className="space-y-3">
            {['Diterima', 'Dicuci', 'Dikeringkan', 'Disetrika', 'Siap Diambil'].map(status => {
              const count = activeOrders.filter(order => order.status_order === status).length
              return (
                <div key={status} className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">{status}</span>
                  <span className="text-sm font-semibold text-gray-900">{count}</span>
                </div>
              )
            })}
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Performance</h3>
          <div className="space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-600">Total Order</span>
              <span className="text-sm font-semibold text-gray-900">{activeOrders.length}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-600">Selesai Hari Ini</span>
              <span className="text-sm font-semibold text-green-600">{stats.selesaiHariIni}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-600">Terlambat</span>
              <span className="text-sm font-semibold text-red-600">
                {activeOrders.filter(order => order.is_overdue).length}
              </span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="space-y-3">
            <button className="w-full text-left p-2 hover:bg-gray-50 rounded-md text-sm">
              <Package className="inline h-4 w-4 mr-2 text-blue-600" />
              Tambah Order Baru
            </button>
            <button className="w-full text-left p-2 hover:bg-gray-50 rounded-md text-sm">
              <Clock className="inline h-4 w-4 mr-2 text-yellow-600" />
              Lihat Order Terlambat
            </button>
            <button className="w-full text-left p-2 hover:bg-gray-50 rounded-md text-sm">
              <CheckCircle className="inline h-4 w-4 mr-2 text-green-600" />
              Update Multiple Status
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}

export default LaundryDashboard