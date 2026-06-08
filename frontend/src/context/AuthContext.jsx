import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { api, setAuthToken } from '../api'
import { clearAuth, getCurrentUser, getStoredToken, getStoredUser, storeAuth } from '../auth'

const AuthContext = createContext(null)

/** API may return role as a string or enum-shaped object depending on serializer. */
export function normalizeUserRole(user) {
  const role = user?.role
  if (typeof role === 'string') return role.toLowerCase()
  if (role && typeof role === 'object') {
    if (typeof role.value === 'string') return role.value.toLowerCase()
    if (typeof role.name === 'string') return role.name.toLowerCase()
  }
  return null
}

export function getPostLoginPath(user, { hostIntent = false } = {}) {
  const role = normalizeUserRole(user)
  if (role === 'admin') return '/admin'
  if (role === 'host' || hostIntent) return '/host'
  return '/dashboard'
}

export function getLoginPathForRole(role) {
  if (role === 'host') return '/host/login'
  return '/login'
}

export function AuthProvider({ children }) {
  const [user, setUser] = useState(getStoredUser())
  const authEpochRef = useRef(0)

  const bumpAuthEpoch = useCallback(() => {
    authEpochRef.current += 1
    return authEpochRef.current
  }, [])

  useEffect(() => {
    const token = getStoredToken()
    if (!token) return undefined

    const requestEpoch = authEpochRef.current
    const requestToken = token
    const controller = new AbortController()

    setAuthToken(token)
    api.get('/user', { signal: controller.signal }).then((res) => {
      if (authEpochRef.current !== requestEpoch) return
      if (getStoredToken() !== requestToken) return

      const nextUser = res.data?.data ?? res.data
      storeAuth(requestToken, nextUser)
      setUser(nextUser)
    }).catch((err) => {
      if (controller.signal.aborted || err?.code === 'ERR_CANCELED') return
      if (authEpochRef.current !== requestEpoch || getStoredToken() !== requestToken) return
      if (err?.response?.status === 401) {
        clearAuth()
        setUser(null)
        setAuthToken(null)
      }
    })

    return () => {
      controller.abort()
    }
  }, [])

  const loginWithCredentials = useCallback(async (email, password) => {
    const response = await api.post('/auth/login', { email, password })
    bumpAuthEpoch()
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [bumpAuthEpoch])

  const registerAccount = useCallback(async (payload) => {
    const response = await api.post('/auth/register', payload)
    bumpAuthEpoch()
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [bumpAuthEpoch])

  const registerAsHost = useCallback(async (payload) => {
    const response = await api.post('/auth/register-host', payload)
    bumpAuthEpoch()
    storeAuth(response.data.token, response.data.user)
    setUser(response.data.user)
    setAuthToken(response.data.token)
    return response.data.user
  }, [bumpAuthEpoch])

  const requestPasswordReset = useCallback(async (email) => {
    const response = await api.post('/auth/forgot-password', { email })
    return response.data.message
  }, [])

  const resetPassword = useCallback(async (payload) => {
    const response = await api.post('/auth/reset-password', payload)
    return response.data.message
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
    bumpAuthEpoch()
    clearAuth()
    setUser(null)
    setAuthToken(null)
  }, [bumpAuthEpoch])

  const value = useMemo(
    () => ({
      user,
      isAuthenticated: !!getCurrentUser(user),
      loginWithCredentials,
      registerAccount,
      registerAsHost,
      requestPasswordReset,
      resetPassword,
      logout,
      setUser,
    }),
    [user, loginWithCredentials, registerAccount, registerAsHost, requestPasswordReset, resetPassword, logout],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

/** Send already-signed-in users to the correct dashboard instead of showing auth forms again. */
export function useRedirectIfAuthenticated(hostIntent = false) {
  const { user } = useAuth()
  const navigate = useNavigate()

  useEffect(() => {
    const current = getCurrentUser(user)
    if (!current) return undefined

    const role = normalizeUserRole(current)
    if (hostIntent) {
      if (role !== 'host' && role !== 'admin') return undefined
    }

    navigate(getPostLoginPath(current, { hostIntent }), { replace: true })
    return undefined
  }, [user, hostIntent, navigate])
}
