import { useCallback, useEffect, useState } from 'react'
import axios from 'axios'
import { Navigate, Route, Routes, useParams } from 'react-router-dom'
import { getSitePreviewUrl, setAuthToken } from './api'
import { AuthProvider, useAuth } from './context/AuthContext'
import { SiteContentProvider } from './context/SiteContentContext'
import { SiteLayoutProvider } from './context/SiteLayoutContext'
import { ToastProvider } from './context/ToastContext'
import { getStoredToken } from './auth'
import SiteLayout from './components/layout/SiteLayout'
import ContentLayout from './components/layout/ContentLayout'
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
import HostLayout from './components/host/HostLayout'
import HostDashboardPage from './pages/host/HostDashboardPage'
import HostGuestHousesPage from './pages/host/HostGuestHousesPage'
import HostGuestHouseEditorPage from './pages/host/HostGuestHouseEditorPage'
import HostCarsPage from './pages/host/HostCarsPage'
import HostCarEditorPage from './pages/host/HostCarEditorPage'
import HostBookingsPage from './pages/host/HostBookingsPage'
import HostSettingsPage from './pages/host/HostSettingsPage'
import HostRegisterPage from './pages/host/HostRegisterPage'
import GuestHouseCheckoutPage from './pages/guest-houses/GuestHouseCheckoutPage'
import SitePagePage from './pages/SitePagePage'
import GoodToKnowPage from './pages/GoodToKnowPage'
import NewsletterUnsubscribePage from './pages/NewsletterUnsubscribePage'
import GoodToKnowPostPage from './pages/GoodToKnowPostPage'

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
        <Route
          path="/host"
          element={
            <ProtectedRoute role="host">
              <HostLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<HostDashboardPage />} />
          <Route path="guesthouses" element={<HostGuestHousesPage />} />
          <Route path="guesthouses/new" element={<HostGuestHouseEditorPage />} />
          <Route path="guesthouses/:id/edit" element={<HostGuestHouseEditorPage />} />
          <Route path="cars" element={<HostCarsPage />} />
          <Route path="cars/new" element={<HostCarEditorPage />} />
          <Route path="cars/:id/edit" element={<HostCarEditorPage />} />
          <Route path="bookings" element={<HostBookingsPage />} />
          <Route path="settings" element={<HostSettingsPage />} />
        </Route>
        <Route path="/guest-houses" element={<Navigate to="/guesthouses" replace />} />
        <Route path="/guest-houses/:slug" element={<RedirectGuestHouseSlug />} />
      </Route>
      <Route
        element={
          <SiteLayoutProvider>
            <ContentLayout />
          </SiteLayoutProvider>
        }
      >
        <Route path="/become-a-host" element={<BecomeHostPage />} />
        <Route path="/about" element={<SitePagePage forcedSlug="about" />} />
        <Route path="/faq" element={<SitePagePage forcedSlug="faq" />} />
        <Route path="/help" element={<Navigate to="/faq" replace />} />
        <Route path="/contact" element={<SitePagePage forcedSlug="contact" />} />
        <Route path="/terms" element={<SitePagePage forcedSlug="terms" />} />
        <Route path="/privacy" element={<SitePagePage forcedSlug="privacy" />} />
        <Route path="/cookies" element={<SitePagePage forcedSlug="cookies" />} />
        <Route path="/good-to-know" element={<GoodToKnowPage />} />
        <Route path="/good-to-know/:slug" element={<GoodToKnowPostPage />} />
        <Route path="/newsletter/unsubscribe" element={<NewsletterUnsubscribePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/host/register" element={<HostRegisterPage />} />
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
        <SiteContentProvider>
          <AppRoutes />
        </SiteContentProvider>
      </AuthProvider>
    </ToastProvider>
  )
}

export default App
