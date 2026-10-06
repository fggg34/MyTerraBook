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

export function destinationLocationPath(countryCode, location) {
  const type = location?.vehicle_type === 'campervan' ? 'campervans' : 'cars'
  const params = new URLSearchParams({
    country_code: countryCode,
    pickup_location_id: String(location?.id || ''),
  })
  return '/' + type + '?' + params.toString()
}

export function partnerDestinationGroups(locations = []) {
  const groups = []
  const index = new Map()
  locations.forEach((location) => {
    if (!location?.id || !location?.name || !location?.partner_name) return
    const key = location.partner_name
    if (!index.has(key)) {
      const group = { name: key, locations: [] }
      index.set(key, group)
      groups.push(group)
    }
    index.get(key).locations.push(location)
  })
  return groups
}