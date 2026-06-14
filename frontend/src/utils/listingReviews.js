export function formatReviewDate(date = new Date()) {
  return date.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })
}

export function buildUserReview({ name, score, text, photoUrl }) {
  const trimmed = name.trim()
  const initial = (trimmed[0] || 'G').toUpperCase()
  const body = text.trim()
  return {
    id: `user-${Date.now()}`,
    name: trimmed || 'Guest',
    initial,
    score: Number(score).toFixed(1),
    date: formatReviewDate(),
    text: body,
    photoUrl: photoUrl || null,
    clamp: body.length > 180,
    isUser: true,
  }
}

export function withReviewIds(reviews) {
  return reviews.map((r, i) => ({
    ...r,
    id: r.id || `seed-${i}-${r.name}`,
  }))
}

export function mergeReviews(baseReviews, userReviews) {
  return [...userReviews, ...withReviewIds(baseReviews)]
}

/** Guest photos only from reviews that include an uploaded image (no listing/demo URLs). */
export function guestPhotosFromReviews(reviews) {
  const urls = []
  const seen = new Set()
  for (const r of reviews) {
    if (!r.photoUrl || seen.has(r.photoUrl)) continue
    seen.add(r.photoUrl)
    urls.push(r.photoUrl)
  }
  return urls
}

export function computeRating(baseRating, allReviews) {
  if (!allReviews.length) {
    return {
      ...baseRating,
      score: ',',
      label: 'No reviews yet',
      reviewCount: 0,
      reviewLinkLabel: 'Be the first to review',
    }
  }
  const scores = allReviews.map((r) => Number.parseFloat(r.score)).filter((n) => !Number.isNaN(n))
  if (!scores.length) return baseRating
  const avg = scores.reduce((a, b) => a + b, 0) / scores.length
  const count = allReviews.length
  let label = 'Good'
  if (avg >= 4.8) label = 'Excellent'
  else if (avg >= 4.5) label = 'Great'
  else if (avg >= 4) label = 'Good'
  else if (avg >= 3) label = 'Fair'
  else label = 'Average'
  return {
    ...baseRating,
    score: avg.toFixed(1),
    label,
    reviewCount: count,
    reviewLinkLabel: `${count} review${count !== 1 ? 's' : ''}`,
  }
}

/** Featured tile: first guest review photo only (never listing gallery images). */
export function pickFeatureImage(reviews) {
  const withPhoto = reviews.find((r) => r.photoUrl)
  return withPhoto?.photoUrl || null
}

/** Duplicate tiles enough times for a smooth marquee loop (HTML uses two identical sets). */
export function buildMarqueePhotos(urls) {
  if (!urls.length) return []
  const minTiles = 6
  const out = []
  while (out.length < minTiles) {
    out.push(...urls)
  }
  return [...out, ...out]
}
