import { useCallback, useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import axios from 'axios'
import { Link, Navigate, Route, Routes } from 'react-router-dom'
import { api, getSitePreviewUrl, setAuthToken } from './api'
import { clearAuth, getStoredToken, getStoredUser, storeAuth } from './auth'
import './index.css'
import AdminDashboardPage from './pages/AdminDashboardPage'
import CarDetailsPage from './pages/CarDetailsPage'
import CarListingPage from './pages/CarListingPage'
import CheckoutPage from './pages/CheckoutPage'
import HomeSearchPage from './pages/HomeSearchPage'
import UnderConstructionPage from './pages/UnderConstructionPage'
import UserDashboardPage from './pages/UserDashboardPage'

function App() {
  const { t } = useTranslation()
  const [user, setUser] = useState(getStoredUser())
  const [previewUnlocked, setPreviewUnlocked] = useState(null)

  const [form, setForm] = useState({ email: '', password: '' })

  const token = getStoredToken()
  setAuthToken(token)

  const refreshPreview = useCallback(async () => {
    try {
      const t = getStoredToken()
      const headers = {}
      if (t) {
        headers.Authorization = `Bearer ${t}`
      }
      const { data } = await axios.get(getSitePreviewUrl(), {
        withCredentials: true,
        headers,
      })
      setPreviewUnlocked(!!data.preview_unlocked)
    } catch {
      setPreviewUnlocked(false)
    }
  }, [])

  useEffect(() => {
    refreshPreview()
  }, [refreshPreview, token])

  useEffect(() => {
    if (previewUnlocked !== false) {
      return undefined
    }
    const onFocus = () => refreshPreview()
    window.addEventListener('focus', onFocus)
    return () => window.removeEventListener('focus', onFocus)
  }, [previewUnlocked, refreshPreview])

  const login = async (event) => {
    event.preventDefault()
    const response = await api.post('/auth/login', form)
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    await refreshPreview()
  }

  const logout = async () => {
    if (token) {
      await api.post('/auth/logout')
    }
    clearAuth()
    setUser(null)
    setAuthToken(null)
    await refreshPreview()
  }

  if (previewUnlocked === null) {
    return (
      <div className="app app-preview-loading" aria-busy="true">
        <div className="app-preview-loading-inner" />
      </div>
    )
  }

  if (!previewUnlocked) {
    return (
      <div className="app">
        <Routes>
          <Route path="*" element={<UnderConstructionPage />} />
        </Routes>
      </div>
    )
  }

  return (
    <div className="app">
      <header className="nav">
        <h2>{t('appTitle')}</h2>
        <nav>
          <Link to="/">{t('home')}</Link>
          <Link to="/cars">{t('cars')}</Link>
          <Link to="/dashboard">{t('dashboard')}</Link>
          {user?.role === 'admin' && <Link to="/admin">{t('admin')}</Link>}
        </nav>
        {user ? (
          <button type="button" onClick={logout}>{t('logout')}</button>
        ) : (
          <form onSubmit={login} className="inline-form">
            <input
              placeholder="email"
              type="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
            <input
              placeholder="password"
              type="password"
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
            />
            <button type="submit">{t('login')}</button>
          </form>
        )}
      </header>

      <div className="container">
        <Routes>
          <Route path="/" element={<HomeSearchPage />} />
          <Route path="/home" element={<Navigate to="/" replace />} />
          <Route path="/cars" element={<CarListingPage />} />
          <Route path="/cars/:id" element={<CarDetailsPage />} />
          <Route path="/checkout" element={<CheckoutPage />} />
          <Route path="/dashboard" element={user ? <UserDashboardPage /> : <Navigate to="/" />} />
          <Route path="/admin" element={user?.role === 'admin' ? <AdminDashboardPage /> : <Navigate to="/" />} />
        </Routes>
      </div>
    </div>
  )
}

export default App
