// Keep retries of the same checkout idempotent, including after a page reload.
// Store only a digest and random token, never customer or licence details.
export async function carenCheckoutToken(payload) {
  const bytes = new TextEncoder().encode(JSON.stringify(payload))
  const digest = await crypto.subtle.digest('SHA-256', bytes)
  const fingerprint = Array.from(new Uint8Array(digest), (byte) => byte.toString(16).padStart(2, '0')).join('')
  const key = `caren-checkout:${payload.car_id}`
  const saved = JSON.parse(sessionStorage.getItem(key) || 'null')
  if (saved?.fingerprint === fingerprint && saved?.token) return saved.token
  const token = crypto.randomUUID()
  sessionStorage.setItem(key, JSON.stringify({ fingerprint, token }))
  return token
}
