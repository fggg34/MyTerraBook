import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { api, setAuthToken } from '../api'
import { clearAuth, getStoredToken, getStoredUser, storeAuth } from '../auth'

const AuthContext = createContext(null)

export function getPostLoginPath(user, redirect) {
  if (redirect) return redirect
  if (user?.role === 'admin') return '/admin'
  if (user?.role === 'host') return '/host'
  return '/dashboard'
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(getStoredUser())

  useEffect(() => {
    const token = getStoredToken()
    if (!token) return
    setAuthToken(token)
    api.get('/user').then((res) => {
      storeAuth(token, res.data)
      setUser(res.data)
    }).catch(() => {})
  }, [])

  const loginWithCredentials = useCallback(async (email, password) => {
    const response = await api.post('/auth/login', { email, password })
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [])

  const registerAccount = useCallback(async (payload) => {
    const response = await api.post('/auth/register', payload)
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [])

  const registerAsHost = useCallback(async (payload) => {
    const response = await api.post('/auth/register-host', payload)
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [])

  const logout = useCallback(async () => {
    const token = getStoredToken()
    if (token) {
      try {
        await api.post('/auth/logout')
      } catch {
        /* ignore */
      }
    }
    clearAuth()
    setUser(null)
    setAuthToken(null)
  }, [])

  const value = useMemo(
    () => ({
      user,
      isAuthenticated: !!user,
      loginWithCredentials,
      registerAccount,
      registerAsHost,
      logout,
      setUser,
    }),
    [user, loginWithCredentials, registerAccount, registerAsHost, logout],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
