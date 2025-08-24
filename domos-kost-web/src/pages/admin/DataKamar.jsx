import React, { useState, useEffect } from 'react'
import { 
  Home, 
  Plus, 
  Search, 
  Filter, 
  MoreVertical,
  Edit,
  Trash2,
  CheckCircle,
  XCircle,
  AlertTriangle,
  DollarSign,
  Users,
  Wifi,
  Wind,
  Tv,
  Coffee
} from 'lucide-react'
import LoadingSpinner from '../../components/LoadingSpinner'

const DataKamar = () => {
  const [kamar, setKamar] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filterStatus, setFilterStatus] = useState('all')
  const [filterTipe, setFilterTipe] = useState('all')

  // Mock data - in real app, fetch from API
  useEffect(() => {
    const mockKamar = [
      {
        id: 1,
        kode_kamar: 'K101',
        lantai: 1,
        tipe_kamar: 'Single',
        tarif_bulanan: 800000,
        status_kamar: 'Terisi',
        penghuni: 'Thika',
        fasilitas: ['WiFi', 'Kipas Angin', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      },
      {
        id: 2,
        kode_kamar: 'K102',
        lantai: 1,
        tipe_kamar: 'Single',
        tarif_bulanan: 800000,
        status_kamar: 'Terisi',
        penghuni: 'Togi',
        fasilitas: ['WiFi', 'Kipas Angin', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      },
      {
        id: 3,
        kode_kamar: 'K103',
        lantai: 1,
        tipe_kamar: 'Single',
        tarif_bulanan: 800000,
        status_kamar: 'Terisi',
        penghuni: 'Beto',
        fasilitas: ['WiFi', 'Kipas Angin', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      },
      {
        id: 4,
        kode_kamar: 'K104',
        lantai: 1,
        tipe_kamar: 'VIP',
        tarif_bulanan: 1200000,
        status_kamar: 'Terisi',
        penghuni: 'Christine',
        fasilitas: ['WiFi', 'AC', 'Lemari', 'Meja Belajar', 'Kulkas Mini', 'TV'],
        luas: '4x5m'
      },
      {
        id: 5,
        kode_kamar: 'K105',
        lantai: 1,
        tipe_kamar: 'Single',
        tarif_bulanan: 800000,
        status_kamar: 'Terisi',
        penghuni: 'Desy',
        fasilitas: ['WiFi', 'Kipas Angin', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      },
      {
        id: 6,
        kode_kamar: 'K207',
        lantai: 2,
        tipe_kamar: 'Double',
        tarif_bulanan: 1000000,
        status_kamar: 'Tersedia',
        penghuni: null,
        fasilitas: ['WiFi', 'AC', 'Lemari', 'Meja Belajar', '2 Tempat Tidur'],
        luas: '4x5m'
      },
      {
        id: 7,
        kode_kamar: 'K208',
        lantai: 2,
        tipe_kamar: 'Single',
        tarif_bulanan: 850000,
        status_kamar: 'Tersedia',
        penghuni: null,
        fasilitas: ['WiFi', 'AC', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      },
      {
        id: 8,
        kode_kamar: 'K210',
        lantai: 2,
        tipe_kamar: 'Single',
        tarif_bulanan: 850000,
        status_kamar: 'Maintenance',
        penghuni: null,
        fasilitas: ['WiFi', 'AC', 'Lemari', 'Meja Belajar'],
        luas: '3x4m'
      }
    ]

    setTimeout(() => {
      setKamar(mockKamar)
      setLoading(false)
    }, 1000)
  }, [])

  const filteredKamar = kamar.filter(k => {
    const matchesSearch = k.kode_kamar.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         k.penghuni?.toLowerCase().includes(searchTerm.toLowerCase())
    
    let matchesStatus = true
    if (filterStatus !== 'all') {
      matchesStatus = k.status_kamar === filterStatus
    }

    let matchesTipe = true
    if (filterTipe !== 'all') {
      matchesTipe = k.tipe_kamar === filterTipe
    }
    
    return matchesSearch && matchesStatus && matchesTipe
  })

  const getStatusBadge = (status) => {
    const styles = {
      'Tersedia': { bg: 'bg-green-100', text: 'text-green-800', icon: CheckCircle },
      'Terisi': { bg: 'bg-blue-100', text: 'text-blue-800', icon: Users },
      'Maintenance': { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: AlertTriangle }
    }
    return styles[status] || styles['Tersedia']
  }

  const getTipeBadge = (tipe) => {
    const colors = {
      'Single': 'bg-gray-100 text-gray-800',
      'Double': 'bg-purple-100 text-purple-800',
      'VIP': 'bg-yellow-100 text-yellow-800'
    }
    return colors[tipe] || 'bg-gray-100 text-gray-800'
  }

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value)
  }

  const getFasilitasIcon = (fasilitas) => {
    const icons = {
      'WiFi': Wifi,
      'AC': Wind,
      'TV': Tv,
      'Kulkas Mini': Coffee
    }
    return icons[fasilitas] || Home
  }

  if (loading) return <LoadingSpinner />

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Data Kamar</h1>
          <p className="text-gray-600">Kelola data kamar kost</p>
        </div>
        <button className="btn-primary flex items-center gap-2">
          <Plus className="w-5 h-5" />
          Tambah Kamar
        </button>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex flex-col sm:flex-row gap-4">
          {/* Search */}
          <div className="flex-1">
            <div className="relative">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="text"
                placeholder="Cari kode kamar atau penghuni..."
                className="input-field pl-10"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
              />
            </div>
          </div>
          
          {/* Status Filter */}
          <select
            className="input-field"
            value={filterStatus}
            onChange={(e) => setFilterStatus(e.target.value)}
          >
            <option value="all">Semua Status</option>
            <option value="Tersedia">Tersedia</option>
            <option value="Terisi">Terisi</option>
            <option value="Maintenance">Maintenance</option>
          </select>

          {/* Tipe Filter */}
          <select
            className="input-field"
            value={filterTipe}
            onChange={(e) => setFilterTipe(e.target.value)}
          >
            <option value="all">Semua Tipe</option>
            <option value="Single">Single</option>
            <option value="Double">Double</option>
            <option value="VIP">VIP</option>
          </select>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Kamar</p>
              <p className="text-2xl font-bold text-gray-900">24</p>
            </div>
            <div className="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
              <Home className="w-6 h-6 text-gray-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Kamar Terisi</p>
              <p className="text-2xl font-bold text-blue-600">
                {kamar.filter(k => k.status_kamar === 'Terisi').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Users className="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Kamar Tersedia</p>
              <p className="text-2xl font-bold text-green-600">
                {kamar.filter(k => k.status_kamar === 'Tersedia').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <CheckCircle className="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Maintenance</p>
              <p className="text-2xl font-bold text-yellow-600">
                {kamar.filter(k => k.status_kamar === 'Maintenance').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <AlertTriangle className="w-6 h-6 text-yellow-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Grid View */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {filteredKamar.map((room) => {
          const statusStyle = getStatusBadge(room.status_kamar)
          const StatusIcon = statusStyle.icon
          
          return (
            <div key={room.id} className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
              <div className="p-6">
                {/* Header */}
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="text-lg font-semibold text-gray-900">{room.kode_kamar}</h3>
                    <p className="text-sm text-gray-500">Lantai {room.lantai} • {room.luas}</p>
                  </div>
                  <div className="flex space-x-1">
                    <button className="text-gray-400 hover:text-gray-600">
                      <Edit className="w-4 h-4" />
                    </button>
                    <button className="text-gray-400 hover:text-gray-600">
                      <MoreVertical className="w-4 h-4" />
                    </button>
                  </div>
                </div>

                {/* Tipe & Status */}
                <div className="flex justify-between items-center mb-4">
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTipeBadge(room.tipe_kamar)}`}>
                    {room.tipe_kamar}
                  </span>
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusStyle.bg} ${statusStyle.text}`}>
                    <StatusIcon className="w-3 h-3 mr-1" />
                    {room.status_kamar}
                  </span>
                </div>

                {/* Penghuni */}
                {room.penghuni ? (
                  <div className="flex items-center mb-4">
                    <Users className="w-4 h-4 text-gray-400 mr-2" />
                    <span className="text-sm text-gray-700">{room.penghuni}</span>
                  </div>
                ) : (
                  <div className="flex items-center mb-4 text-gray-400">
                    <Users className="w-4 h-4 mr-2" />
                    <span className="text-sm">Belum ada penghuni</span>
                  </div>
                )}

                {/* Tarif */}
                <div className="flex items-center mb-4">
                  <DollarSign className="w-4 h-4 text-green-500 mr-2" />
                  <span className="text-sm font-medium text-gray-900">
                    {formatCurrency(room.tarif_bulanan)}/bulan
                  </span>
                </div>

                {/* Fasilitas */}
                <div className="space-y-2">
                  <p className="text-xs font-medium text-gray-500 uppercase tracking-wider">Fasilitas</p>
                  <div className="flex flex-wrap gap-1">
                    {room.fasilitas.slice(0, 4).map((fasilitas, index) => {
                      const Icon = getFasilitasIcon(fasilitas)
                      return (
                        <div key={index} className="flex items-center bg-gray-100 rounded-md px-2 py-1">
                          <Icon className="w-3 h-3 text-gray-600 mr-1" />
                          <span className="text-xs text-gray-700">{fasilitas}</span>
                        </div>
                      )
                    })}
                    {room.fasilitas.length > 4 && (
                      <div className="flex items-center bg-gray-100 rounded-md px-2 py-1">
                        <span className="text-xs text-gray-700">+{room.fasilitas.length - 4}</span>
                      </div>
                    )}
                  </div>
                </div>

                {/* Actions */}
                <div className="mt-4 pt-4 border-t border-gray-200">
                  {room.status_kamar === 'Tersedia' ? (
                    <button className="w-full btn-primary text-sm py-2">
                      Tambah Penghuni
                    </button>
                  ) : room.status_kamar === 'Terisi' ? (
                    <button className="w-full bg-gray-100 text-gray-700 text-sm py-2 px-4 rounded-md hover:bg-gray-200 transition-colors">
                      Lihat Detail
                    </button>
                  ) : (
                    <button className="w-full bg-yellow-100 text-yellow-700 text-sm py-2 px-4 rounded-md hover:bg-yellow-200 transition-colors">
                      Maintenance
                    </button>
                  )}
                </div>
              </div>
            </div>
          )
        })}
      </div>

      {filteredKamar.length === 0 && (
        <div className="bg-white rounded-lg shadow-md p-12 text-center">
          <Home className="mx-auto h-12 w-12 text-gray-400" />
          <h3 className="mt-2 text-sm font-medium text-gray-900">Tidak ada kamar</h3>
          <p className="mt-1 text-sm text-gray-500">
            {searchTerm ? 'Tidak ada hasil yang cocok dengan pencarian.' : 'Mulai dengan menambah kamar baru.'}
          </p>
        </div>
      )}
    </div>
  )
}

export default DataKamar
