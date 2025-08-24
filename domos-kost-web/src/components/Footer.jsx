import { MapPin, Phone, Mail, Facebook, Instagram, Twitter } from 'lucide-react'

const Footer = () => {
  const currentYear = new Date().getFullYear()

  return (
    <footer className="bg-gray-900 text-gray-300">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          {/* Company Info */}
          <div>
            <h3 className="text-white font-heading font-bold text-lg mb-4">Domos Kost Group</h3>
            <p className="text-sm mb-4">
              Hunian nyaman dan modern dengan layanan laundry terintegrasi untuk mahasiswa dan karyawan di Medan.
            </p>
            <div className="flex space-x-4">
              <a href="#" className="hover:text-primary-400 transition-colors">
                <Facebook className="w-5 h-5" />
              </a>
              <a href="#" className="hover:text-primary-400 transition-colors">
                <Instagram className="w-5 h-5" />
              </a>
              <a href="#" className="hover:text-primary-400 transition-colors">
                <Twitter className="w-5 h-5" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-white font-heading font-bold text-lg mb-4">Quick Links</h3>
            <ul className="space-y-2">
              <li>
                <a href="#" className="text-sm hover:text-primary-400 transition-colors">Tentang Kami</a>
              </li>
              <li>
                <a href="#" className="text-sm hover:text-primary-400 transition-colors">Fasilitas</a>
              </li>
              <li>
                <a href="#" className="text-sm hover:text-primary-400 transition-colors">Layanan Laundry</a>
              </li>
              <li>
                <a href="#" className="text-sm hover:text-primary-400 transition-colors">Syarat & Ketentuan</a>
              </li>
            </ul>
          </div>

          {/* Services */}
          <div>
            <h3 className="text-white font-heading font-bold text-lg mb-4">Layanan Kami</h3>
            <ul className="space-y-2">
              <li className="text-sm">✓ Kamar Kost Modern</li>
              <li className="text-sm">✓ Layanan Laundry Express</li>
              <li className="text-sm">✓ WiFi Kecepatan Tinggi</li>
              <li className="text-sm">✓ Keamanan 24 Jam</li>
              <li className="text-sm">✓ Dapur Bersama</li>
              <li className="text-sm">✓ Ruang Santai</li>
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h3 className="text-white font-heading font-bold text-lg mb-4">Hubungi Kami</h3>
            <div className="space-y-3">
              <div className="flex items-start space-x-3">
                <MapPin className="w-5 h-5 text-primary-400 flex-shrink-0 mt-0.5" />
                <p className="text-sm">
                  Jl. Parang III Gg. Pekan Jaya No. 88,<br />
                  Kelurahan Kwala Bekala, Medan Johor,<br />
                  P. Bulan, 20142
                </p>
              </div>
              <div className="flex items-center space-x-3">
                <Phone className="w-5 h-5 text-primary-400" />
                <p className="text-sm">+62 812-3456-7890</p>
              </div>
              <div className="flex items-center space-x-3">
                <Mail className="w-5 h-5 text-primary-400" />
                <p className="text-sm">info@domoskost.com</p>
              </div>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-8 pt-8 border-t border-gray-800 text-center">
          <p className="text-sm">
            © {currentYear} Domos Kost Group. All rights reserved. | 
            <span className="text-xs ml-1">Developed by Mustika Sari Sinulingga - 210810065</span>
          </p>
        </div>
      </div>
    </footer>
  )
}

export default Footer
