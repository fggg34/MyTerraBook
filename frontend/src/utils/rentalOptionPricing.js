export function findCatalogRentalOption(catalogOptions, optionId) {
  return catalogOptions.find((option) => String(option.id) === String(optionId))
}

/** Host override when set, otherwise catalog default. */
export function resolveRentalOptionIsDailyCost(hostRow, catalogOption) {
  if (hostRow?.is_daily_cost != null) return !!hostRow.is_daily_cost
  return !!catalogOption?.is_daily_cost
}

export function defaultRentalOptionAmountEuros(catalogOption) {
  return catalogOption?.cost_euros
    ?? (catalogOption?.cost_cents ?? catalogOption?.default_cost_cents ?? 0) / 100
}

export function hostRentalOptionPriceSuffix(isDailyCost) {
  return isDailyCost ? '/ day' : 'flat'
}

export function guestRentalOptionSubLabel(isDailyCost) {
  return isDailyCost ? 'Per day' : 'One-time'
}

export function calculateRentalOptionTotalCents(unitCents, isDailyCost, days, quantity = 1) {
  const qty = Math.max(1, quantity)
  const rentalDays = Math.max(1, days)
  return isDailyCost ? unitCents * rentalDays * qty : unitCents * qty
}

export function normalizeHostRentalOptionFromApi(row) {
  return {
    id: row.id,
    cost_euros: row.cost_euros ?? (row.cost_cents ?? 0) / 100,
    is_daily_cost: row.is_daily_cost != null ? !!row.is_daily_cost : undefined,
  }
}

export function buildHostRentalOptionSyncPayload(row, catalogOptions = []) {
  const catalogOption = findCatalogRentalOption(catalogOptions, row.id)
  return {
    id: row.id,
    cost_euros: Number(row.cost_euros) || 0,
    is_daily_cost: resolveRentalOptionIsDailyCost(row, catalogOption),
  }
}
