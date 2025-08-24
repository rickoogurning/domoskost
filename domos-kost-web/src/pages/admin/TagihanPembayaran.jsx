import React, { useState, useEffect } from 'react'
import { 
  CreditCard, 
  Plus, 
  Search, 
  Filter, 
  MoreVertical,
  Eye,
  Download,
  Send,
  CheckCircle,
  AlertTriangle,
  XCircle,
  Calendar,
  User,
  DollarSign,
  FileText
} from 'lucide-react'
import LoadingSpinner from '../../components/LoadingSpinner'

const TagihanPembayaran = () => {
  const [tagihan, setTagihan] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filterStatus, setFilterStatus] = useState('all')
  const [filterBulan, setFilterBulan] = useState('2025-01')

  // Mock data - in real app, fetch from API
  useEffect(() => {
    const mockTagihan = [
      {
        id: 1,
        kode_tagihan: 'TGH250101001',
        penghuni: 'Thika',
        kamar: 'K101',
        periode: 'Januari 2025',
        total_tagihan: 800000,
        tanggal_terbit: '2025-01-01',
        tanggal_jatuh_tempo: '2025-01-10',
        status_tagihan: 'Sudah Bayar',
        tanggal_bayar: '2025-01-05',
        metode_bayar: 'Transfer Bank'
      },
      {
        id: 2,
        kode_tagihan: 'TGH250101002',
        penghuni: 'Togi',
        kamar: 'K102',
        periode: 'Januari 2025',
        total_tagihan: 800000,
        tanggal_terbit: '2025-01-01',
        tanggal_jatuh_tempo: '2025-01-10',
        status_tagihan: 'Sudah Bayar',
        tanggal_bayar: '2025-01-05',
        metode_bayar: 'Tunai'
      },
      {
        id: 3,
        kode_tagihan: 'TGH250101003',
        penghuni: 'Beto',
        kamar: 'K103',
        periode: 'Januari 2025',
        total_tagihan: 800000,
        tanggal_terbit: '2025-01-01',
        tanggal_jatuh_tempo: '2025-01-10',
        status_tagihan: 'Belum Bayar',
        tanggal_bayar: null,
        metode_bayar: null
      },
      {
        id: 4,
        kode_tagihan: 'TGH250101004',
        penghuni: 'Christine',
        kamar: 'K104',
        periode: 'Januari 2025',
        total_tagihan: 1200000,
        tanggal_terbit: '2025-01-01',
        tanggal_jatuh_tempo: '2025-01-10',
        status_tagihan: 'Sudah Bayar',
        tanggal_bayar: '2025-01-06',
        metode_bayar: 'Transfer Bank'
      },
      {
        id: 5,
        kode_tagihan: 'TGH250101005',
        penghuni: 'Desy',
        kamar: 'K105',
        periode: 'Januari 2025',
        total_tagihan: 800000,
        tanggal_terbit: '2025-01-01',
        tanggal_jatuh_tempo: '2025-01-10',
        status_tagihan: 'Terlambat',
        tanggal_bayar: null,
        metode_bayar: null,
        denda: 50000
      }
    ]

    setTimeout(() => {
      setTagihan(mockTagihan)
      setLoading(false)
    }, 1000)
  }, [])

  const filteredTagihan = tagihan.filter(t => {
    const matchesSearch = t.penghuni.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         t.kamar.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         t.kode_tagihan.toLowerCase().includes(searchTerm.toLowerCase())
    
    if (filterStatus === 'all') return matchesSearch
    return matchesSearch && t.status_tagihan === filterStatus
  })

  const getStatusBadge = (status) => {
    const styles = {
      'Sudah Bayar': { bg: 'bg-green-100', text: 'text-green-800', icon: CheckCircle },
      'Belum Bayar': { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: AlertTriangle },
      'Terlambat': { bg: 'bg-red-100', text: 'text-red-800', icon: XCircle }
    }
    return styles[status] || styles['Belum Bayar']
  }

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(value)
  }

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }

  if (loading) return <LoadingSpinner />

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Tagihan & Pembayaran</h1>
          <p className="text-gray-600">Kelola tagihan dan pembayaran penghuni</p>
        </div>
        <div className="flex space-x-3">
          <button className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <Download className="w-5 h-5" />
            Export
          </button>
          <button className="btn-primary flex items-center gap-2">
            <Plus className="w-5 h-5" />
            Buat Tagihan
          </button>
        </div>
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
                placeholder="Cari penghuni, kamar, atau kode tagihan..."
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
            <option value="Sudah Bayar">Sudah Bayar</option>
            <option value="Belum Bayar">Belum Bayar</option>
            <option value="Terlambat">Terlambat</option>
          </select>

          {/* Periode Filter */}
          <input
            type="month"
            className="input-field"
            value={filterBulan}
            onChange={(e) => setFilterBulan(e.target.value)}
          />
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Tagihan</p>
              <p className="text-2xl font-bold text-gray-900">{tagihan.length}</p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <FileText className="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Sudah Bayar</p>
              <p className="text-2xl font-bold text-green-600">
                {tagihan.filter(t => t.status_tagihan === 'Sudah Bayar').length}
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
              <p className="text-sm font-medium text-gray-600">Belum Bayar</p>
              <p className="text-2xl font-bold text-yellow-600">
                {tagihan.filter(t => t.status_tagihan === 'Belum Bayar').length}
              </p>
            </div>
            <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <AlertTriangle className="w-6 h-6 text-yellow-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Total Pendapatan</p>
              <p className="text-2xl font-bold text-emerald-600">
                {formatCurrency(
                  tagihan
                    .filter(t => t.status_tagihan === 'Sudah Bayar')
                    .reduce((sum, t) => sum + t.total_tagihan, 0)
                )}
              </p>
            </div>
            <div className="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
              <DollarSign className="w-6 h-6 text-emerald-600" />
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
                  Tagihan
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Penghuni
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Periode
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Total
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jatuh Tempo
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pembayaran
                </th>
                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredTagihan.map((bill) => {
                const statusStyle = getStatusBadge(bill.status_tagihan)
                const StatusIcon = statusStyle.icon
                const isOverdue = new Date(bill.tanggal_jatuh_tempo) < new Date() && bill.status_tagihan !== 'Sudah Bayar'
                
                return (
                  <tr key={bill.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{bill.kode_tagihan}</div>
                        <div className="text-sm text-gray-500">Terbit: {formatDate(bill.tanggal_terbit)}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                          <User className="w-4 h-4 text-gray-600" />
                        </div>
                        <div className="ml-3">
                          <div className="text-sm font-medium text-gray-900">{bill.penghuni}</div>
                          <div className="text-sm text-gray-500">{bill.kamar}</div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{bill.periode}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        {formatCurrency(bill.total_tagihan)}
                      </div>
                      {bill.denda && (
                        <div className="text-sm text-red-600">
                          + {formatCurrency(bill.denda)} (denda)
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className={`text-sm ${isOverdue ? 'text-red-600 font-medium' : 'text-gray-900'}`}>
                        {formatDate(bill.tanggal_jatuh_tempo)}
                      </div>
                      {isOverdue && (
                        <div className="text-xs text-red-500">Terlambat</div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusStyle.bg} ${statusStyle.text}`}>
                        <StatusIcon className="w-3 h-3 mr-1" />
                        {bill.status_tagihan}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      {bill.tanggal_bayar ? (
                        <div>
                          <div className="text-sm text-gray-900">{formatDate(bill.tanggal_bayar)}</div>
                          <div className="text-sm text-gray-500">{bill.metode_bayar}</div>
                        </div>
                      ) : (
                        <div className="text-sm text-gray-400">-</div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-end space-x-2">
                        <button className="text-blue-600 hover:text-blue-900">
                          <Eye className="w-4 h-4" />
                        </button>
                        <button className="text-green-600 hover:text-green-900">
                          <Download className="w-4 h-4" />
                        </button>
                        {bill.status_tagihan === 'Belum Bayar' && (
                          <button className="text-orange-600 hover:text-orange-900">
                            <Send className="w-4 h-4" />
                          </button>
                        )}
                        <button className="text-gray-400 hover:text-gray-600">
                          <MoreVertical className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                )
              })}
            </tbody>
          </table>
        </div>

        {filteredTagihan.length === 0 && (
          <div className="text-center py-12">
            <CreditCard className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">Tidak ada tagihan</h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm ? 'Tidak ada hasil yang cocok dengan pencarian.' : 'Mulai dengan membuat tagihan baru.'}
            </p>
          </div>
        )}
      </div>
    </div>
  )
}

export default TagihanPembayaran
