function gearboxLabel(transmission) {
  const value = String(transmission || '').toLowerCase()
  if (value.includes('manual')) return 'MANUAL'
  if (value.includes('auto')) return 'AUTO'
  return String(transmission || 'AUTO').toUpperCase()
}

const DRIVE_LABELS = {
  fwd: 'FWD',
  rwd: 'RWD',
  awd: 'AWD',
  '4wd': '4×4',
}

export function driveLabel(driveType) {
  const key = String(driveType || '').toLowerCase()
  return DRIVE_LABELS[key] || String(driveType || '').toUpperCase()
}

export function isCampervanListing(item) {
  const slug = item?.main_category_slug
    || item?.main_category?.slug
    || item?.category?.main_category_slug
  return slug === 'campervan'
}

/**
 * Build the four product-card spec chips for cars and campervans.
 * Cars: transmission, drive, seats, bags.
 * Campervans: transmission, drive, seats, sleeps.
 */
export function buildVehicleCardSpecs(item, { isCampervan } = {}) {
  const camper = isCampervan ?? isCampervanListing(item)
  const specs = []

  if (item?.transmission) {
    specs.push({ type: 'gearbox', label: gearboxLabel(item.transmission) })
  }
  if (item?.drive_type) {
    specs.push({ type: 'drive', label: driveLabel(item.drive_type) })
  }
  if (item?.seats != null) {
    specs.push({ type: 'seat', label: `Seats ${item.seats}` })
  }
  if (camper) {
    const sleeps = item?.sleeps ?? null
    if (sleeps != null && sleeps > 0) {
      specs.push({ type: 'bed', label: `Sleeps ${sleeps}` })
    }
  } else if (item?.bags != null) {
    specs.push({ type: 'bag', label: `${item.bags} Bag${item.bags === 1 ? '' : 's'}` })
  }

  return specs
}

export { gearboxLabel }
