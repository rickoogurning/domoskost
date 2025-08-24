import api from './api'

const authService = {
  // Login
  login: async (credentials) => {
    try {
      const response = await api.post('/auth/login', {
        username: credentials.username,
        password: credentials.password,
      })
      
      if (response.data.success) {
        const { user, token } = response.data
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        return { success: true, user, token }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login gagal. Silakan coba lagi.',
      }
    }
  },

  // Logout
  logout: async () => {
    try {
      await api.post('/auth/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  },

  // Get profile
  getProfile: async () => {
    try {
      const response = await api.get('/auth/profile')
      
      if (response.data.success) {
        const user = response.data.user
        localStorage.setItem('user', JSON.stringify(user))
        return { success: true, user }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengambil profil',
      }
    }
  },

  // Update profile
  updateProfile: async (profileData) => {
    try {
      const response = await api.put('/auth/profile', profileData)
      
      if (response.data.success) {
        const user = response.data.data
        localStorage.setItem('user', JSON.stringify(user))
        return { success: true, user }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal memperbarui profil',
      }
    }
  },

  // Change password
  changePassword: async (passwordData) => {
    try {
      const response = await api.put('/auth/change-password', passwordData)
      
      if (response.data.success) {
        return { success: true, message: response.data.message }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal mengubah password',
      }
    }
  },

  // Refresh token
  refreshToken: async () => {
    try {
      const response = await api.post('/auth/refresh')
      
      if (response.data.success) {
        const { token } = response.data.data
        localStorage.setItem('token', token)
        return { success: true, token }
      }
      
      return { success: false, message: response.data.message }
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Gagal refresh token',
      }
    }
  },

  // Check if user is authenticated
  isAuthenticated: () => {
    const token = localStorage.getItem('token')
    const user = localStorage.getItem('user')
    return !!(token && user)
  },

  // Get current user from localStorage
  getCurrentUser: () => {
    try {
      const user = localStorage.getItem('user')
      return user ? JSON.parse(user) : null
    } catch (error) {
      return null
    }
  },

  // Get token from localStorage
  getToken: () => {
    return localStorage.getItem('token')
  },
}

export default authService