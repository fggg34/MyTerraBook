import assert from 'node:assert/strict'
import test from 'node:test'
import { resolvePublicStorageUrl } from './publicStorageUrl.js'

test('CMS image mapping followed by CmsImage resolution keeps the same URL', () => {
  const path = 'site-content/home/hero.png'
  for (const base of ['/backend/api', 'https://myterrabook.com/backend/api', 'http://127.0.0.1:8080/api']) {
    const expected = `${base.replace(/\/api$/, '')}/storage/${path}`
    for (const input of [path, [path], `/storage/${path}`, `https://myterrabook.com/backend/storage/${path}`]) {
      const mapped = resolvePublicStorageUrl(input, base)
      assert.equal(mapped, expected)
      assert.equal(resolvePublicStorageUrl(mapped, base), expected)
    }
  }
})

test('root-relative production storage URLs are not prefixed again', () => {
  const path = '/backend/storage/site-content/home/hero.png?v=2'
  assert.equal(resolvePublicStorageUrl(path, '/backend/api'), path)
})

test('empty values, bundled images and external image URLs retain their behavior', () => {
  for (const input of [null, undefined, '', []]) {
    assert.equal(resolvePublicStorageUrl(input, '/backend/api'), '')
  }
  for (const input of ['/images/homepage/hero.jpg', 'https://images.example.com/hero.jpg']) {
    assert.equal(resolvePublicStorageUrl(input, '/backend/api'), input)
  }
})
