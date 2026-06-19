import {
  preloadAllCachedAssets,
  preloadSiteAssets,
  readBlogPostsCache,
  readHomepageCache,
  readSitePagesCache,
  seedClientCacheFromBootstrap,
} from './siteContentCache'
import {
  getBootstrappedBlogPosts,
  getBootstrappedHomepage,
  getBootstrappedSiteContent,
  getBootstrappedSitePages,
  getInstantHomepage,
  hasInstantSiteData,
  readSiteBootstrap,
} from './siteBootstrap'

function resolveBootstrapApiUrl() {
  const fromEnv = import.meta.env.VITE_API_URL
  if (fromEnv) {
    return `${fromEnv.replace(/\/$/, '')}/bootstrap`
  }
  if (import.meta.env.PROD) {
    return '/backend/api/bootstrap'
  }
  return 'http://127.0.0.1:8080/api/bootstrap'
}

function isHomepageCacheComplete() {
  const homepage = readHomepageCache() ?? getInstantHomepage()
  return Boolean(homepage?.hero?.heading || homepage?.hero?.backgroundImage)
}

function needsBootstrapTopUp() {
  return (
    !readSitePagesCache()?.about
    || !readBlogPostsCache()?.length
    || !isHomepageCacheComplete()
  )
}

export async function ensureClientSiteCache() {
  const bootstrap = readSiteBootstrap()
  if (bootstrap) {
    seedClientCacheFromBootstrap(bootstrap)
    preloadSiteAssets(
      bootstrap.siteContent ?? getBootstrappedSiteContent(),
      bootstrap.homepage ?? getBootstrappedHomepage(),
      bootstrap.sitePages ?? getBootstrappedSitePages(),
      bootstrap.blogPosts ?? getBootstrappedBlogPosts(),
    )
    return bootstrap
  }

  if (hasInstantSiteData()) {
    preloadAllCachedAssets()
    if (needsBootstrapTopUp()) {
      fetchBootstrapPayload().then((payload) => {
        if (payload) seedClientCacheFromBootstrap(payload)
      })
    }
    return null
  }

  const payload = await fetchBootstrapPayload()
  if (payload) {
    preloadSiteAssets(payload.siteContent, payload.homepage, payload.sitePages, payload.blogPosts)
  }
  return payload
}

async function fetchBootstrapPayload() {
  try {
    const response = await fetch(resolveBootstrapApiUrl(), {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
    })
    if (!response.ok) return null

    const payload = await response.json()
    if (!payload || typeof payload !== 'object') return null

    window.__MYTERRABOOK_BOOTSTRAP__ = payload
    seedClientCacheFromBootstrap(payload)
    return payload
  } catch {
    return null
  }
}
