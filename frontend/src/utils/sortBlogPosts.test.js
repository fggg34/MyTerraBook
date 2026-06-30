import test from 'node:test'
import assert from 'node:assert/strict'
import { sortBlogPosts } from './sortBlogPosts.js'

test('sortBlogPosts puts featured posts first', () => {
  const posts = [
    { slug: 'b', is_featured: false, sort_order: 0 },
    { slug: 'a', is_featured: true, sort_order: 2 },
    { slug: 'c', is_featured: false, sort_order: 1 },
  ]

  assert.deepEqual(
    sortBlogPosts(posts).map((post) => post.slug),
    ['a', 'b', 'c'],
  )
})

test('sortBlogPosts respects sort_order within featured and non-featured groups', () => {
  const posts = [
    { slug: 'd', is_featured: false, sort_order: 4, published_at: '2026-01-01' },
    { slug: 'b', is_featured: true, sort_order: 2, published_at: '2026-01-01' },
    { slug: 'a', is_featured: true, sort_order: 1, published_at: '2026-01-01' },
    { slug: 'c', is_featured: false, sort_order: 3, published_at: '2026-01-01' },
  ]

  assert.deepEqual(
    sortBlogPosts(posts).map((post) => post.slug),
    ['a', 'b', 'c', 'd'],
  )
})

test('sortBlogPosts falls back to published_at when sort_order matches', () => {
  const posts = [
    { slug: 'old', is_featured: false, sort_order: 1, published_at: '2025-01-01' },
    { slug: 'new', is_featured: false, sort_order: 1, published_at: '2026-06-01' },
  ]

  assert.deepEqual(
    sortBlogPosts(posts).map((post) => post.slug),
    ['new', 'old'],
  )
})
