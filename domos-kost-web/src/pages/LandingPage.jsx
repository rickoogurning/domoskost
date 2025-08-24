import { useEffect } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { 
  Home, Shield, Wifi, Droplets, Clock, CreditCard, 
  CheckCircle, ArrowRight, Users, Star, TrendingUp,
  Sparkles, Zap, Heart
} from 'lucide-react'

const LandingPage = () => {
  const { user, loading } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()

  // Auto redirect if user is already logged in
  useEffect(() => {
    console.log('🔄 LandingPage useEffect triggered:', { 
      loading, 
      user: user?.nama_lengkap, 
      role: user?.role, 
      pathname: location.pathname 
    })
    
    // Force redirect if user is logged in and on root path
    if (!loading && user && user.role && location.pathname === '/') {
      console.log('🎯 FORCE REDIRECT: User logged in, redirecting from landing page')
      
      const roleRedirects = {
        'pemilik': '/admin',
        'koordinator': '/admin', 
        'pengawas_kost': '/admin',
        'petugas_laundry': '/laundry',
        'petugas_kebersihan': '/admin',
        'petugas_keamanan': '/admin',
        'penghuni': '/penghuni'
      }
      
      const redirectPath = roleRedirects[user.role] || '/admin'
      console.log(`🚀 Redirecting ${user.role} to ${redirectPath}`)
      
      // Immediate redirect without setTimeout
      navigate(redirectPath, { replace: true })
    }
  }, [user, loading, navigate, location.pathname])

  const handleDashboardClick = () => {
    console.log('🔘 Dashboard button clicked:', { user: user?.nama_lengkap, role: user?.role })
    
    if (!user) {
      // User belum login, redirect ke login
      console.log('❌ No user, redirecting to login')
      navigate('/login')
      return
    }

    // User sudah login, redirect ke dashboard sesuai role
    const roleRedirects = {
      'pemilik': '/admin',
      'koordinator': '/admin', 
      'pengawas_kost': '/admin',
      'petugas_laundry': '/laundry',
      'petugas_kebersihan': '/admin',
      'petugas_keamanan': '/admin',
      'penghuni': '/penghuni'
    }
    
    const redirectPath = roleRedirects[user.role] || '/admin'
    console.log(`🎯 Manual redirect: ${user.role} → ${redirectPath}`)
    navigate(redirectPath, { replace: true })
  }

  const features = [
    {
      icon: <Home className="w-6 h-6" />,
      title: "Kamar Modern & Nyaman",
      description: "24 kamar dengan fasilitas lengkap, AC/kipas angin, dan furniture berkualitas"
    },
    {
      icon: <Droplets className="w-6 h-6" />,
      title: "Tracking Laundry Real-time",
      description: "Pantau status cucian Anda dari mulai diterima hingga siap diambil"
    },
    {
      icon: <CreditCard className="w-6 h-6" />,
      title: "Pembayaran Digital",
      description: "Bayar sewa kost dan laundry dengan mudah melalui sistem online"
    },
    {
      icon: <Shield className="w-6 h-6" />,
      title: "Keamanan 24 Jam",
      description: "CCTV dan petugas keamanan untuk kenyamanan penghuni"
    },
    {
      icon: <Wifi className="w-6 h-6" />,
      title: "WiFi Kecepatan Tinggi",
      description: "Internet stabil untuk mendukung aktivitas belajar dan bekerja"
    },
    {
      icon: <Clock className="w-6 h-6" />,
      title: "Layanan Responsif",
      description: "Tim siap melayani kebutuhan penghuni dengan cepat dan ramah"
    }
  ]

  const laundryProcess = [
    { step: 1, title: "Diterima", desc: "Cucian diterima dan ditimbang" },
    { step: 2, title: "Dicuci", desc: "Proses pencucian sesuai jenis" },
    { step: 3, title: "Dikeringkan", desc: "Pengeringan & setrika" },
    { step: 4, title: "Siap Diambil", desc: "Notifikasi otomatis" }
  ]

  const testimonials = [
    {
      name: "Thika",
      role: "Mahasiswa",
      content: "Sistem tracking laundry-nya sangat membantu! Saya bisa tau kapan cucian saya selesai tanpa harus bolak-balik tanya.",
      rating: 5
    },
    {
      name: "Riki Ananda",
      role: "Karyawan",
      content: "Kost yang nyaman dengan sistem pembayaran yang transparan. Tidak ada hidden cost dan semua tercatat dengan baik.",
      rating: 5
    },
    {
      name: "Christine",
      role: "Mahasiswa",
      content: "Fasilitas lengkap, lokasi strategis, dan pelayanan ramah. Highly recommended untuk yang cari kost di Medan Johor!",
      rating: 5
    }
  ]

  // Show loading spinner if still loading auth
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">
        <div className="text-center text-white">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
          <p>Memuat...</p>
        </div>
      </div>
    )
  }

  return (
    <div>
      {/* Hero Section */}
      <section className="relative bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700 overflow-hidden">
        <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]" />
        <div className="absolute top-20 right-10 w-72 h-72 bg-primary-500 rounded-full filter blur-3xl opacity-20 animate-pulse" />
        <div className="absolute bottom-10 left-10 w-96 h-96 bg-primary-400 rounded-full filter blur-3xl opacity-20 animate-pulse animation-delay-2000" />
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
          <div className="text-center">
            <div className="inline-flex items-center px-4 py-2 bg-primary-800/50 backdrop-blur-sm rounded-full text-primary-100 text-sm font-medium mb-6">
              <Sparkles className="w-4 h-4 mr-2" />
              Sistem Informasi Kost Modern
            </div>
            
            <h1 className="text-4xl lg:text-6xl font-heading font-bold text-white mb-6 animate-fade-in">
              Kelola Kost & Laundry<br />
              <span className="text-primary-300">Lebih Mudah, Cepat, dan Transparan</span>
            </h1>
            
            <p className="text-xl text-primary-100 mb-8 max-w-3xl mx-auto animate-slide-in">
              Domos Kost Group menghadirkan solusi hunian modern dengan sistem pembayaran digital 
              dan tracking laundry real-time untuk kenyamanan maksimal penghuni.
            </p>
            
            <div className="flex flex-col sm:flex-row gap-4 justify-center animate-bounce-in">
                             <button
                 onClick={handleDashboardClick}
                 className="inline-flex items-center px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-xl"
               >
                 {user ? 'Masuk ke Dashboard' : 'Masuk ke Dashboard'}
                 <ArrowRight className="w-5 h-5 ml-2" />
               </button>
              <a
                href="#features"
                className="inline-flex items-center px-8 py-4 bg-primary-600/20 backdrop-blur-sm text-white font-bold rounded-xl hover:bg-primary-600/30 border-2 border-primary-400/50 transform hover:scale-105 transition-all duration-200"
              >
                Lihat Fitur
                <Zap className="w-5 h-5 ml-2" />
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-12 bg-white border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <div className="text-center">
              <div className="text-4xl font-bold text-primary-600 mb-2">24</div>
              <div className="text-gray-600">Kamar Tersedia</div>
            </div>
            <div className="text-center">
              <div className="text-4xl font-bold text-primary-600 mb-2">2</div>
              <div className="text-gray-600">Lantai Bangunan</div>
            </div>
            <div className="text-center">
              <div className="text-4xl font-bold text-primary-600 mb-2">17+</div>
              <div className="text-gray-600">Penghuni Aktif</div>
            </div>
            <div className="text-center">
              <div className="text-4xl font-bold text-primary-600 mb-2">24/7</div>
              <div className="text-gray-600">Layanan Keamanan</div>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section id="features" className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl lg:text-4xl font-heading font-bold text-gray-900 mb-4">
              Fitur Unggulan Kami
            </h2>
            <p className="text-xl text-gray-600 max-w-2xl mx-auto">
              Nikmati kemudahan pengelolaan kost dan laundry dengan teknologi modern
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <div
                key={index}
                className="card p-6 hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300"
              >
                <div className="w-12 h-12 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center mb-4">
                  {feature.icon}
                </div>
                <h3 className="text-xl font-semibold text-gray-900 mb-2">{feature.title}</h3>
                <p className="text-gray-600">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Laundry Tracking Process */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl lg:text-4xl font-heading font-bold text-gray-900 mb-4">
              Tracking Laundry Real-time
            </h2>
            <p className="text-xl text-gray-600 max-w-2xl mx-auto">
              Pantau status cucian Anda secara real-time melalui dashboard
            </p>
          </div>

          <div className="relative">
            <div className="absolute top-1/2 left-0 right-0 h-1 bg-primary-200 -translate-y-1/2 hidden lg:block" />
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {laundryProcess.map((process, index) => (
                <div key={index} className="relative">
                  <div className="bg-white p-6 rounded-xl shadow-lg border-2 border-primary-100 hover:border-primary-300 transition-colors">
                    <div className="w-12 h-12 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold text-lg mb-4 mx-auto">
                      {process.step}
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900 text-center mb-2">
                      {process.title}
                    </h3>
                    <p className="text-gray-600 text-center text-sm">{process.desc}</p>
                  </div>
                  {index < laundryProcess.length - 1 && (
                    <ArrowRight className="hidden lg:block absolute top-1/2 -right-6 transform -translate-y-1/2 text-primary-400 w-5 h-5 z-10" />
                  )}
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="py-20 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl lg:text-4xl font-heading font-bold text-gray-900 mb-4">
              Apa Kata Penghuni Kami?
            </h2>
            <p className="text-xl text-gray-600 max-w-2xl mx-auto">
              Testimoni dari penghuni setia Domos Kost Group
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {testimonials.map((testimonial, index) => (
              <div key={index} className="card p-6">
                <div className="flex mb-4">
                  {[...Array(testimonial.rating)].map((_, i) => (
                    <Star key={i} className="w-5 h-5 text-yellow-400 fill-current" />
                  ))}
                </div>
                <p className="text-gray-600 mb-6 italic">"{testimonial.content}"</p>
                <div className="flex items-center">
                  <div className="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                    <Users className="w-6 h-6 text-primary-600" />
                  </div>
                  <div className="ml-3">
                    <p className="font-semibold text-gray-900">{testimonial.name}</p>
                    <p className="text-sm text-gray-500">{testimonial.role}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-to-r from-primary-600 to-primary-700">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl lg:text-4xl font-heading font-bold text-white mb-6">
            Siap Bergabung dengan Domos Kost?
          </h2>
          <p className="text-xl text-primary-100 mb-8">
            Rasakan pengalaman tinggal di kost modern dengan sistem digital terintegrasi
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              to="/login"
              className="inline-flex items-center px-8 py-4 bg-white text-primary-700 font-bold rounded-xl hover:bg-gray-100 transform hover:scale-105 transition-all duration-200 shadow-xl"
            >
              <Heart className="w-5 h-5 mr-2" />
              Mulai Sekarang
            </Link>
            <a
              href="#contact"
              className="inline-flex items-center px-8 py-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-400 transform hover:scale-105 transition-all duration-200"
            >
              Hubungi Kami
              <ArrowRight className="w-5 h-5 ml-2" />
            </a>
          </div>
        </div>
      </section>
    </div>
  )
}

export default LandingPage
