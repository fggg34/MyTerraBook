import { formatDateTimeLocal, parseDateTimeLocal } from './format'

export function computeRentalDays(pickupAt, dropoffAt) {
  if (!pickupAt || !dropoffAt) return 0
  const durationMinutes = (dropoffAt.getTime() - pickupAt.getTime()) / 60000
  return Math.max(1, Math.ceil(durationMinutes / 1440))
}

export function computeMinDropoffDate(pickupAt, minRentalDays = 1) {
  if (!pickupAt) return null
  if (!minRentalDays || minRentalDays <= 1) return new Date(pickupAt)
  const d = new Date(pickupAt)
  d.setDate(d.getDate() + minRentalDays)
  return d
}

export function isDropoffDateAllowed(pickupAt, candidateDate, minRentalDays = 1) {
  if (!pickupAt || !candidateDate) return true

  const dropoffAt = new Date(candidateDate)
  dropoffAt.setHours(pickupAt.getHours(), pickupAt.getMinutes(), 0, 0)

  if (dropoffAt <= pickupAt) return false
  return computeRentalDays(pickupAt, dropoffAt) >= (minRentalDays || 1)
}

export function dropoffFilterDate(pickupAt, minRentalDays = 1) {
  return (candidateDate) => isDropoffDateAllowed(pickupAt, candidateDate, minRentalDays)
}

export function ensureValidDropoff(pickupAt, dropoffAt, minRentalDays = 1) {
  const pickup = pickupAt instanceof Date ? pickupAt : parseDateTimeLocal(pickupAt)
  if (!pickup) return typeof dropoffAt === 'string' ? dropoffAt : ''

  const dropoff = dropoffAt instanceof Date ? dropoffAt : parseDateTimeLocal(dropoffAt)
  const minDays = minRentalDays || 1

  if (!dropoff || !isDropoffDateAllowed(pickup, dropoff, minDays)) {
    const minDrop = computeMinDropoffDate(pickup, minDays > 1 ? minDays : 1)
    if (minDays <= 1) {
      minDrop.setDate(minDrop.getDate() + 1)
    }
    return formatDateTimeLocal(minDrop)
  }

  if (dropoff <= pickup) {
    const minDrop = computeMinDropoffDate(pickup, minDays > 1 ? minDays : 1)
    if (minDays <= 1) {
      minDrop.setDate(minDrop.getDate() + 1)
    }
    return formatDateTimeLocal(minDrop)
  }

  return typeof dropoffAt === 'string' ? dropoffAt : formatDateTimeLocal(dropoff)
}

export function computeMaxDropoffDate(pickupAt, maxRentalDays) {
  if (!pickupAt || !maxRentalDays) return null
  return computeMinDropoffDate(pickupAt, maxRentalDays)
}

export function minRentalHint(minRentalDays) {
  if (!minRentalDays || minRentalDays <= 1) return null
  return `Minimum rental: ${minRentalDays} days`
}
