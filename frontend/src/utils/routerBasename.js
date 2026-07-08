export const ADMIN_CALENDAR_EMBED_PATH = '/calendar-embed'

export function detectRouterBasename() {
  const { pathname } = window.location
  if (pathname === '/backend' || pathname.startsWith('/backend/')) {
    return '/backend'
  }
  return undefined
}
