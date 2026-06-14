const DEFAULT_SUFFIX = ' | MyTerraBook'

export function stripHtml(value = '') {
  return String(value)
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

export function truncateDescription(value = '', max = 160) {
  const text = stripHtml(value)
  if (text.length <= max) return text
  return `${text.slice(0, max - 1).trim()}…`
}

export function getSiteBaseUrl() {
  if (typeof window !== 'undefined' && window.location?.origin) {
    return window.location.origin.replace(/\/$/, '')
  }

  const envUrl = typeof import.meta !== 'undefined' ? import.meta.env?.VITE_APP_URL : undefined
  if (envUrl) return String(envUrl).replace(/\/$/, '')

  return ''
}

export function buildCanonical(pathname = '/') {
  const base = getSiteBaseUrl()
  const path = pathname.startsWith('/') ? pathname : `/${pathname}`
  return base ? `${base}${path}` : path
}

function includesSiteName(title, siteName) {
  if (!title || !siteName) return false
  return title.toLowerCase().includes(siteName.toLowerCase())
}

function appendSuffix(title, suffix, siteName) {
  if (!title) return title
  if (!suffix || includesSiteName(title, siteName)) return title
  return `${title}${suffix}`
}

export function resolveListingCategoryLabel(listingType) {
  switch (listingType) {
    case 'campervan':
      return 'Campervan'
    case 'car':
      return 'Car & 4×4'
    case 'guesthouse':
      return 'Guesthouse'
    default:
      return 'Listing'
  }
}

export function resolveAutoTitle(source = {}, globalSeo = {}) {
  if (source.name && source.listingType) {
    const category = resolveListingCategoryLabel(source.listingType)
    return `${source.name}, ${category} in Iceland`
  }

  if (source.title) return source.title
  if (source.heading) return source.heading
  if (source.titleLead) return source.titleLead
  if (source.hero?.title) return source.hero.title
  if (source.header?.title) return source.header.title
  if (source.content?.title) return source.content.title

  return globalSeo.siteName || 'MyTerraBook'
}

export function resolveAutoDescription(source = {}) {
  if (source.meta_description) return source.meta_description
  if (source.description) return truncateDescription(source.description)
  if (source.short_description) return truncateDescription(source.short_description)
  if (source.excerpt) return truncateDescription(source.excerpt)
  if (source.subtitle) return truncateDescription(source.subtitle)
  if (source.lead) return truncateDescription(source.lead)
  if (source.hero?.lead) return truncateDescription(source.hero.lead)
  if (source.header?.lead) return truncateDescription(source.header.lead)
  if (source.content?.subtitle) return truncateDescription(source.content.subtitle)

  return ''
}

export function resolveAutoImage(source = {}) {
  return (
    source.og_image ||
    source.ogImage ||
    source.featured_image ||
    source.main_image_path ||
    source.thumbnail ||
    source.backgroundImage ||
    source.hero?.image ||
    ''
  )
}

export function resolveAbsoluteUrl(url, base = getSiteBaseUrl()) {
  if (!url) return ''
  const value = String(url)
  if (value.startsWith('http://') || value.startsWith('https://')) return value
  if (value.startsWith('//')) return `https:${value}`
  if (base) return `${base}${value.startsWith('/') ? value : `/${value}`}`
  return value
}

/**
 * Merge page/entity SEO with global defaults and auto-fallbacks.
 */
export function resolveSeo({
  globalSeo = {},
  pageSeo = {},
  source = {},
  pathname = '/',
  robots,
} = {}) {
  const siteName = globalSeo.siteName || 'MyTerraBook'
  const titleSuffix = globalSeo.titleSuffix ?? DEFAULT_SUFFIX

  const rawTitle = pageSeo.title || source.meta_title || resolveAutoTitle(source, globalSeo)
  const title = appendSuffix(rawTitle, titleSuffix, siteName)

  const description =
    pageSeo.description ||
    source.meta_description ||
    resolveAutoDescription(source) ||
    globalSeo.defaultDescription ||
    ''

  const ogImage = resolveAbsoluteUrl(
    pageSeo.ogImage || resolveAutoImage(source) || globalSeo.defaultOgImage || '',
  )
  const canonical = buildCanonical(pathname)
  const resolvedRobots = robots ?? pageSeo.robots ?? 'index'

  return {
    title,
    description: truncateDescription(description),
    ogImage: ogImage ? String(ogImage) : '',
    robots: resolvedRobots,
    canonical,
    ogUrl: canonical,
    siteName,
  }
}
