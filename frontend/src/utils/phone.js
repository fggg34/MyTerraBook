import { isValidPhoneNumber, parsePhoneNumber } from 'libphonenumber-js'

export function validatePhone(value, { required = true } = {}) {
  const trimmed = String(value || '').trim()
  if (!trimmed) return required ? 'Phone is required' : null
  if (!isValidPhoneNumber(trimmed)) return 'Enter a valid phone number'
  return null
}

export function formatPhoneForApi(value) {
  const trimmed = String(value || '').trim()
  if (!trimmed) return ''
  try {
    const parsed = parsePhoneNumber(trimmed)
    return parsed ? parsed.format('E.164') : trimmed
  } catch {
    return trimmed
  }
}
