import React from 'react'
import { useAuth } from '../contexts/AuthContext'
import { useLocation } from 'react-router-dom'

const DebugAuth = () => {
  const { user, loading } = useAuth()
  const location = useLocation()

  // Only show in development
  if (process.env.NODE_ENV !== 'development') {
    return null
  }

  return (
    <div className="fixed bottom-4 right-4 bg-black/80 text-white p-4 rounded-lg text-xs max-w-sm z-50">
      <div className="font-bold mb-2">🔧 Debug Auth</div>
      <div className="space-y-1">
        <div>Loading: {loading ? '✅' : '❌'}</div>
        <div>User: {user?.nama_lengkap || 'None'}</div>
        <div>Role: {user?.role || 'None'}</div>
        <div>Path: {location.pathname}</div>
        <div>Token: {localStorage.getItem('token') ? '✅' : '❌'}</div>
      </div>
    </div>
  )
}

export default DebugAuth
