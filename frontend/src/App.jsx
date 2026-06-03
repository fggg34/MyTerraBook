import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import { Navigate, Route, Routes } from 'react-router-dom'
import { getSitePreviewUrl, setAuthToken } from './api'
import { AuthProvider, useAuth } from './context/AuthContext'
import { SiteLayoutProvider } from './context/SiteLayoutContext'
import { ToastProvider } from './context/ToastContext'
import { getStoredToken } from './auth'
import SiteLayout from './components/layout/SiteLayout'
import LoadingSpinner from './components/ui/LoadingSpinner'
import AdminDashboardPage from './pages/AdminDashboardPage'
import BecomeHostPage from './pages/BecomeHostPage'
import CarDetailsPage from './pages/CarDetailsPage'
import SearchResultsPage from './pages/SearchResultsPage'
import CheckoutPage from './pages/CheckoutPage'
import HomePageContainer from './pages/HomePageContainer'
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
      <Route path="/become-a-host" element={<BecomeHostPage />} />
      <Route
        element={
          <SiteLayoutProvider>
            <SiteLayout />
          </SiteLayoutProvider>
        }
      >
        <Route path="/" element={<HomePageContainer />} />
        <Route path="/search" element={<SearchResultsPage vehicleType="campervan" />} />
        <Route path="/home" element={<Navigate to="/" replace />} />
        <Route path="/campervans" element={<SearchResultsPage vehicleType="campervan" />} />
        <Route path="/cars" element={<SearchResultsPage vehicleType="car" />} />
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
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
      </Route>
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
