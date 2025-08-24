import { Outlet } from 'react-router-dom'
import PublicNavbar from '../components/PublicNavbar'
import Footer from '../components/Footer'

const PublicLayout = () => {
  return (
    <div className="min-h-screen flex flex-col">
      <PublicNavbar />
      <main className="flex-1">
        <Outlet />
      </main>
      <Footer />
    </div>
  )
}

export default PublicLayout
