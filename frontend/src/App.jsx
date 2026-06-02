import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import { Navigate, Route, Routes } from 'react-router-dom'
import { getSitePreviewUrl, setAuthToken } from './api'
import { AuthProvider, useAuth } from './context/AuthContext'
import { ToastProvider } from './context/ToastContext'
import { getStoredToken } from './auth'
import Layout from './components/layout/Layout'
import LoadingSpinner from './components/ui/LoadingSpinner'
import AdminDashboardPage from './pages/AdminDashboardPage'
import CarDetailsPage from './pages/CarDetailsPage'
import CarListingPage from './pages/CarListingPage'
import CheckoutPage from './pages/CheckoutPage'
import HomePageContainer from './pages/HomePageContainer'
import HomeSearchPage from './pages/HomeSearchPage'
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import UnderConstructionPage from './pages/UnderConstructionPage'
import UserDashboardPage from './pages/UserDashboardPage'

function ProtectedRoute({ children, role }) {
  const { user } = useAuth()
  if (!user) return <Navigate to="/login" replace />
  if (role && user.role !== role) return <Navigate to="/" replace />
  return children
}

function AppRoutes() {
  const [previewUnlocked, setPreviewUnlocked] = useState(null)
  const token = getStoredToken()
  setAuthToken(token)

  const refreshPreview = useCallback(async () => {
    try {
      const headers = {}
      if (token) headers.Authorization = `Bearer ${token}`
      const { data } = await axios.get(getSitePreviewUrl(), {
        withCredentials: true,
        headers,
      })
      setPreviewUnlocked(!!data.preview_unlocked)
    } catch {
      setPreviewUnlocked(!!import.meta.env.DEV)
    }
  }, [token])

  useEffect(() => {
    refreshPreview()
  }, [refreshPreview])

  if (previewUnlocked === null) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-brand-950">
        <LoadingSpinner size="lg" />
      </div>
    )
  }

  if (!previewUnlocked) {
    return (
      <Routes>
        <Route path="*" element={<UnderConstructionPage />} />
      </Routes>
    )
  }

  return (
    <Routes>
      <Route path="/" element={<HomePageContainer />} />
      <Route element={<Layout />}>
        <Route path="/search" element={<HomeSearchPage />} />
        <Route path="/home" element={<Navigate to="/" replace />} />
        <Route path="/cars" element={<CarListingPage />} />
        <Route path="/cars/:id" element={<CarDetailsPage />} />
        <Route path="/checkout" element={<CheckoutPage />} />
        <Route
          path="/dashboard"
          element={
            <ProtectedRoute>
              <UserDashboardPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/admin"
          element={
            <ProtectedRoute role="admin">
              <AdminDashboardPage />
            </ProtectedRoute>
          }
        />
      </Route>
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />
    </Routes>
  )
}

function App() {
  return (
    <ToastProvider>
      <AuthProvider>
        <AppRoutes />
      </AuthProvider>
    </ToastProvider>
  )
}

export default App
