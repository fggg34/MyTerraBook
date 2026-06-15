/**
 * Share the current page via the Web Share API, or copy the URL to the clipboard.
 * @returns {{ ok: boolean, method?: 'share' | 'clipboard', aborted?: boolean }}
 */
export async function sharePage({ url, title, text } = {}) {
  const shareUrl = url || window.location.href
  const shareTitle = title || document.title
  const shareText = text || shareTitle

  if (navigator.share) {
    try {
      await navigator.share({ url: shareUrl, title: shareTitle, text: shareText })
      return { ok: true, method: 'share' }
    } catch (err) {
      if (err?.name === 'AbortError') return { ok: false, aborted: true }
    }
  }

  try {
    await navigator.clipboard.writeText(shareUrl)
    return { ok: true, method: 'clipboard' }
  } catch {
    return { ok: false }
  }
}
