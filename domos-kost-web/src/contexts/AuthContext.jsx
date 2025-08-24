import { createContext, useContext, useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'
import authService from '../services/authService'

const AuthContext = createContext({})

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const navigate = useNavigate()

  useEffect(() => {
    checkAuth()
  }, [])

  const checkAuth = async () => {
    console.log('🔍 AuthContext: Starting checkAuth...')
    try {
      const token = localStorage.getItem('token')
      const userData = localStorage.getItem('user')
      
      console.log('🔍 AuthContext: Found in localStorage:', { 
        hasToken: !!token, 
        hasUserData: !!userData 
      })
      
      if (token && userData) {
        try {
          // Try to parse user data from localStorage first
          const parsedUser = JSON.parse(userData)
          console.log('✅ AuthContext: User loaded from localStorage:', parsedUser)
          setUser(parsedUser)
          
          // Optional: verify with API in background (don't block UI)
          authService.getProfile().then(result => {
            if (!result.success) {
              // If API says token is invalid, logout
              console.warn('⚠️ Token expired or invalid, logging out')
              localStorage.removeItem('token')
              localStorage.removeItem('user')
              setUser(null)
            } else {
              console.log('✅ Token verified with API')
            }
          }).catch(error => {
            // API error, but keep user logged in if localStorage data exists
            console.warn('⚠️ API check failed, but keeping user logged in from localStorage:', error.message)
          })
          
        } catch (parseError) {
          // If userData parsing fails, clear storage
          console.error('Failed to parse user data:', parseError)
          localStorage.removeItem('token')
          localStorage.removeItem('user')
        }
      } else {
        console.log('❌ AuthContext: No token/userData found')
      }
    } catch (error) {
      console.error('Auth check failed:', error)
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    } finally {
      console.log('🔍 AuthContext: checkAuth completed, setting loading to false')
      setLoading(false)
    }
  }

  const login = async (credentials) => {
    console.log('🔐 AuthContext: Starting login process...', { username: credentials.username })
    try {
      const result = await authService.login(credentials)
      console.log('🔐 AuthContext: Login result:', { success: result.success, user: result.user?.nama_lengkap })
      
      if (result.success) {
        const { user, token } = result
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(user))
        setUser(user)
        
        console.log('✅ AuthContext: Login successful, user set:', user)
        
        // Navigation dilakukan di LoginPage, tidak perlu di sini
        // untuk menghindari konflik navigation
        
        toast.success(`Selamat datang, ${user.nama_lengkap}!`)
        return { success: true, user }
      } else {
        console.log('❌ AuthContext: Login failed:', result.message)
        toast.error(result.message || 'Login gagal')
        return { success: false, error: result.message }
      }
    } catch (error) {
      console.error('🚨 AuthContext: Login error:', error)
      let message = 'Login gagal. Periksa kembali username dan password.'
      
      // Better error handling for network issues
      if (error.message.includes('fetch')) {
        message = 'Server tidak dapat dijangkau. Pastikan backend berjalan.'
      } else if (error.response?.status === 401) {
        message = 'Username atau password salah.'
      } else if (error.response?.data?.message) {
        message = error.response.data.message
      }
      
      toast.error(message)
      return { success: false, error: message }
    }
  }

  const logout = () => {
    localStorage.removeItem('token')
    setUser(null)
    navigate('/')
    toast.success('Berhasil logout')
  }

  const updateProfile = async (data) => {
    try {
      const updatedUser = await authService.updateProfile(data)
      setUser(updatedUser)
      toast.success('Profil berhasil diperbarui')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Gagal memperbarui profil'
      toast.error(message)
      return { success: false, error: message }
    }
  }

  const value = {
    user,
    loading,
    login,
    logout,
    updateProfile,
    checkAuth
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}
