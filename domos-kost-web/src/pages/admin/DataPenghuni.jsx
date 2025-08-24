import React, { useState, useEffect } from 'react'
import { 
  Users, 
  Plus, 
  Search, 
  Filter, 
  MoreVertical,
  Edit,
  Trash2,
  UserCheck,
  UserX,
  Home,
  Phone,
  Calendar,
  CreditCard
} from 'lucide-react'
import LoadingSpinner from '../../components/LoadingSpinner'

const DataPenghuni = () => {
  const [penghuni, setPenghuni] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filterStatus, setFilterStatus] = useState('all')

  // Mock data - in real app, fetch from API
  useEffect(() => {
    const mockPenghuni = [
      {
        id: 1,
        nama: 'Thika',
        email: 'thika@student.com',
        no_telp: '082111000001',
        kamar: 'K101',
        status: 'Aktif',
        tanggal_masuk: '2024-08-01',
        tagihan_terakhir: 'Lunas',
        foto: null
      },
      {
        id: 2,
        nama: 'Togi',
        email: 'togi@student.com',
        no_telp: '082111000002',
        kamar: 'K102',
        status: 'Aktif',
        tanggal_masuk: '2024-08-01',
        tagihan_terakhir: 'Lunas',
        foto: null
      },
      {
        id: 3,
        nama: 'Beto',
        email: 'beto@student.com',
        no_telp: '082111000003',
        kamar: 'K103',
        status: 'Aktif',
        tanggal_masuk: '2024-08-15',
        tagihan_terakhir: 'Belum Bayar',
        foto: null
      },
      {
        id: 4,
        nama: 'Christine',
        email: 'christine@student.com',
        no_telp: '082111000004',
        kamar: 'K104 (VIP)',
        status: 'Aktif',
        tanggal_masuk: '2024-08-15',
        tagihan_terakhir: 'Lunas',
        foto: null
      },
      {
        id: 5,
        nama: 'Desy',
        email: 'desy@student.com',
        no_telp: '082111000005',
        kamar: 'K105',
        status: 'Aktif',
        tanggal_masuk: '2024-09-01',
        tagihan_terakhir: 'Belum Bayar',
        foto: null
      }
    ]

    setTimeout(() => {
      setPenghuni(mockPenghuni)
      setLoading(false)
    }, 1000)
  }, [])

  const filteredPenghuni = penghuni.filter(p => {
    const matchesSearch = p.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         p.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         p.kamar.toLowerCase().includes(searchTerm.toLowerCase())
    
    if (filterStatus === 'all') return matchesSearch
    return matchesSearch && p.status === filterStatus
  })

  const getStatusBadge = (status) => {
    const colors = {
      'Aktif': 'bg-green-100 text-green-800',
      'Tidak Aktif': 'bg-red-100 text-red-800',
      'Pindah': 'bg-gray-100 text-gray-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
  }

  const getTagihanBadge = (tagihan) => {
    const colors = {
      'Lunas': 'bg-green-100 text-green-800',
      'Belum Bayar': 'bg-red-100 text-red-800',
      'Terlambat': 'bg-yellow-100 text-yellow-800'
    }
    return colors[tagihan] || 'bg-gray-100 text-gray-800'
  }

  if (loading) return <LoadingSpinner />

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Data Penghuni</h1>
          <p className="text-gray-600">Kelola data penghuni kost</p>
        </div>
        <button className="btn-primary flex items-center gap-2">
          <Plus className="w-5 h-5" />
          Tambah Penghuni
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
                placeholder="Cari penghuni, email, atau kamar..."
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
            <option value="Aktif">Aktif</option>
            <option value="Tidak Aktif">Tidak Aktif</option>
            <option value="Pindah">Pindah</option>
          </select>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Penghuni</p>
              <p className="text-2xl font-bold text-gray-900">{penghuni.length}</p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Users className="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Status Aktif</p>
              <p className="text-2xl font-bold text-green-600">
                {penghuni.filter(p => p.status === 'Aktif').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <UserCheck className="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Belum Bayar</p>
              <p className="text-2xl font-bold text-red-600">
                {penghuni.filter(p => p.tagihan_terakhir === 'Belum Bayar').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
              <CreditCard className="w-6 h-6 text-red-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Kamar Terisi</p>
              <p className="text-2xl font-bold text-purple-600">
                {penghuni.filter(p => p.status === 'Aktif').length}/24
              </p>
            </div>
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <Home className="w-6 h-6 text-purple-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Data Table */}
      <div className="bg-white rounded-lg shadow-md overflow-hidden">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Penghuni
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kontak
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kamar
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tagihan
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal Masuk
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredPenghuni.map((person) => (
                <tr key={person.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <Users className="w-5 h-5 text-gray-600" />
                      </div>
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{person.nama}</div>
                        <div className="text-sm text-gray-500">{person.email}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center text-sm text-gray-900">
                      <Phone className="w-4 h-4 mr-2 text-gray-400" />
                      {person.no_telp}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center text-sm text-gray-900">
                      <Home className="w-4 h-4 mr-2 text-gray-400" />
                      {person.kamar}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadge(person.status)}`}>
                      {person.status}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTagihanBadge(person.tagihan_terakhir)}`}>
                      {person.tagihan_terakhir}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center text-sm text-gray-900">
                      <Calendar className="w-4 h-4 mr-2 text-gray-400" />
                      {new Date(person.tanggal_masuk).toLocaleDateString('id-ID')}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div className="flex items-center justify-end space-x-2">
                      <button className="text-blue-600 hover:text-blue-900">
                        <Edit className="w-4 h-4" />
                      </button>
                      <button className="text-red-600 hover:text-red-900">
                        <Trash2 className="w-4 h-4" />
                      </button>
                      <button className="text-gray-400 hover:text-gray-600">
                        <MoreVertical className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {filteredPenghuni.length === 0 && (
          <div className="text-center py-12">
            <Users className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">Tidak ada penghuni</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm ? 'Tidak ada hasil yang cocok dengan pencarian.' : 'Mulai dengan menambah penghuni baru.'}
            </p>
          </div>
        )}
      </div>
    </div>
  )
}

export default DataPenghuni
