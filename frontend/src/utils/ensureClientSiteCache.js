import { resolveApiBaseUrl } from '../api'
import {
  preloadAllCachedAssets,
  preloadSiteAssets,
  seedClientCacheFromBootstrap,
} from './siteContentCache'
import {
  getBootstrappedBlogPosts,
  getBootstrappedHomepage,
  getBootstrappedSiteContent,
  getBootstrappedSitePages,
  hasInstantSiteData,
  readSiteBootstrap,
} from './siteBootstrap'

function resolveBootstrapApiUrl() {
  return `${resolveApiBaseUrl()}/bootstrap`
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

  // No server bootstrap, so the cache is all this device has. Paint from it at
  // once, then always revalidate behind it. This used to refresh only when
  // something was missing, which left a complete but stale cache on screen
  // until it expired days later.
  if (hasInstantSiteData()) {
    preloadAllCachedAssets()
    fetchBootstrapPayload().then((payload) => {
      if (payload) seedClientCacheFromBootstrap(payload)
    })
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
