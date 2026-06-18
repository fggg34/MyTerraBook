/**
 * Read CMS bootstrap payload injected by Laravel into the production HTML shell.
 * In local dev (Vite), this is null and the app fetches /api/site-content instead.
 */
export function readSiteBootstrap() {
  if (typeof window === 'undefined') return null

  const data = window.__MYTERRABOOK_BOOTSTRAP__
  if (!data || typeof data !== 'object') return null

  return data
}

export function getBootstrappedSiteContent() {
  const bootstrap = readSiteBootstrap()
  const pages = bootstrap?.siteContent
  if (!pages || typeof pages !== 'object') return null
  return pages
}

export function getBootstrappedHomepage() {
  const bootstrap = readSiteBootstrap()
  const homepage = bootstrap?.homepage
  if (!homepage || typeof homepage !== 'object') return null
  return homepage
}
