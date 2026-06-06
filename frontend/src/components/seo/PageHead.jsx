import { useEffect } from 'react'

const MANAGED_SELECTOR = '[data-seo-managed="true"]'

function upsertMeta(attr, key, content) {
  if (!content && content !== '0') return

  let el = document.head.querySelector(`meta[${attr}="${key}"]`)
  if (!el) {
    el = document.createElement('meta')
    el.setAttribute(attr, key)
    el.dataset.seoManaged = 'true'
    document.head.appendChild(el)
  }

  el.setAttribute('content', content)
}

function upsertLink(rel, href) {
  if (!href) return

  let el = document.head.querySelector(`link[rel="${rel}"]${MANAGED_SELECTOR}`)
  if (!el) {
    el = document.createElement('link')
    el.setAttribute('rel', rel)
    el.dataset.seoManaged = 'true'
    document.head.appendChild(el)
  }

  el.setAttribute('href', href)
}

function removeManagedTags() {
  document.head.querySelectorAll(MANAGED_SELECTOR).forEach((node) => node.remove())
}

export default function PageHead({
  title,
  description,
  ogImage,
  robots = 'index',
  canonical,
  ogUrl,
  siteName = 'MyTerraBook',
}) {
  useEffect(() => {
    const previousTitle = document.title

    if (title) {
      document.title = title
    }

    if (description) {
      upsertMeta('name', 'description', description)
    }

    const robotsValue = robots === 'noindex' ? 'noindex, nofollow' : 'index, follow'
    upsertMeta('name', 'robots', robotsValue)

    if (canonical) {
      upsertLink('canonical', canonical)
    }

    upsertMeta('property', 'og:type', 'website')
    upsertMeta('property', 'og:site_name', siteName)
    if (title) upsertMeta('property', 'og:title', title)
    if (description) upsertMeta('property', 'og:description', description)
    if (ogImage) upsertMeta('property', 'og:image', ogImage)
    if (ogUrl || canonical) upsertMeta('property', 'og:url', ogUrl || canonical)

    upsertMeta('name', 'twitter:card', ogImage ? 'summary_large_image' : 'summary')
    if (title) upsertMeta('name', 'twitter:title', title)
    if (description) upsertMeta('name', 'twitter:description', description)
    if (ogImage) upsertMeta('name', 'twitter:image', ogImage)

    return () => {
      document.title = previousTitle
      removeManagedTags()
    }
  }, [title, description, ogImage, robots, canonical, ogUrl, siteName])

  return null
}
