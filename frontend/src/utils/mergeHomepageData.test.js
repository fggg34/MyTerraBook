import test from 'node:test'
import assert from 'node:assert/strict'
import { usableMobileValue } from './seededMobileFallbacks.js'

test('seeded mobile hero copy is treated as empty so phones use desktop CMS fields', () => {
  assert.equal(usableMobileValue('Iceland road trips, one booking.'), '')
  assert.equal(usableMobileValue('Campervans, 4×4s & guesthouses for the Ring Road.'), '')
  assert.equal(usableMobileValue('/images/homepage/cardcamper.jpg'), '')
  assert.equal(usableMobileValue('Become a Host — start earning today!'), '')
  assert.equal(usableMobileValue('List your van'), '')
})
