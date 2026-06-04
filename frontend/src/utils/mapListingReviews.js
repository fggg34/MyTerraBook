import { resolveStorageUrl } from '../api'

export function formatReviewApiDate(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
}

export function mapApiListingReviews(apiReviews = []) {
  return (apiReviews || []).map((r) => {
    const body = r.body || ''
    const name = r.guest_name || 'Guest'
    return {
      id: String(r.id),
      name,
      initial: (name.trim()[0] || 'G').toUpperCase(),
      date: formatReviewApiDate(r.created_at),
      score: Number(r.rating).toFixed(1),
      text: body,
      photoUrl: r.photo_url || (r.photo_path ? resolveStorageUrl(r.photo_path) : null),
      clamp: body.length > 180,
    }
  })
}
