import assert from 'node:assert/strict'
import { test } from 'node:test'
import { carenPriceLabel, formatCarenPrice } from './carenPricing.js'

test('provider money is converted only when it matches the formatter base currency', () => {
  const formatter = { baseCurrency: 'EUR', format: (value) => `converted:${value}` }
  assert.equal(formatCarenPrice(123, 'EUR', formatter), 'converted:123')
  const isk = formatCarenPrice(123456, 'ISK', formatter)
  assert.ok(isk.includes('ISK'))
  assert.ok(isk.includes('123,456'))
  assert.ok(!isk.includes('converted'))
})

test('missing dates, missing rates and failures never advertise a free rental', () => {
  assert.equal(carenPriceLabel('needs_dates'), 'Select dates for price')
  assert.equal(carenPriceLabel('unavailable'), 'No rate for these dates')
  assert.equal(carenPriceLabel('error'), 'Price temporarily unavailable')
})
