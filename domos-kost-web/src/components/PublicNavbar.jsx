import { Link } from 'react-router-dom'
import { Home, Info, Phone, LogIn } from 'lucide-react'

const PublicNavbar = () => {
  return (
    <nav className="bg-white shadow-sm sticky top-0 z-40">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex items-center">
            {/* Logo */}
            <Link to="/" className="flex items-center space-x-3">
              <div className="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                <Home className="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 className="text-xl font-heading font-bold text-gray-900">Domos Kost</h1>
                <p className="text-xs text-gray-500">Sistem Informasi Kost & Laundry</p>
              </div>
            </Link>

            {/* Navigation Links */}
            <div className="hidden md:flex items-center space-x-8 ml-10">
              <Link to="/" className="text-gray-700 hover:text-primary-600 font-medium transition-colors">
                Beranda
              </Link>
              <a href="#features" className="text-gray-700 hover:text-primary-600 font-medium transition-colors">
                Fitur
              </a>
              <a href="#about" className="text-gray-700 hover:text-primary-600 font-medium transition-colors">
                Tentang
              </a>
              <a href="#contact" className="text-gray-700 hover:text-primary-600 font-medium transition-colors">
                Kontak
              </a>
            </div>
          </div>

          {/* CTA Button */}
          <div className="flex items-center">
            <Link
              to="/login"
              className="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
            >
              <LogIn className="w-4 h-4 mr-2" />
              Login
            </Link>
          </div>
        </div>
      </div>
    </nav>
  )
}

export default PublicNavbar
