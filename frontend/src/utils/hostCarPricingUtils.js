/** Platform protection tiers, hosts set rental rates, not these labels. */
export const PROTECTION_TIER_SLUGS = {
  standard: 'basic',
  enhanced: 'plus',
  full: 'max',
}

export const PROTECTION_TIER_ORDER = [
  PROTECTION_TIER_SLUGS.standard,
  PROTECTION_TIER_SLUGS.enhanced,
  PROTECTION_TIER_SLUGS.full,
]

export const PROTECTION_TIER_HOST_LABELS = {
  basic: {
    title: 'Standard coverage',
    note: 'Included with your daily rental rate',
  },
  plus: {
    title: 'Enhanced coverage',
    note: 'Lower guest deposit',
  },
  max: {
    title: 'Full coverage',
    note: 'Zero guest deposit',
  },
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

export function catalogProtectionTiers(priceTypes) {
  return PROTECTION_TIER_ORDER
    .map((slug) => {
      const priceType = findPriceType(priceTypes, slug)
      if (!priceType) return null

      return {
        slug,
        priceType,
        isStandard: slug === PROTECTION_TIER_SLUGS.standard,
      }
    })
    .filter(Boolean)
}

export function isProtectionTierOffered(dailyFares, priceTypes, tierSlug) {
  if (tierSlug === PROTECTION_TIER_SLUGS.standard) {
    return findBaseDailyFare(standardDailyFares(dailyFares, priceTypes)) != null
  }

  const tier = findPriceType(priceTypes, tierSlug)
  if (!tier) return false

  return dailyFares.some((fare) => Number(fare.price_type_id) === Number(tier.id))
}

export function readProtectionOffers(dailyFares, priceTypes) {
  return {
    plus: isProtectionTierOffered(dailyFares, priceTypes, PROTECTION_TIER_SLUGS.enhanced),
    max: isProtectionTierOffered(dailyFares, priceTypes, PROTECTION_TIER_SLUGS.full),
  }
}

export function hasUnsavedProtectionOffers(offers, dailyFares, priceTypes) {
  const saved = readProtectionOffers(dailyFares, priceTypes)
  return offers.plus !== saved.plus || offers.max !== saved.max
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

export function hasUnsavedProtectionPricing(offers, plusAddOn, maxAddOn, dailyFares, priceTypes) {
  if (hasUnsavedProtectionOffers(offers, dailyFares, priceTypes)) return true

  if (offers.plus && isProtectionAddOnDirty(plusAddOn, dailyFares, priceTypes, 'plus')) return true
  if (offers.max && isProtectionAddOnDirty(maxAddOn, dailyFares, priceTypes, 'max')) return true

  if (!offers.plus && isProtectionTierOffered(dailyFares, priceTypes, PROTECTION_TIER_SLUGS.enhanced)) return true
  if (!offers.max && isProtectionTierOffered(dailyFares, priceTypes, PROTECTION_TIER_SLUGS.full)) return true

  return false
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
