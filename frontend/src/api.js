import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'

/**
 * Session-based preview check; defaults to same base as the API (`…/api` + `/site-preview`).
 * Override with VITE_SITE_PREVIEW_URL only if you must point somewhere else.
 */
export function getSitePreviewUrl() {
  const explicit = import.meta.env.VITE_SITE_PREVIEW_URL
  if (explicit) {
    return explicit
  }
  const base = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  return `${base.replace(/\/$/, '')}/site-preview`
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
