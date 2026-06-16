/** Platform protection tiers, hosts set rental rates, not these labels. */
export const PROTECTION_TIER_SLUGS = {
  standard: 'basic',
  enhanced: 'plus',
  full: 'max',
}

export function findPriceType(priceTypes, slug) {
  const target = slug?.toLowerCase()
  return priceTypes.find((pt) => pt.slug?.toLowerCase() === target)
}

export function standardPriceTypeId(priceTypes) {
  const pt = findPriceType(priceTypes, PROTECTION_TIER_SLUGS.standard) || priceTypes[0]
  return pt ? Number(pt.id) : null
}

export const BASE_FARE_FROM_DAYS = 1
export const BASE_FARE_TO_DAYS = 365

export function findBaseDailyFare(standardFares) {
  if (!standardFares.length) return null

  return standardFares.find(
    (f) => f.from_days === BASE_FARE_FROM_DAYS && f.to_days >= BASE_FARE_TO_DAYS,
  ) || standardFares.find((f) => f.from_days === BASE_FARE_FROM_DAYS) || null
}

export function durationTierFares(standardFares) {
  const base = findBaseDailyFare(standardFares)
  if (!base) return [...standardFares].sort((a, b) => a.from_days - b.from_days)

  return standardFares
    .filter((f) => f.id !== base.id)
    .sort((a, b) => a.from_days - b.from_days)
}

export function standardDailyFares(dailyFares, priceTypes) {
  const standardId = standardPriceTypeId(priceTypes)
  if (!standardId) return dailyFares

  return dailyFares.filter((fare) => Number(fare.price_type_id) === standardId)
}

export function filterStandardPriceTypeRows(rows, priceTypes) {
  const standardId = standardPriceTypeId(priceTypes)
  if (!standardId) return rows

  return rows.filter((row) => Number(row.price_type_id) === standardId)
}

export function isBaseDailyPriceDirty(baseDailyPrice, baseDailyFare) {
  const input = String(baseDailyPrice ?? '').trim()
  if (!input) return !!baseDailyFare

  const parsed = Number(input)
  if (!parsed || parsed <= 0) return false

  if (!baseDailyFare) return true

  return Math.abs(parsed - baseDailyFare.price_per_day_cents / 100) > 0.001
}

export function isProtectionAddOnDirty(addOnValue, dailyFares, priceTypes, tierSlug) {
  const saved = inferProtectionAddOnEuros(dailyFares, priceTypes, tierSlug)
  const input = String(addOnValue ?? '').trim()

  if (!input) return saved != null && saved > 0

  const parsed = Number(input)
  if (!parsed || parsed <= 0) return saved != null && saved > 0

  if (saved == null) return true

  return Math.abs(parsed - saved) > 0.001
}

export function hasUnsavedProtectionPricing(plusAddOn, maxAddOn, dailyFares, priceTypes) {
  return isProtectionAddOnDirty(plusAddOn, dailyFares, priceTypes, 'plus')
    || isProtectionAddOnDirty(maxAddOn, dailyFares, priceTypes, 'max')
}

export function inferProtectionAddOnEuros(dailyFares, priceTypes, tierSlug) {
  const standard = findPriceType(priceTypes, PROTECTION_TIER_SLUGS.standard)
  const tier = findPriceType(priceTypes, tierSlug)
  if (!standard || !tier) return null

  const standardFares = dailyFares
    .filter((f) => Number(f.price_type_id) === standard.id)
    .sort((a, b) => a.from_days - b.from_days)
  const tierFares = dailyFares
    .filter((f) => Number(f.price_type_id) === tier.id)
    .sort((a, b) => a.from_days - b.from_days)

  const standardFare = findBaseDailyFare(standardFares) || standardFares[0]
  if (!standardFare || tierFares.length === 0) return null

  const tierFare = tierFares.find(
    (f) => f.from_days === standardFare.from_days && f.to_days === standardFare.to_days,
  ) || tierFares[0]

  return Math.max(0, (tierFare.price_per_day_cents - standardFare.price_per_day_cents) / 100)
}

export async function syncProtectionAddOn(
  recordId,
  tierSlug,
  addOnEuros,
  dailyFares,
  priceTypes,
  api,
) {
  const standard = findPriceType(priceTypes, PROTECTION_TIER_SLUGS.standard)
  const tier = findPriceType(priceTypes, tierSlug)
  if (!standard || !tier) return

  const standardFares = dailyFares.filter((f) => Number(f.price_type_id) === standard.id)
  const existingTierFares = dailyFares.filter((f) => Number(f.price_type_id) === tier.id)
  const addOn = Number(addOnEuros)

  if (!addOn || addOn <= 0 || standardFares.length === 0) {
    await Promise.all(existingTierFares.map((f) => api.deleteHostCarDailyFare(recordId, f.id)))
    return
  }

  const matchedTierIds = new Set()

  for (const standardFare of standardFares) {
    const priceEuros = (standardFare.price_per_day_cents / 100) + addOn
    const existing = existingTierFares.find(
      (f) => f.from_days === standardFare.from_days && f.to_days === standardFare.to_days,
    )
    const payload = {
      price_type_id: tier.id,
      from_days: standardFare.from_days,
      to_days: standardFare.to_days,
      price_per_day_euros: priceEuros,
    }

    if (existing) {
      await api.updateHostCarDailyFare(recordId, existing.id, payload)
      matchedTierIds.add(existing.id)
    } else {
      const res = await api.createHostCarDailyFare(recordId, payload)
      matchedTierIds.add(res.data.data.id)
    }
  }

  for (const fare of existingTierFares) {
    if (!matchedTierIds.has(fare.id)) {
      await api.deleteHostCarDailyFare(recordId, fare.id)
    }
  }
}
