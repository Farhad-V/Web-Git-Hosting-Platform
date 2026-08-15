import { LoaderCircle } from 'lucide-react'
import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from './auth-context'

function AuthenticationLoader() {
  return (
    <main className="grid min-h-screen place-items-center">
      <div className="flex items-center gap-3 text-sm text-slate-400">
        <LoaderCircle
          aria-hidden="true"
          className="size-5 animate-spin text-blue-400"
        />
        Verifying your session...
      </div>
    </main>
  )
}

export function ProtectedRoute() {
  const location = useLocation()
  const { isAuthenticated, isLoading } = useAuth()

  if (isLoading) {
    return <AuthenticationLoader />
  }

  if (!isAuthenticated) {
    return (
      <Navigate
        replace
        state={{ from: location.pathname }}
        to="/login"
      />
    )
  }

  return <Outlet />
}

export function GuestRoute() {
  const { isAuthenticated, isLoading } = useAuth()

  if (isLoading) {
    return <AuthenticationLoader />
  }

  if (isAuthenticated) {
    return <Navigate replace to="/dashboard" />
  }

  return <Outlet />
}