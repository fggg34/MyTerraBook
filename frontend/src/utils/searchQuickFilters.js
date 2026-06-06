const GUESTHOUSE_TYPE_LABELS = {
  room: 'Room',
  apartment: 'Apartment',
  villa: 'Villa',
  cottage: 'Cottage',
  chalet: 'Chalet',
  studio: 'Studio',
}

const VEHICLE_ATTRIBUTE_FILTERS = [
  {
    id: '4x4',
    label: '4×4',
    kind: 'attribute',
    match: (car) => /suv|4x4|4wd/i.test(`${car.categoryName || ''} ${car.name || ''}`),
  },
  {
    id: 'auto',
    label: 'Automatic',
    kind: 'attribute',
    match: (car) => /auto/i.test(car.transmission || ''),
  },
  {
    id: 'winter',
    label: 'Winter-ready',
    kind: 'attribute',
    match: (car) => /suv|van|4x4|awd|4wd/i.test(`${car.categoryName || ''} ${car.name || ''} ${car.fuel_type || ''}`),
  },
]

function formatTypeLabel(type) {
  const key = String(type || '').toLowerCase()
  if (GUESTHOUSE_TYPE_LABELS[key]) return GUESTHOUSE_TYPE_LABELS[key]
  if (!key) return 'Stay'
  return key.charAt(0).toUpperCase() + key.slice(1)
}

export function buildGuesthouseQuickFilters(houses = []) {
  const types = [...new Set(
    houses
      .map((house) => String(house.type || '').toLowerCase())
      .filter(Boolean),
  )].sort((a, b) => formatTypeLabel(a).localeCompare(formatTypeLabel(b)))

  return types.map((type) => ({
    id: type,
    label: formatTypeLabel(type),
    kind: 'type',
    match: (card) => String(card.houseType || '').toLowerCase() === type,
  }))
}

export function buildVehicleQuickFilters(cars = []) {
  const attributeFilters = VEHICLE_ATTRIBUTE_FILTERS.filter((filter) => (
    cars.some((car) => filter.match(car))
  ))

  const categories = [...new Set(
    cars
      .map((car) => car.categoryName)
      .filter(Boolean),
  )].sort((a, b) => a.localeCompare(b))

  const categoryFilters = categories.map((name) => ({
    id: `category:${name}`,
    label: name,
    kind: 'category',
    match: (car) => car.categoryName === name,
  }))

  return [...attributeFilters, ...categoryFilters]
}

export function applyQuickFilters(list, quickFilters, quickFilterOptions = []) {
  if (!quickFilters.length || !quickFilterOptions.length) return list

  const active = quickFilterOptions.filter((option) => quickFilters.includes(option.id))
  if (!active.length) return list

  const attributes = active.filter((option) => option.kind === 'attribute')
  const categories = active.filter((option) => option.kind === 'category')
  const types = active.filter((option) => option.kind === 'type')

  let result = list

  attributes.forEach((filter) => {
    result = result.filter((item) => filter.match(item))
  })

  if (categories.length) {
    result = result.filter((item) => categories.some((filter) => filter.match(item)))
  }

  if (types.length) {
    result = result.filter((item) => types.some((filter) => filter.match(item)))
  }

  return result
}

export function pruneQuickFilters(quickFilters, quickFilterOptions = []) {
  const validIds = new Set(quickFilterOptions.map((option) => option.id))
  return quickFilters.filter((id) => validIds.has(id))
}

export function toggleQuickFilter(prev, id, quickFilterOptions = []) {
  const option = quickFilterOptions.find((entry) => entry.id === id)
  if (!option) return prev

  if (prev.includes(id)) {
    return prev.filter((entry) => entry !== id)
  }

  if (option.kind === 'type') {
    const typeIds = new Set(quickFilterOptions.filter((entry) => entry.kind === 'type').map((entry) => entry.id))
    return [...prev.filter((entry) => !typeIds.has(entry)), id]
  }

  return [...prev, id]
}
