const STOREFRONT_HOSTS = new Set(['myterrabook.com', 'www.myterrabook.com'])

// Both public hostnames serve Laravel. Keep requests on the visitor's origin
// even when a production build pins VITE_API_URL to the other hostname.
export function firstPartyApiBase(candidate, hostname) {
  if (!STOREFRONT_HOSTS.has(hostname)) return candidate

  try {
    const url = new URL(candidate)
    if (
      STOREFRONT_HOSTS.has(url.hostname)
      && !url.port
      && /^https?:$/.test(url.protocol)
      && /^\/backend\/api\/?$/.test(url.pathname)
    ) {
      return `${url.pathname.replace(/\/$/, '')}${url.search}${url.hash}`
    }
  } catch {
    // Relative URLs already use the current origin.
  }

  return candidate
}
