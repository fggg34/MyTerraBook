import test from 'node:test'
import assert from 'node:assert/strict'
import { mergePageContent } from './mergePageContent.js'

test('mergePageContent keeps cleared review texts and does not pad demo cards', () => {
  const merged = mergePageContent(
    {
      reviewsSection: {
        heading: 'Loved on the Ring Road.',
        reviews: [
          { quote: 'Demo quote', name: 'Marta' },
          { quote: 'Another demo', name: 'Tom' },
        ],
      },
    },
    {
      reviewsSection: {
        heading: '',
        reviews: [{ quote: 'Custom stay', name: 'Ada' }],
      },
    },
  )

  assert.equal(merged.reviewsSection.heading, '')
  assert.equal(merged.reviewsSection.reviews.length, 1)
  assert.equal(merged.reviewsSection.reviews[0].name, 'Ada')
})

test('mergePageContent keeps an empty reviews list from the CMS', () => {
  const merged = mergePageContent(
    {
      reviewsSection: {
        heading: 'Loved on the Ring Road.',
        reviews: [{ quote: 'Demo quote', name: 'Marta' }],
      },
    },
    {
      reviewsSection: {
        heading: '',
        reviews: [],
      },
    },
  )

  assert.equal(merged.reviewsSection.heading, '')
  assert.deepEqual(merged.reviewsSection.reviews, [])
})
