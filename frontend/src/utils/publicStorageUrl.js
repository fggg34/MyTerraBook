function normalizeStoragePath(path) {
  if (path == null || path === '') return ''
  if (Array.isArray(path)) {
    const first = path.find((item) => item != null && item !== '')
    return first == null ? '' : String(first).trim()
  }
  return String(path).trim()
}

export function resolvePublicStorageUrl(path, apiBase) {
  const p = normalizeStoragePath(path)
  if (!p) return ''
  // Bundled frontend assets (Vite public folder)
  if (p.startsWith('/images/')) return p
  const appBase = apiBase.replace(/\/api\/?$/i, '')
  // Rewrite API absolute storage URLs to the current app base (fixes wrong APP_URL on live).
  if (/^https?:\/\//i.test(p)) {
    const storageMatch = p.match(/\/storage\/(.+)$/i)
    if (storageMatch) {
      return `${appBase}/storage/${storageMatch[1]}`
    }
    return p
  }
  // A CMS URL is resolved during data mapping and again by CmsImage.
  // Already-resolved root-relative paths must not receive a second prefix.
  const storagePrefix = `${appBase}/storage/`
  if (p.startsWith(storagePrefix)) return p
  if (p.startsWith('/storage/')) {
    return `${appBase}${p}`
  }
  return `${appBase}/storage/${p.replace(/^\/+/, '')}`
}

