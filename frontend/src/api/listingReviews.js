import { api } from '../api'
import { mapApiListingReviews } from '../utils/mapListingReviews'

export function reviewsPathForListing(listingType, id) {
  if (listingType === 'guesthouse') {
    return `/guest-houses/${id}/reviews`
  }
  return `/cars/${id}/reviews`
}

export async function fetchListingReviews(listingType, id) {
  const res = await api.get(reviewsPathForListing(listingType, id))
  return mapApiListingReviews(res.data?.data || [])
}

export async function submitListingReview(listingType, id, { name, score, text, photoFile }) {
  const form = new FormData()
  form.append('guest_name', name.trim() || 'Guest')
  form.append('rating', String(score))
  form.append('body', text.trim())
  if (photoFile) {
    form.append('photo', photoFile)
  }
  const res = await api.post(reviewsPathForListing(listingType, id), form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return res.data?.data
}
