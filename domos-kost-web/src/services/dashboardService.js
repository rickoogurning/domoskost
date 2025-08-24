import api from './api'

export const dashboardService = {
  // Get admin dashboard data
  getAdminDashboard: async () => {
    try {
      const response = await api.get('/dashboard/admin')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data dashboard admin',
      }
    }
  },

  // Get penghuni dashboard data
  getPenghuniDashboard: async () => {
    try {
      const response = await api.get('/dashboard/penghuni')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data dashboard penghuni',
      }
    }
  },

  // Get laundry dashboard data
  getLaundryDashboard: async () => {
    try {
      const response = await api.get('/dashboard/laundry')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data dashboard laundry',
      }
    }
  },
}

export const penghuniService = {
  // Get all penghuni
  getAll: async (params = {}) => {
    try {
      const response = await api.get('/penghuni', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data penghuni',
      }
    }
  },

  // Get penghuni by ID
  getById: async (id) => {
    try {
      const response = await api.get(`/penghuni/${id}`)
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil detail penghuni',
      }
    }
  },

  // Create penghuni
  create: async (penghuniData) => {
    try {
      const response = await api.post('/penghuni', penghuniData)
      
      if (response.data.success) {
        return { success: true, data: response.data.data, message: response.data.message }
      }
      
      return { success: false, message: response.data.message, errors: response.data.errors }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal menambah penghuni',
        errors: error.response?.data?.errors || null,
      }
    }
  },

  // Update penghuni
  update: async (id, penghuniData) => {
    try {
      const response = await api.put(`/penghuni/${id}`, penghuniData)
      
      if (response.data.success) {
        return { success: true, data: response.data.data, message: response.data.message }
      }
      
      return { success: false, message: response.data.message, errors: response.data.errors }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal memperbarui penghuni',
        errors: error.response?.data?.errors || null,
      }
    }
  },

  // Delete penghuni
  delete: async (id) => {
    try {
      const response = await api.delete(`/penghuni/${id}`)
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal menghapus penghuni',
      }
    }
  },

  // Activate penghuni
  activate: async (id) => {
    try {
      const response = await api.post(`/penghuni/${id}/activate`)
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengaktifkan penghuni',
      }
    }
  },

  // Deactivate penghuni
  deactivate: async (id) => {
    try {
      const response = await api.post(`/penghuni/${id}/deactivate`)
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal menonaktifkan penghuni',
      }
    }
  },
}

export const kamarService = {
  // Get all kamar
  getAll: async (params = {}) => {
    try {
      const response = await api.get('/kamar', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data kamar',
      }
    }
  },

  // Get kamar status summary
  getStatusSummary: async () => {
    try {
      const response = await api.get('/kamar/status/summary')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil ringkasan status kamar',
      }
    }
  },
}

export const tagihan = {
  // Get my tagihan (penghuni)
  getMyTagihan: async (params = {}) => {
    try {
      const response = await api.get('/my/tagihan', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data tagihan',
      }
    }
  },

  // Get my tagihan detail (penghuni)
  getMyTagihanDetail: async (id) => {
    try {
      const response = await api.get(`/my/tagihan/${id}`)
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil detail tagihan',
      }
    }
  },
}

export const pembayaran = {
  // Create payment (penghuni)
  createPayment: async (paymentData) => {
    try {
      const response = await api.post('/my/pembayaran', paymentData)
      
      if (response.data.success) {
        return { success: true, data: response.data.data, message: response.data.message }
      }
      
      return { success: false, message: response.data.message, errors: response.data.errors }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengunggah pembayaran',
        errors: error.response?.data?.errors || null,
      }
    }
  },

  // Get my payment history (penghuni)
  getMyPembayaran: async (params = {}) => {
    try {
      const response = await api.get('/my/pembayaran', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil riwayat pembayaran',
      }
    }
  },
}

export const laundryService = {
  // Get my laundry orders (penghuni)
  getMyLaundry: async (params = {}) => {
    try {
      const response = await api.get('/my/laundry', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data laundry',
      }
    }
  },

  // Create laundry order (penghuni)
  createOrder: async (orderData) => {
    try {
      const response = await api.post('/my/laundry', orderData)
      
      if (response.data.success) {
        return { success: true, data: response.data.data, message: response.data.message }
      }
      
      return { success: false, message: response.data.message, errors: response.data.errors }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal membuat order laundry',
        errors: error.response?.data?.errors || null,
      }
    }
  },

  // Get jenis layanan
  getJenisLayanan: async () => {
    try {
      const response = await api.get('/jenis-layanan')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil jenis layanan',
      }
    }
  },

  // Get all laundry orders (staff)
  getAll: async (params = {}) => {
    try {
      const response = await api.get('/laundry', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil data order laundry',
      }
    }
  },

  // Update laundry status
  updateStatus: async (id, statusData) => {
    try {
      const response = await api.post(`/laundry/${id}/update-status`, statusData)
      
      if (response.data.success) {
        return { success: true, data: response.data.data, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal memperbarui status',
      }
    }
  },
}

export const notifikasiService = {
  // Get notifications
  getNotifications: async (params = {}) => {
    try {
      const response = await api.get('/notifikasi', { params })
      
      if (response.data.success) {
        return { success: true, data: response.data.data, pagination: response.data.pagination }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil notifikasi',
      }
    }
  },

  // Get unread count
  getUnreadCount: async () => {
    try {
      const response = await api.get('/notifikasi/unread-count')
      
      if (response.data.success) {
        return { success: true, data: response.data.data }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil jumlah notifikasi',
      }
    }
  },

  // Mark as read
  markAsRead: async (id) => {
    try {
      const response = await api.post(`/notifikasi/${id}/mark-read`)
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal menandai notifikasi',
      }
    }
  },

  // Mark all as read
  markAllAsRead: async () => {
    try {
      const response = await api.post('/notifikasi/mark-all-read')
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal menandai semua notifikasi',
      }
    }
  },
}
