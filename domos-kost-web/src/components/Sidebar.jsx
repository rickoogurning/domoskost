import { Fragment } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { Dialog, Transition } from '@headlessui/react'
import { useAuth } from '../contexts/AuthContext'
import { 
  Home, Users, DoorOpen, FileText, ClipboardList, 
  Droplets, BarChart3, Settings, X, LogOut,
  CreditCard, Bell, UserCircle, Package, Calendar
} from 'lucide-react'
import clsx from 'clsx'

const Sidebar = ({ isOpen, onClose }) => {
  const location = useLocation()
  const { user, logout } = useAuth()

  // Menu items based on user role
  const getMenuItems = () => {
    const role = user?.role

    if (['pemilik', 'koordinator', 'pengawas_kost', 'petugas_kebersihan', 'petugas_keamanan'].includes(role)) {
      return [
        { name: 'Dashboard', href: '/admin', icon: Home },
        { name: 'Data Penghuni', href: '/admin/penghuni', icon: Users },
        { name: 'Data Kamar', href: '/admin/kamar', icon: DoorOpen },
        { name: 'Tagihan & Pembayaran', href: '/admin/tagihan', icon: CreditCard },
        { name: 'Order Laundry', href: '/admin/laundry', icon: Droplets },
        { name: 'Laporan Keuangan', href: '/admin/laporan', icon: BarChart3 },
        { name: 'Notifikasi', href: '/admin/notifikasi', icon: Bell },
        { name: 'Pengaturan', href: '/admin/pengaturan', icon: Settings },
      ]
    } else if (role === 'petugas_laundry') {
      return [
        { name: 'Dashboard', href: '/laundry', icon: Home },
        { name: 'Order Baru', href: '/laundry/order-baru', icon: Package },
        { name: 'Dalam Proses', href: '/laundry/proses', icon: Droplets },
        { name: 'Riwayat Selesai', href: '/laundry/selesai', icon: ClipboardList },
      ]
    } else if (role === 'penghuni') {
      return [
        { name: 'Dashboard', href: '/penghuni', icon: Home },
        { name: 'Tagihan Saya', href: '/penghuni/tagihan', icon: FileText },
        { name: 'Laundry Saya', href: '/penghuni/laundry', icon: Droplets },
        { name: 'Riwayat Pembayaran', href: '/penghuni/riwayat', icon: Calendar },
        { name: 'Profil Saya', href: '/penghuni/profil', icon: UserCircle },
      ]
    }
    
    return []
  }

  const menuItems = getMenuItems()
  
  // Debug info
  console.log('🔧 Sidebar Debug:', {
    user: user?.nama_lengkap,
    role: user?.role,
    menuItemsCount: menuItems.length,
    menuItems: menuItems.map(item => item.name)
  })

  const SidebarContent = () => (
    <>
      {/* Logo */}
      <div className="flex items-center justify-between px-4 py-6 border-b border-gray-200">
        <Link to="/" className="flex items-center space-x-3">
          <div className="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
            <Home className="w-6 h-6 text-white" />
          </div>
          <div>
            <h1 className="text-lg font-heading font-bold text-gray-900">Domos Kost</h1>
            <p className="text-xs text-gray-500">Management System</p>
          </div>
        </Link>
        <button
          onClick={onClose}
          className="lg:hidden p-2 rounded-lg hover:bg-gray-100"
        >
          <X className="w-5 h-5 text-gray-500" />
        </button>
      </div>

      {/* User Info */}
      <div className="px-4 py-4 border-b border-gray-200">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
            <UserCircle className="w-6 h-6 text-primary-600" />
          </div>
          <div className="flex-1">
            <p className="text-sm font-medium text-gray-900">{user?.nama_lengkap}</p>
            <p className="text-xs text-gray-500">{user?.role}</p>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="flex-1 px-2 py-4 overflow-y-auto">
        {menuItems.length === 0 ? (
          <div className="px-3 py-4 text-center">
            <p className="text-sm text-gray-500 mb-2">⚠️ Menu tidak tersedia</p>
            <p className="text-xs text-gray-400">Role: {user?.role || 'Unknown'}</p>
            <p className="text-xs text-gray-400">User: {user?.nama_lengkap || 'Unknown'}</p>
          </div>
        ) : (
          <ul className="space-y-1">
            {menuItems.map((item) => {
              const isActive = location.pathname === item.href || 
                             location.pathname.startsWith(item.href + '/')
              
              return (
                <li key={item.name}>
                  <Link
                    to={item.href}
                    onClick={() => onClose()}
                    className={clsx(
                      'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors',
                      isActive
                        ? 'bg-primary-50 text-primary-700'
                        : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                    )}
                  >
                    <item.icon className={clsx(
                      'w-5 h-5 mr-3',
                      isActive ? 'text-primary-600' : 'text-gray-400'
                    )} />
                    {item.name}
                  </Link>
                </li>
              )
            })}
          </ul>
        )}
      </nav>

      {/* Logout Button */}
      <div className="p-4 border-t border-gray-200">
        <button
          onClick={logout}
          className="w-full flex items-center px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors"
        >
          <LogOut className="w-5 h-5 mr-3" />
          Keluar
        </button>
      </div>
    </>
  )

  return (
    <>
      {/* Mobile Sidebar */}
      <Transition.Root show={isOpen} as={Fragment}>
        <Dialog as="div" className="relative z-50 md:hidden" onClose={onClose}>
          <Transition.Child
            as={Fragment}
            enter="transition-opacity ease-linear duration-300"
            enterFrom="opacity-0"
            enterTo="opacity-100"
            leave="transition-opacity ease-linear duration-300"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <div className="fixed inset-0 bg-gray-900/80" />
          </Transition.Child>

          <div className="fixed inset-0 flex">
            <Transition.Child
              as={Fragment}
              enter="transition ease-in-out duration-300 transform"
              enterFrom="-translate-x-full"
              enterTo="translate-x-0"
              leave="transition ease-in-out duration-300 transform"
              leaveFrom="translate-x-0"
              leaveTo="-translate-x-full"
            >
              <Dialog.Panel className="relative mr-16 flex w-full max-w-xs flex-1">
                <div className="flex grow flex-col bg-white">
                  <SidebarContent />
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </Dialog>
      </Transition.Root>

      {/* Desktop Sidebar */}
      <div className="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
        <div className="flex grow flex-col border-r border-gray-200 bg-white">
          <SidebarContent />
        </div>
      </div>
    </>
  )
}

export default Sidebar
