import { useState } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { Menu, Bell, UserCircle, Search, Sun, Moon } from 'lucide-react'
import { Menu as HeadlessMenu, Transition } from '@headlessui/react'
import { Fragment } from 'react'
import clsx from 'clsx'

const DashboardNavbar = ({ onMenuClick }) => {
  const { user, logout } = useAuth()
  const [darkMode, setDarkMode] = useState(false)

  // Mock notifications - in real app, fetch from API
  const notifications = [
    { id: 1, title: 'Pembayaran Baru', message: 'Thika telah melakukan pembayaran', time: '5m', read: false },
    { id: 2, title: 'Laundry Selesai', message: 'Order #LD-202412-010 siap diambil', time: '1h', read: false },
    { id: 3, title: 'Penghuni Baru', message: 'Ada pendaftar baru menunggu verifikasi', time: '2h', read: true },
  ]

  const unreadCount = notifications.filter(n => !n.read).length

  return (
    <nav className="sticky top-0 z-40 bg-white border-b border-gray-200">
      <div className="px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          {/* Left side */}
          <div className="flex items-center">
            <button
              onClick={onMenuClick}
              className="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100 md:hidden"
            >
              <Menu className="h-6 w-6" />
            </button>

            {/* Search bar */}
            <div className="ml-4 lg:ml-0 flex-1 max-w-md">
              <div className="relative">
                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <Search className="h-5 w-5 text-gray-400" />
                </div>
                <input
                  type="search"
                  className="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-10 pr-3 text-sm placeholder-gray-500 focus:border-primary-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                  placeholder="Cari penghuni, kamar, atau transaksi..."
                />
              </div>
            </div>
          </div>

          {/* Right side */}
          <div className="flex items-center space-x-4">
            {/* Dark mode toggle */}
            <button
              onClick={() => setDarkMode(!darkMode)}
              className="p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100"
            >
              {darkMode ? (
                <Sun className="h-5 w-5" />
              ) : (
                <Moon className="h-5 w-5" />
              )}
            </button>

            {/* Notifications */}
            <HeadlessMenu as="div" className="relative">
              <HeadlessMenu.Button className="relative p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                <Bell className="h-5 w-5" />
                {unreadCount > 0 && (
                  <span className="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500" />
                )}
              </HeadlessMenu.Button>

              <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
              >
                <HeadlessMenu.Items className="absolute right-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <div className="p-4 border-b border-gray-200">
                    <h3 className="text-sm font-semibold text-gray-900">Notifikasi</h3>
                  </div>
                  <div className="max-h-96 overflow-y-auto">
                    {notifications.length > 0 ? (
                      notifications.map((notification) => (
                        <HeadlessMenu.Item key={notification.id}>
                          {({ active }) => (
                            <button
                              className={clsx(
                                'w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors',
                                !notification.read && 'bg-blue-50'
                              )}
                            >
                              <div className="flex justify-between items-start">
                                <div className="flex-1">
                                  <p className="text-sm font-medium text-gray-900">
                                    {notification.title}
                                  </p>
                                  <p className="text-sm text-gray-600 mt-1">
                                    {notification.message}
                                  </p>
                                </div>
                                <span className="text-xs text-gray-500 ml-2">
                                  {notification.time}
                                </span>
                              </div>
                            </button>
                          )}
                        </HeadlessMenu.Item>
                      ))
                    ) : (
                      <div className="px-4 py-8 text-center text-sm text-gray-500">
                        Tidak ada notifikasi
                      </div>
                    )}
                  </div>
                  <div className="p-2 border-t border-gray-200">
                    <button className="w-full text-center text-sm text-primary-600 hover:text-primary-700 font-medium py-2 hover:bg-gray-50 rounded">
                      Lihat Semua Notifikasi
                    </button>
                  </div>
                </HeadlessMenu.Items>
              </Transition>
            </HeadlessMenu>

            {/* Profile dropdown */}
            <HeadlessMenu as="div" className="relative">
              <HeadlessMenu.Button className="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                <div className="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                  <UserCircle className="w-5 h-5 text-primary-600" />
                </div>
                <div className="hidden md:block text-left">
                  <p className="text-sm font-medium text-gray-700">{user?.nama_lengkap}</p>
                  <p className="text-xs text-gray-500">{user?.role}</p>
                </div>
              </HeadlessMenu.Button>

              <Transition
                as={Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
              >
                <HeadlessMenu.Items className="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <HeadlessMenu.Item>
                    {({ active }) => (
                      <a
                        href="#"
                        className={clsx(
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-gray-700'
                        )}
                      >
                        Profil Saya
                      </a>
                    )}
                  </HeadlessMenu.Item>
                  <HeadlessMenu.Item>
                    {({ active }) => (
                      <a
                        href="#"
                        className={clsx(
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-gray-700'
                        )}
                      >
                        Pengaturan
                      </a>
                    )}
                  </HeadlessMenu.Item>
                  <hr className="my-1" />
                  <HeadlessMenu.Item>
                    {({ active }) => (
                      <button
                        onClick={logout}
                        className={clsx(
                          active ? 'bg-gray-100' : '',
                          'block w-full text-left px-4 py-2 text-sm text-red-600'
                        )}
                      >
                        Keluar
                      </button>
                    )}
                  </HeadlessMenu.Item>
                </HeadlessMenu.Items>
              </Transition>
            </HeadlessMenu>
          </div>
        </div>
      </div>
    </nav>
  )
}

export default DashboardNavbar
