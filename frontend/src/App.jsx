import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import { Navigate, Route, Routes, useParams } from 'react-router-dom'
import { getSitePreviewUrl, setAuthToken } from './api'
import { AuthProvider, useAuth } from './context/AuthContext'
import { SiteLayoutProvider } from './context/SiteLayoutContext'
import { ToastProvider } from './context/ToastContext'
import { getStoredToken } from './auth'
import SiteLayout from './components/layout/SiteLayout'
import BookingLayout from './components/layout/BookingLayout'
import SearchResultsLayout from './components/layout/SearchResultsLayout'
import LoadingSpinner from './components/ui/LoadingSpinner'
import AdminDashboardPage from './pages/AdminDashboardPage'
import BecomeHostPage from './pages/BecomeHostPage'
import ListingPage from './pages/ListingPage'
import SearchResultsPage from './pages/SearchResultsPage'
import CheckoutPage from './pages/CheckoutPage'
import HomePageContainer from './pages/HomePageContainer'
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import UnderConstructionPage from './pages/UnderConstructionPage'
import UserDashboardPage from './pages/UserDashboardPage'
import GuestHouseCheckoutPage from './pages/guest-houses/GuestHouseCheckoutPage'

function RedirectGuestHouseSlug() {
  const { slug } = useParams()
  return <Navigate to={`/guesthouses/${slug}`} replace />
}

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
        <Route path="/home" element={<Navigate to="/" replace />} />
        <Route path="/cars/:id" element={<ListingPage listingType="car" />} />
        <Route path="/campervans/:id" element={<ListingPage listingType="campervan" />} />
        <Route path="/guesthouses/:id" element={<ListingPage listingType="guesthouse" />} />
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
        <Route path="/guest-houses" element={<Navigate to="/guesthouses" replace />} />
        <Route path="/guest-houses/:slug" element={<RedirectGuestHouseSlug />} />
      </Route>
      <Route
        element={
          <SiteLayoutProvider>
            <BookingLayout />
          </SiteLayoutProvider>
        }
      >
        <Route path="/checkout" element={<CheckoutPage />} />
        <Route path="/guest-houses/checkout" element={<GuestHouseCheckoutPage />} />
      </Route>
      <Route
        element={
          <SiteLayoutProvider>
            <SearchResultsLayout />
          </SiteLayoutProvider>
        }
      >
        <Route path="/search" element={<SearchResultsPage vehicleType="campervan" />} />
        <Route path="/campervans" element={<SearchResultsPage vehicleType="campervan" />} />
        <Route path="/cars" element={<SearchResultsPage vehicleType="car" />} />
        <Route path="/guesthouses" element={<SearchResultsPage vehicleType="guesthouse" />} />
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
