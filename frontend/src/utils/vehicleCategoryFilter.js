/**
 * Match a vehicle listing against the storefront main category slug.
 */
export function mainCategoryMatchesVehicleType(mainCategorySlug, vehicleType) {
  if (!vehicleType || vehicleType === 'guesthouse') return true
  const normalized = String(mainCategorySlug || '').trim().toLowerCase()
  if (!normalized) return false
  return normalized === String(vehicleType).trim().toLowerCase()
}

/**
 * @deprecated Use mainCategoryMatchesVehicleType with main_category_slug from the API.
 */
export function categoryMatchesVehicleType(categoryName, allowedNames) {
  if (!allowedNames?.length) return true
  const normalized = String(categoryName || '').trim().toLowerCase()
  if (!normalized) return false
  return allowedNames.some((name) => String(name).trim().toLowerCase() === normalized)
}
