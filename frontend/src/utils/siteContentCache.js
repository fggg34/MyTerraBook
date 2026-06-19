const CACHE_VERSION = 1
const MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000

const KEYS = {
  siteContent: `myterrabook.siteContent.v${CACHE_VERSION}`,
  homepage: `myterrabook.homepage.v${CACHE_VERSION}`,
  sitePage: (slug) => `myterrabook.sitePage.v${CACHE_VERSION}.${slug}`,
}

function readEntry(key) {
  if (typeof window === 'undefined') return null

  try {
    const raw = window.localStorage.getItem(key)
    if (!raw) return null

    const parsed = JSON.parse(raw)
    if (!parsed || typeof parsed !== 'object' || parsed.data == null) return null
    if (typeof parsed.savedAt !== 'number' || Date.now() - parsed.savedAt > MAX_AGE_MS) {
      window.localStorage.removeItem(key)
      return null
    }

    return parsed.data
  } catch {
    return null
  }
}

function writeEntry(key, data) {
  if (typeof window === 'undefined' || data == null) return

  try {
    window.localStorage.setItem(
      key,
      JSON.stringify({
        savedAt: Date.now(),
        data,
      }),
    )
  } catch {
    // Ignore quota / private-mode errors.
  }
}

export function readSiteContentCache() {
  const data = readEntry(KEYS.siteContent)
  return data && typeof data === 'object' ? data : null
}

export function writeSiteContentCache(pages) {
  if (!pages || typeof pages !== 'object') return
  writeEntry(KEYS.siteContent, pages)
}

export function readHomepageCache() {
  const data = readEntry(KEYS.homepage)
  return data && typeof data === 'object' ? data : null
}

export function writeHomepageCache(homepage) {
  if (!homepage || typeof homepage !== 'object') return
  writeEntry(KEYS.homepage, homepage)
}

export function readSitePageCache(slug) {
  if (!slug) return null
  const data = readEntry(KEYS.sitePage(slug))
  return data && typeof data === 'object' ? data : null
}

export function writeSitePageCache(slug, page) {
  if (!slug || !page || typeof page !== 'object') return
  writeEntry(KEYS.sitePage(slug), page)
}

function collectImageUrls(value, urls, depth = 0) {
  if (depth > 8 || value == null) return

  if (typeof value === 'string') {
    if (/^https?:\/\//i.test(value) || value.startsWith('/')) {
      if (/\.(avif|gif|jpe?g|png|svg|webp)(\?|$)/i.test(value)) {
        urls.add(value)
      }
    }
    return
  }

  if (Array.isArray(value)) {
    value.forEach((item) => collectImageUrls(item, urls, depth + 1))
    return
  }

  if (typeof value === 'object') {
    Object.values(value).forEach((item) => collectImageUrls(item, urls, depth + 1))
  }
}

/**
 * Warm the browser image cache for hero/logo and other CMS images from bootstrap or storage.
 */
export function preloadSiteAssets(pages, homepage) {
  if (typeof window === 'undefined') return

  const urls = new Set()
  collectImageUrls(pages, urls)
  collectImageUrls(homepage, urls)

  const branding = pages?.global?.branding
  if (branding?.favicon) urls.add(branding.favicon)
  if (branding?.logoImage) urls.add(branding.logoImage)

  urls.forEach((url) => {
    const img = new Image()
    img.decoding = 'async'
    img.src = url
  })
}
