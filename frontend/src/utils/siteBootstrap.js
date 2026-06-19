import { buildSiteDataFromContent } from '../data/defaultSiteContentData'
import { readHomepageCache, readSiteContentCache } from './siteContentCache'

/**
 * Read CMS bootstrap payload injected by Laravel into the production HTML shell.
 * In local dev (Vite), this is null until /api/bootstrap is fetched.
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

export function getBootstrappedSitePages() {
  const bootstrap = readSiteBootstrap()
  const sitePages = bootstrap?.sitePages
  if (!sitePages || typeof sitePages !== 'object') return null
  return sitePages
}

export function getBootstrappedSitePage(slug) {
  if (!slug) return null
  return getBootstrappedSitePages()?.[slug] ?? null
}

export function getBootstrappedBlogPosts() {
  const bootstrap = readSiteBootstrap()
  const posts = bootstrap?.blogPosts
  return Array.isArray(posts) ? posts : null
}

export function getBootstrappedBlogPost(slug) {
  if (!slug) return null
  return getBootstrappedBlogPosts()?.find((post) => post?.slug === slug) ?? null
}

export function getInitialSiteContent(getCached = () => null) {
  return getBootstrappedSiteContent() ?? getCached()
}

export function getInitialHomepage(getCached = () => null) {
  return getBootstrappedHomepage() ?? getCached()
}

export function getInstantSiteContent() {
  return getBootstrappedSiteContent() ?? readSiteContentCache()
}

export function getInstantHomepage() {
  const bootstrapHomepage = getBootstrappedHomepage()
  if (bootstrapHomepage && Object.keys(bootstrapHomepage).length > 0) {
    return bootstrapHomepage
  }

  const cachedHomepage = readHomepageCache()
  if (cachedHomepage && Object.keys(cachedHomepage).length > 0) {
    return cachedHomepage
  }

  const pages = getInstantSiteContent()
  if (pages && (pages.home || pages.global)) {
    return buildSiteDataFromContent(pages, { useDefaults: false })
  }

  return null
}

export function hasInstantSiteData() {
  return Boolean(getInstantSiteContent() || getInstantHomepage())
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
