import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { api, setAuthToken } from '../api'
import { clearAuth, getStoredToken, getStoredUser, storeAuth } from '../auth'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(getStoredUser())

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
      logout,
      setUser,
    }),
    [user, loginWithCredentials, registerAccount, logout],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
