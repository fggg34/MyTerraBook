import assert from 'node:assert/strict'
import { test } from 'node:test'
import { webcrypto } from 'node:crypto'
import { carenCheckoutToken } from './carenCheckoutToken.js'

test('same checkout reuses its token, changed checkout gets a new token, storage excludes customer data', async () => {
  const values = new Map()
  Object.defineProperty(globalThis, 'crypto', { value: webcrypto, configurable: true })
  globalThis.sessionStorage = { getItem: (key) => values.get(key), setItem: (key, value) => values.set(key, value) }
  const payload = { car_id: 123, customer_email: 'customer@example.test', expected_total_cents: 75000 }
  const first = await carenCheckoutToken(payload)
  assert.equal(await carenCheckoutToken({ ...payload }), first)
  assert.notEqual(await carenCheckoutToken({ ...payload, expected_total_cents: 80000 }), first)
  assert.ok(![...values.values()].join('').includes('customer@example.test'))
})
