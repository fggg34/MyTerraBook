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

export function mergeGuestPhotos(userReviews, baseUrls) {
  const fromReviews = userReviews.filter((r) => r.photoUrl).map((r) => r.photoUrl)
  const seen = new Set()
  const merged = []
  for (const url of [...fromReviews, ...baseUrls]) {
    if (!url || seen.has(url)) continue
    seen.add(url)
    merged.push(url)
  }
  return merged
}

export function computeRating(baseRating, allReviews) {
  if (!allReviews.length) return baseRating
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

export function pickFeatureImage(userReviews, guestPhotos, listingImages) {
  const withPhoto = userReviews.find((r) => r.photoUrl)
  if (withPhoto?.photoUrl) return withPhoto.photoUrl
  if (guestPhotos[0]) return guestPhotos[0]
  return listingImages[0]?.url || '/images/homepage/cardcamper.jpg'
}
