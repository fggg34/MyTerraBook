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
  return 'http://127.0.0.1:8000/api'
}

const API_BASE_URL = resolveApiBaseUrl()

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
  return config
})

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    delete api.defaults.headers.common.Authorization
  }
}
