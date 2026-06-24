export function findCatalogRentalOption(catalogOptions, optionId) {
  return catalogOptions.find((option) => String(option.id) === String(optionId))
}

/** Host override when set, otherwise catalog default. */
export function resolveRentalOptionIsDailyCost(hostRow, catalogOption) {
  if (hostRow?.is_daily_cost != null) return !!hostRow.is_daily_cost
  return !!catalogOption?.is_daily_cost
}

function baseMajorUnitsFromRow(row) {
  if (row?.cost_cents != null) return Number(row.cost_cents) / 100
  if (row?.default_cost_cents != null) return Number(row.default_cost_cents) / 100
  if (row?.cost_euros != null) return Number(row.cost_euros)
  return 0
}

/** Suggested catalog amount in host display currency (major units). */
export function defaultRentalOptionAmountEuros(catalogOption, currencyContext = null) {
  const baseMajor = baseMajorUnitsFromRow(catalogOption)
  if (!currencyContext?.fromBaseAmount) return baseMajor
  return currencyContext.fromBaseAmount(baseMajor)
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

/** Map API row (base currency) into host form state (display currency). */
export function normalizeHostRentalOptionFromApi(row, currencyContext = null) {
  const baseMajor = baseMajorUnitsFromRow(row)
  const displayMajor = currencyContext?.fromBaseAmount
    ? currencyContext.fromBaseAmount(baseMajor)
    : baseMajor

  return {
    id: row.id,
    cost_euros: Math.round(displayMajor * 100) / 100,
    is_daily_cost: row.is_daily_cost != null ? !!row.is_daily_cost : undefined,
  }
}

/** Map host form state (display currency) into API payload (base currency major units). */
export function buildHostRentalOptionSyncPayload(row, catalogOptions = [], currencyContext = null) {
  const catalogOption = findCatalogRentalOption(catalogOptions, row.id)
  const displayMajor = Number(row.cost_euros) || 0
  const baseMajor = currencyContext?.toBaseAmount
    ? currencyContext.toBaseAmount(displayMajor)
    : displayMajor

  return {
    id: row.id,
    cost_euros: Math.round(baseMajor * 100) / 100,
    is_daily_cost: resolveRentalOptionIsDailyCost(row, catalogOption),
  }
}
