import assert from 'node:assert/strict'
import { test } from 'node:test'
import {
  clampPriceFilters,
  computePriceBounds,
  defaultPriceFilters,
  isPriceFilterActive,
  matchesPriceFilter,
} from './searchPriceBounds.js'

// Real daily rates synced from Greenlight, in ISK.
const ISK_CARDS = [
  { sortPrice: 16200 },
  { sortPrice: 10800 },
  { sortPrice: 10320 },
  { sortPrice: 12720 },
]

test('an untouched price filter keeps every listing, whatever the currency scale', () => {
  const filters = defaultPriceFilters()

  assert.equal(filters.minPrice, null)
  assert.equal(filters.maxPrice, null)

  const kept = ISK_CARDS.filter((card) => matchesPriceFilter(card.sortPrice, filters))
  assert.equal(kept.length, ISK_CARDS.length)
})

test('an untouched price filter does not count as active', () => {
  const bounds = computePriceBounds(ISK_CARDS)

  assert.deepEqual(bounds, { min: 10320, max: 16200, step: 10 })
  assert.equal(isPriceFilterActive(defaultPriceFilters(), bounds), false)
})

test('a range the traveller set is applied and counts as active', () => {
  const bounds = computePriceBounds(ISK_CARDS)
  const filters = { minPrice: 10320, maxPrice: 11000 }

  const kept = ISK_CARDS.filter((card) => matchesPriceFilter(card.sortPrice, filters))
  assert.deepEqual(kept.map((c) => c.sortPrice), [10800, 10320])
  assert.equal(isPriceFilterActive(filters, bounds), true)
})

test('clamping leaves an unset range unset', () => {
  const bounds = computePriceBounds(ISK_CARDS)

  assert.deepEqual(clampPriceFilters(defaultPriceFilters(), bounds), {
    minPrice: null,
    maxPrice: null,
  })
})

test('clamping pulls a stale range back inside new bounds', () => {
  const bounds = computePriceBounds(ISK_CARDS)

  assert.deepEqual(clampPriceFilters({ minPrice: 0, maxPrice: 500 }, bounds), {
    minPrice: 10320,
    maxPrice: 10320,
  })
})

test('a listing with no usable price is never filtered out', () => {
  assert.equal(matchesPriceFilter(Number.NaN, { minPrice: 1, maxPrice: 2 }), true)
  assert.equal(matchesPriceFilter(null, { minPrice: 1, maxPrice: 2 }), true)
})
