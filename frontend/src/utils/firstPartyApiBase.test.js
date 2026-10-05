import assert from 'node:assert/strict'
import test from 'node:test'
import { firstPartyApiBase } from './firstPartyApiBase.js'

test('both public hostnames use their own origin for the Laravel API', () => {
  for (const host of ['myterrabook.com', 'www.myterrabook.com']) {
    for (const apiHost of ['myterrabook.com', 'www.myterrabook.com']) {
      assert.equal(firstPartyApiBase(`https://${apiHost}/backend/api`, host), '/backend/api')
      assert.equal(firstPartyApiBase(`https://${apiHost}/backend/api/`, host), '/backend/api')
    }
  }
})

test('relative URLs and explicitly different API deployments are preserved', () => {
  for (const url of [
    '/backend/api',
    'https://api.myterrabook.com/api',
    'https://example.com/backend/api',
    'https://myterrabook.com:8443/backend/api',
    'https://myterrabook.com/other/api',
    'http://127.0.0.1:8080/api',
  ]) {
    assert.equal(firstPartyApiBase(url, 'www.myterrabook.com'), url)
  }
})

test('local development and preview hosts retain an explicitly configured API', () => {
  const url = 'https://myterrabook.com/backend/api'
  for (const host of ['localhost', '127.0.0.1', 'preview.example.com']) {
    assert.equal(firstPartyApiBase(url, host), url)
  }
})
