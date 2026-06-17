function decodeBasicEntities(value = '') {
  return String(value)
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&lt;/gi, '<')
    .replace(/&gt;/gi, '>')
    .replace(/&#39;/gi, "'")
    .replace(/&apos;/gi, "'")
    .replace(/&quot;/gi, '"')
}

function htmlToPlainText(value = '') {
  return decodeBasicEntities(value)
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/p>\s*<p[^>]*>/gi, '\n\n')
    .replace(/<\/p>/gi, '\n\n')
    .replace(/<p[^>]*>/gi, '')
    .replace(/<[^>]+>/g, '')
    .replace(/\n{3,}/g, '\n\n')
    .trim()
}

export function formatListingDescription(value = '') {
  const raw = String(value || '').trim()
  if (!raw) return ''

  if (/<[a-z][\s\S]*>/i.test(raw)) {
    return htmlToPlainText(raw)
  }

  return raw
}

export function splitListingDescription(value = '', maxLength = 280) {
  const desc = formatListingDescription(value)
  if (desc.length <= maxLength) {
    return { short: desc, more: '' }
  }

  return {
    short: desc.slice(0, maxLength).trim(),
    more: desc.slice(maxLength).trim(),
  }
}
