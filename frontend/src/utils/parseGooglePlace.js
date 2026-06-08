function getComponent(components, type) {
  return components.find((c) => c.types.includes(type))
}

function getComponentName(components, ...types) {
  for (const type of types) {
    const match = getComponent(components, type)
    if (match?.long_name) return match.long_name
  }
  return ''
}

/**
 * Extract listing location fields from a Google Places result.
 */
export function parseGooglePlace(place) {
  const components = place?.address_components || []
  const streetNumber = getComponentName(components, 'street_number')
  const route = getComponentName(components, 'route')
  const streetLine = [streetNumber, route].filter(Boolean).join(' ').trim()

  const city = getComponentName(
    components,
    'locality',
    'postal_town',
    'administrative_area_level_2',
    'administrative_area_level_1',
  )

  const country = getComponentName(components, 'country')

  const address =
    streetLine ||
    place?.name ||
    (place?.formatted_address || '').split(',')[0]?.trim() ||
    place?.formatted_address ||
    ''

  const lat = place?.geometry?.location?.lat?.()
  const lng = place?.geometry?.location?.lng?.()

  return {
    address,
    city,
    country,
    latitude: typeof lat === 'number' ? lat : null,
    longitude: typeof lng === 'number' ? lng : null,
    formattedAddress: place?.formatted_address || '',
  }
}

export function formatLocationLine({ address, city, country }) {
  return [address, city, country].filter(Boolean).join(' · ')
}

export function buildGoogleMapsUrl({ latitude, longitude, address, city, country }) {
  if (latitude != null && longitude != null) {
    return `https://www.google.com/maps?q=${latitude},${longitude}`
  }
  const query = [address, city, country].filter(Boolean).join(', ')
  if (!query) return null
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`
}

export function buildStaticMapUrl({ latitude, longitude, mapsApiKey, width = 640, height = 280, zoom = 14 }) {
  if (!mapsApiKey || latitude == null || longitude == null) return null
  const params = new URLSearchParams({
    center: `${latitude},${longitude}`,
    zoom: String(zoom),
    size: `${width}x${height}`,
    maptype: 'roadmap',
    markers: `color:red|${latitude},${longitude}`,
    key: mapsApiKey,
  })
  return `https://maps.googleapis.com/maps/api/staticmap?${params.toString()}`
}
