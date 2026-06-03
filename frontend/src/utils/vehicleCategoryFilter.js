/**
 * Match a backend category name against allowed storefront labels (case-insensitive).
 */
export function categoryMatchesVehicleType(categoryName, allowedNames) {
  if (!allowedNames?.length) return true
  const normalized = String(categoryName || '').trim().toLowerCase()
  if (!normalized) return false
  return allowedNames.some((name) => String(name).trim().toLowerCase() === normalized)
}
