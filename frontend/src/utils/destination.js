export function destinationSlug(name = '') {
  return String(name)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

export function destinationLandingPath(destination) {
  const name = typeof destination === 'string' ? destination : destination?.name
  return '/' + destinationSlug(name) + '-campervan-rental'
}

export function destinationSearchPath(countryCode) {
  return '/campervans?country_code=' + encodeURIComponent(countryCode)
}