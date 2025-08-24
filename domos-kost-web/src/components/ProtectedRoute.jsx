import { Navigate, useLocation } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import LoadingSpinner from './LoadingSpinner'

const ProtectedRoute = ({ children, allowedRoles = [] }) => {
  const { user, loading } = useAuth()
  const location = useLocation()

  console.log('🛡️ ProtectedRoute check:', { 
    loading, 
    user: user?.nama_lengkap, 
    role: user?.role, 
    allowedRoles,
    path: location.pathname 
  })

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <LoadingSpinner text="Memuat..." />
      </div>
    )
  }

  if (!user) {
    console.log('❌ No user, redirecting to login')
    // Redirect to login page but save the attempted location
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
    console.log('❌ Role not allowed:', user.role, 'allowed:', allowedRoles)
    // User doesn't have the required role
    return <Navigate to="/" replace />
  }

  console.log('✅ Access granted')
  return children
}

export default ProtectedRoute
