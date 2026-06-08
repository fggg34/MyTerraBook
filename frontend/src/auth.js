import { setAuthToken } from './api'

const TOKEN_KEY = 'terrabook_token'
const USER_KEY = 'terrabook_user'

export function getStoredToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function storeAuth(token, user) {
  localStorage.setItem(TOKEN_KEY, token)
  localStorage.setItem(USER_KEY, JSON.stringify(user))
  setAuthToken(token)
}

export function clearAuth() {
  localStorage.removeItem(TOKEN_KEY)
  localStorage.removeItem(USER_KEY)
  setAuthToken(null)
}

export function getStoredUser() {
  const raw = localStorage.getItem(USER_KEY)
  return raw ? JSON.parse(raw) : null
}

/** Prefer localStorage when a token exists so route guards stay in sync right after login. */
export function getCurrentUser(contextUser) {
  const token = getStoredToken()
  const storedUser = token ? getStoredUser() : null
  return storedUser ?? contextUser ?? null
}
