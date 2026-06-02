import { Loader2 } from 'lucide-react'

export default function LoadingSpinner({ size = 'md', className = '' }) {
  const sizes = { sm: 'h-4 w-4', md: 'h-8 w-8', lg: 'h-12 w-12' }
  return (
    <Loader2
      className={`animate-spin text-accent ${sizes[size] || sizes.md} ${className}`}
      aria-label="Loading"
    />
  )
}

export function PageLoader({ message = 'Loading…' }) {
  return (
    <div className="flex min-h-[40vh] flex-col items-center justify-center gap-4">
      <LoadingSpinner size="lg" />
      <p className="text-sm text-slate-500">{message}</p>
    </div>
  )
}
