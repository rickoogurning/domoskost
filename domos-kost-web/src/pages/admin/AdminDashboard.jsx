import React, { useState, useEffect } from 'react'
import { 
  Users, 
  Home, 
  DollarSign, 
  TrendingUp, 
  AlertTriangle,
  Package,
  Clock,
  CheckCircle
} from 'lucide-react'
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, LineChart, Line
} from 'recharts'
import { dashboardService } from '../../services/dashboardService'
import LoadingSpinner from '../../components/LoadingSpinner'

const AdminDashboard = () => {
  const [dashboardData, setDashboardData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      setLoading(true)
      const response = await dashboardService.getAdminDashboard()
      
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

  const { stats, charts, recentActivities } = dashboardData

  // Colors for charts
  const COLORS = ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6']

  // Format currency
  const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value)
  }

  // Calculate revenue growth
  const revenueGrowth = stats.pendapatanBulanLalu > 0 
    ? ((stats.pendapatanBulanIni - stats.pendapatanBulanLalu) / stats.pendapatanBulanLalu * 100)
    : 0

  const statsCards = [
    {
      title: 'Total Penghuni',
      value: stats.totalPenghuni,
      icon: Users,
      color: 'bg-blue-500',
      trend: null
    },
    {
      title: 'Kamar Terisi',
      value: `${stats.kamarTerisi}/${stats.totalKamar}`,
      icon: Home,
      color: 'bg-green-500',
      trend: `${Math.round((stats.kamarTerisi / stats.totalKamar) * 100)}% occupancy`
    },
    {
      title: 'Pendapatan Bulan Ini',
      value: formatCurrency(stats.pendapatanBulanIni),
      icon: DollarSign,
      color: 'bg-emerald-500',
      trend: `${revenueGrowth >= 0 ? '+' : ''}${revenueGrowth.toFixed(1)}% dari bulan lalu`
    },
    {
      title: 'Tagihan Belum Bayar',
      value: stats.tagihanBelumBayar,
      icon: AlertTriangle,
      color: 'bg-red-500',
      trend: 'Perlu follow-up'
    }
  ]

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Dashboard Admin</h1>
          <p className="text-gray-600">Ringkasan operasional Domos Kost Group</p>
        </div>
        <button 
          onClick={loadDashboardData}
          className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center gap-2"
        >
          <TrendingUp className="h-4 w-4" />
          Refresh Data
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
                {stat.trend && (
                  <p className="text-sm text-gray-500 mt-1">{stat.trend}</p>
                )}
              </div>
              <div className={`${stat.color} p-3 rounded-full`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue Trend */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Tren Pendapatan (6 Bulan)</h3>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={charts.revenueTrend}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="month" />
              <YAxis tickFormatter={(value) => `${value / 1000000}M`} />
              <Tooltip 
                formatter={(value, name) => [
                  formatCurrency(value), 
                  name === 'sewa' ? 'Sewa' : 'Laundry'
                ]} 
              />
              <Bar dataKey="sewa" fill="#3b82f6" name="sewa" />
              <Bar dataKey="laundry" fill="#10b981" name="laundry" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Room Occupancy */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Status Kamar</h3>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={charts.occupancyData}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={({name, value}) => `${name}: ${value}`}
                outerRadius={80}
                fill="#8884d8"
                dataKey="value"
              >
                {charts.occupancyData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Laundry Status Today */}
      {charts.laundryStatusToday && charts.laundryStatusToday.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Status Laundry Hari Ini</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {charts.laundryStatusToday.map((item, index) => (
              <div key={index} className="text-center p-4 bg-gray-50 rounded-lg">
                <div className="text-2xl font-bold text-blue-600">{item.count}</div>
                <div className="text-sm text-gray-600">{item.status}</div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Recent Activities */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Aktivitas Terbaru</h3>
        <div className="space-y-4">
          {recentActivities && recentActivities.length > 0 ? (
            recentActivities.map((activity, index) => (
              <div key={index} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                <div className={`p-2 rounded-full ${
                  activity.type === 'payment' ? 'bg-green-100' : 'bg-blue-100'
                }`}>
                  {activity.type === 'payment' ? (
                    <DollarSign className="h-4 w-4 text-green-600" />
                  ) : (
                    <Package className="h-4 w-4 text-blue-600" />
                  )}
                </div>
                <div className="flex-1">
                  <p className="text-sm text-gray-900">
                    <span className="font-medium">{activity.user}</span> {activity.action}
                    {activity.amount && (
                      <span className="font-semibold text-green-600 ml-1">
                        {formatCurrency(activity.amount)}
                      </span>
                    )}
                  </p>
                  <p className="text-xs text-gray-500">{activity.time}</p>
                </div>
              </div>
            ))
          ) : (
            <div className="text-center py-8 text-gray-500">
              <Clock className="mx-auto h-8 w-8 mb-2" />
              <p>Belum ada aktivitas terbaru</p>
            </div>
          )}
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <Users className="mx-auto h-6 w-6 text-blue-600 mb-2" />
            <span className="text-sm font-medium">Kelola Penghuni</span>
          </button>
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <Home className="mx-auto h-6 w-6 text-green-600 mb-2" />
            <span className="text-sm font-medium">Kelola Kamar</span>
          </button>
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <DollarSign className="mx-auto h-6 w-6 text-emerald-600 mb-2" />
            <span className="text-sm font-medium">Generate Tagihan</span>
          </button>
          <button className="p-4 text-center border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <TrendingUp className="mx-auto h-6 w-6 text-purple-600 mb-2" />
            <span className="text-sm font-medium">Lihat Laporan</span>
          </button>
        </div>
      </div>
    </div>
  )
}

export default AdminDashboard