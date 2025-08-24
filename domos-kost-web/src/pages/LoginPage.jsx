import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { Eye, EyeOff, LogIn, Home, Loader2 } from 'lucide-react'
import toast from 'react-hot-toast'

const LoginPage = () => {
  const [formData, setFormData] = useState({
    username: '',
    password: ''
  })
  const [showPassword, setShowPassword] = useState(false)
  const [loading, setLoading] = useState(false)
  
  const { login } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  
  const from = location.state?.from?.pathname || '/'

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!formData.username || !formData.password) {
      toast.error('Username dan password harus diisi')
      return
    }

    setLoading(true)
    const result = await login(formData)
    setLoading(false)

    if (result.success) {
      // Login berhasil, redirect manual berdasarkan role
      const roleRedirects = {
        'pemilik': '/admin',
        'koordinator': '/admin', 
        'pengawas_kost': '/admin',
        'petugas_laundry': '/laundry',
        'petugas_kebersihan': '/admin',
        'petugas_keamanan': '/admin',
        'penghuni': '/penghuni'
      }
      
      const redirectPath = roleRedirects[result.user?.role] || '/admin'
      navigate(redirectPath, { replace: true })
    }
  }

  // Demo credentials info
  const demoCredentials = [
    { role: 'Pemilik', username: 'pelita', password: 'admin123' },
    { role: 'Koordinator', username: 'jhon', password: 'staff123' },
    { role: 'Petugas Laundry', username: 'diana', password: 'laundry123' },
    { role: 'Penghuni', username: 'thika', password: 'penghuni123' }
  ]

  return (
    <div className="min-h-screen flex">
      {/* Left Side - Login Form */}
      <div className="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-white">
        <div className="max-w-md w-full space-y-8">
          {/* Logo */}
          <div className="text-center">
            <Link to="/" className="inline-flex items-center justify-center space-x-3 mb-8">
              <div className="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
                <Home className="w-7 h-7 text-white" />
              </div>
              <div className="text-left">
                <h1 className="text-2xl font-heading font-bold text-gray-900">Domos Kost</h1>
                <p className="text-sm text-gray-500">Sistem Informasi Kost & Laundry</p>
              </div>
            </Link>
            
            <h2 className="text-3xl font-heading font-bold text-gray-900">
              Selamat Datang Kembali
            </h2>
            <p className="mt-2 text-gray-600">
              Silakan login untuk mengakses dashboard Anda
            </p>
          </div>

          {/* Login Form */}
          <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
            <div className="space-y-4">
              <div>
                <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-2">
                  Username
                </label>
                <input
                  id="username"
                  name="username"
                  type="text"
                  autoComplete="username"
                  required
                  value={formData.username}
                  onChange={handleChange}
                  className="input-field"
                  placeholder="Masukkan username"
                />
              </div>
              
              <div>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700 mb-2">
                  Password
                </label>
                <div className="relative">
                  <input
                    id="password"
                    name="password"
                    type={showPassword ? 'text' : 'password'}
                    autoComplete="current-password"
                    required
                    value={formData.password}
                    onChange={handleChange}
                    className="input-field pr-10"
                    placeholder="Masukkan password"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    {showPassword ? (
                      <EyeOff className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                    ) : (
                      <Eye className="h-5 w-5 text-gray-400 hover:text-gray-600" />
                    )}
                  </button>
                </div>
              </div>
            </div>

            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <input
                  id="remember-me"
                  name="remember-me"
                  type="checkbox"
                  className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label htmlFor="remember-me" className="ml-2 block text-sm text-gray-700">
                  Ingat saya
                </label>
              </div>

              <div className="text-sm">
                <a href="#" className="font-medium text-primary-600 hover:text-primary-500">
                  Lupa password?
                </a>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full btn-primary flex items-center justify-center"
            >
              {loading ? (
                <>
                  <Loader2 className="w-5 h-5 mr-2 animate-spin" />
                  Sedang masuk...
                </>
              ) : (
                <>
                  <LogIn className="w-5 h-5 mr-2" />
                  Masuk
                </>
              )}
            </button>
          </form>

          {/* Demo Credentials */}
          <div className="mt-6 p-4 bg-blue-50 rounded-lg">
            <p className="text-sm font-medium text-blue-900 mb-2">Demo Credentials:</p>
            <div className="space-y-1">
              {demoCredentials.map((cred, index) => (
                <div key={index} className="text-xs text-blue-700">
                  <span className="font-medium">{cred.role}:</span> {cred.username} / {cred.password}
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Right Side - Image/Graphics */}
      <div className="hidden lg:block lg:w-1/2 relative">
        <div className="absolute inset-0 bg-gradient-to-br from-primary-600 to-primary-800">
          <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]" />
          <div className="absolute top-20 right-20 w-72 h-72 bg-primary-500 rounded-full filter blur-3xl opacity-30 animate-pulse" />
          <div className="absolute bottom-20 left-20 w-96 h-96 bg-primary-400 rounded-full filter blur-3xl opacity-30 animate-pulse animation-delay-2000" />
        </div>
        
        <div className="relative h-full flex items-center justify-center p-12">
          <div className="text-white text-center max-w-lg">
            <h3 className="text-4xl font-heading font-bold mb-6">
              Kelola Kost dengan Mudah
            </h3>
            <p className="text-xl mb-8 text-primary-100">
              Sistem informasi terintegrasi untuk pengelolaan pembayaran kost dan tracking laundry real-time
            </p>
            
            <div className="grid grid-cols-2 gap-6 text-left">
              <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <div className="text-3xl font-bold mb-1">24/7</div>
                <div className="text-sm text-primary-200">Akses Sistem</div>
              </div>
              <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <div className="text-3xl font-bold mb-1">100%</div>
                <div className="text-sm text-primary-200">Digital</div>
              </div>
              <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <div className="text-3xl font-bold mb-1">Real-time</div>
                <div className="text-sm text-primary-200">Tracking</div>
              </div>
              <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                <div className="text-3xl font-bold mb-1">Aman</div>
                <div className="text-sm text-primary-200">Terpercaya</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default LoginPage
