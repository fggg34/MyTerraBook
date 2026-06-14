import axios from 'axios'

/**
 * Dev: local Laravel. Prod (no env): same-origin `/backend/api` so the bundle never
 * calls 127.0.0.1 on visitors’ devices (avoids Chrome “local network” permission prompts).
 * Set VITE_API_URL when the API is on another host or path.
 */
function resolveApiBaseUrl() {
  const fromEnv = import.meta.env.VITE_API_URL
  if (fromEnv) {
    return fromEnv.replace(/\/$/, '')
  }
  if (import.meta.env.PROD) {
    return '/backend/api'
  }
  return 'http://127.0.0.1:8080/api'
}

const API_BASE_URL = resolveApiBaseUrl()

/**
 * Build a public URL for a path stored via Filament disk (e.g. `cars/photo.jpg`).
 * Absolute URLs are returned unchanged.
 */
function normalizeStoragePath(path) {
  if (path == null || path === '') return ''
  if (Array.isArray(path)) {
    const first = path.find((item) => item != null && item !== '')
    return first == null ? '' : String(first).trim()
  }
  return String(path).trim()
}

export function resolveStorageUrl(path) {
  const p = normalizeStoragePath(path)
  if (!p) return ''
  // Bundled frontend assets (Vite public folder)
  if (p.startsWith('/images/')) return p
  const appBase = resolveApiBaseUrl().replace(/\/api\/?$/i, '')
  // Rewrite API absolute storage URLs to the current app base (fixes wrong APP_URL on live).
  if (/^https?:\/\//i.test(p)) {
    const storageMatch = p.match(/\/storage\/(.+)$/i)
    if (storageMatch) {
      return `${appBase}/storage/${storageMatch[1]}`
    }
    return p
  }
  if (p.startsWith('/storage/')) {
    return `${appBase}${p}`
  }
  return `${appBase}/storage/${p.replace(/^\/+/, '')}`
}

/** Resolve a CMS upload path, falling back to bundled defaults when empty. */
export function resolveCmsImage(value, fallback) {
  const merged = value == null || value === '' ? fallback : value
  if (merged == null || merged === '') return fallback || ''
  const resolved = resolveStorageUrl(merged)
  return resolved || fallback || ''
}

/**
 * Session-based preview check; defaults to same base as the API (`…/api` + `/site-preview`).
 * Override with VITE_SITE_PREVIEW_URL only if you must point somewhere else.
 */
export function getSitePreviewUrl() {
  const explicit = import.meta.env.VITE_SITE_PREVIEW_URL
  if (explicit) {
    return explicit
  }
  return `${resolveApiBaseUrl()}/site-preview`
}

export const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
})

api.interceptors.request.use((config) => {
  config.headers['Accept-Language'] = 'en'
  const token = typeof localStorage !== 'undefined' ? localStorage.getItem('terrabook_token') : null
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    delete api.defaults.headers.common.Authorization
  }
}
