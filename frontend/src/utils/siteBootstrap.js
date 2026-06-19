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

export function getBootstrappedBranding() {
  const pages = getBootstrappedSiteContent()
  const branding = pages?.global?.branding
  if (!branding || typeof branding !== 'object') return null
  return branding
}

export function mergeBranding(apiBranding = {}) {
  const boot = getBootstrappedBranding()
  if (!boot) return apiBranding
  return { ...boot, ...apiBranding }
}

export function applyBootstrapDocumentMeta() {
  if (typeof document === 'undefined') return

  const branding = getBootstrappedBranding()
  if (!branding) return

  const prefix = branding.prefix ?? 'My'
  const accent = branding.accent ?? 'Terra'
  const suffix = branding.suffix ?? 'Book'
  document.title = `${prefix}${accent}${suffix}`

  const favicon = branding.favicon
  if (!favicon) return

  let link = document.querySelector("link[rel~='icon']")
  if (!link) {
    link = document.createElement('link')
    link.rel = 'icon'
    document.head.appendChild(link)
  }

  const ext = String(favicon).split('.').pop()?.toLowerCase()
  const typeMap = { svg: 'image/svg+xml', png: 'image/png', ico: 'image/x-icon' }
  if (ext && typeMap[ext]) {
    link.type = typeMap[ext]
  } else {
    link.removeAttribute('type')
  }
  link.href = favicon
}
