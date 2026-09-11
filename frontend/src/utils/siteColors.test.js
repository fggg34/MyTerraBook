import test from 'node:test'
import assert from 'node:assert/strict'
import { mergeSiteColors, normalizeHex, siteColorCss } from './siteColors.js'

test('normalizeHex expands short values and rejects invalid colors', () => {
  assert.equal(normalizeHex('#0F2036'), '#0f2036')
  assert.equal(normalizeHex('123'), '#112233')
  assert.equal(normalizeHex('navy'), null)
})

test('mergeSiteColors keeps defaults when a CMS value is empty', () => {
  const merged = mergeSiteColors({ navy: '#112233', green: '' })

  assert.equal(merged.navy, '#112233')
  assert.equal(merged.green, '#45a06a')
})

test('siteColorCss writes the shared storefront tokens', () => {
  const css = siteColorCss({ navy: '#112233' })

  assert.match(css, /--navy: #112233 !important/)
  assert.match(css, /--green: #45a06a !important/)
})
