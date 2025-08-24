import React from 'react'
import { Loader2 } from 'lucide-react'

const LoadingSpinner = ({ size = "large", text = "Memuat data..." }) => {
  const sizeClasses = {
    small: "w-5 h-5",
    medium: "w-8 h-8", 
    large: "w-12 h-12"
  }

  return (
    <div className="flex flex-col items-center justify-center min-h-[400px] space-y-4">
      <Loader2 className={`${sizeClasses[size]} text-primary-600 animate-spin`} />
      <p className="text-gray-600 text-sm font-medium">{text}</p>
    </div>
  )
}

export default LoadingSpinner