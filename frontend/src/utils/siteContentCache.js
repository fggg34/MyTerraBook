const CACHE_VERSION = 2
const MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000

const KEYS = {
  siteContent: `myterrabook.siteContent.v${CACHE_VERSION}`,
  homepage: `myterrabook.homepage.v${CACHE_VERSION}`,
  sitePages: `myterrabook.sitePages.v${CACHE_VERSION}`,
  blogPosts: `myterrabook.blogPosts.v${CACHE_VERSION}`,
  sitePage: (slug) => `myterrabook.sitePage.v${CACHE_VERSION}.${slug}`,
  blogPost: (slug) => `myterrabook.blogPost.v${CACHE_VERSION}.${slug}`,
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

export function readSitePagesCache() {
  const data = readEntry(KEYS.sitePages)
  return data && typeof data === 'object' ? data : null
}

export function writeSitePagesCache(sitePages) {
  if (!sitePages || typeof sitePages !== 'object') return
  writeEntry(KEYS.sitePages, sitePages)
  Object.entries(sitePages).forEach(([slug, page]) => {
    if (page && typeof page === 'object') {
      writeEntry(KEYS.sitePage(slug), page)
    }
  })
}

export function readSitePageCache(slug) {
  if (!slug) return null

  const fromMap = readSitePagesCache()?.[slug]
  if (fromMap && typeof fromMap === 'object') return fromMap

  const data = readEntry(KEYS.sitePage(slug))
  return data && typeof data === 'object' ? data : null
}

export function writeSitePageCache(slug, page) {
  if (!slug || !page || typeof page !== 'object') return
  writeEntry(KEYS.sitePage(slug), page)

  const existing = readSitePagesCache() ?? {}
  writeSitePagesCache({ ...existing, [slug]: page })
}

export function readBlogPostsCache() {
  const data = readEntry(KEYS.blogPosts)
  return Array.isArray(data) ? data : null
}

export function writeBlogPostsCache(posts) {
  if (!Array.isArray(posts)) return
  writeEntry(KEYS.blogPosts, posts)
  posts.forEach((post) => {
    if (post?.slug) writeEntry(KEYS.blogPost(post.slug), post)
  })
}

export function readBlogPostCache(slug) {
  if (!slug) return null

  const fromList = readBlogPostsCache()?.find((post) => post?.slug === slug)
  if (fromList) return fromList

  const data = readEntry(KEYS.blogPost(slug))
  return data && typeof data === 'object' ? data : null
}

export function writeBlogPostCache(slug, post) {
  if (!slug || !post || typeof post !== 'object') return
  writeEntry(KEYS.blogPost(slug), post)

  const existing = readBlogPostsCache() ?? []
  const next = existing.some((item) => item?.slug === slug)
    ? existing.map((item) => (item?.slug === slug ? post : item))
    : [...existing, post]
  writeEntry(KEYS.blogPosts, next)
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
 * Persist server bootstrap payload into localStorage for instant SPA navigations.
 */
export function seedClientCacheFromBootstrap(bootstrap) {
  if (!bootstrap || typeof bootstrap !== 'object') return

  if (bootstrap.siteContent) writeSiteContentCache(bootstrap.siteContent)
  if (bootstrap.homepage) writeHomepageCache(bootstrap.homepage)
  if (bootstrap.sitePages) writeSitePagesCache(bootstrap.sitePages)
  if (bootstrap.blogPosts) writeBlogPostsCache(bootstrap.blogPosts)
}

/**
 * Warm the browser image cache for hero/logo and other CMS images from bootstrap or storage.
 */
export function preloadSiteAssets(pages, homepage, sitePages, blogPosts) {
  if (typeof window === 'undefined') return

  const urls = new Set()
  collectImageUrls(pages, urls)
  collectImageUrls(homepage, urls)
  collectImageUrls(sitePages, urls)
  collectImageUrls(blogPosts, urls)

  const branding = pages?.global?.branding
  if (branding?.favicon) urls.add(branding.favicon)
  if (branding?.logoImage) urls.add(branding.logoImage)

  urls.forEach((url) => {
    const img = new Image()
    img.decoding = 'async'
    img.src = url
  })
}

export function preloadAllCachedAssets() {
  preloadSiteAssets(
    readSiteContentCache(),
    readHomepageCache(),
    readSitePagesCache(),
    readBlogPostsCache(),
  )
}
