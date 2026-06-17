import { describe, expect, it } from 'vitest'
import { formatListingDescription, splitListingDescription } from './formatListingDescription'

describe('formatListingDescription', () => {
  it('preserves blank lines in plain text', () => {
    const input = 'First paragraph.\n\nSecond paragraph.'
    expect(formatListingDescription(input)).toBe(input)
  })

  it('converts RichEditor HTML paragraphs to blank lines', () => {
    const input = '<p>First paragraph.</p><p>Second paragraph.</p>'
    expect(formatListingDescription(input)).toBe('First paragraph.\n\nSecond paragraph.')
  })

  it('converts br tags to line breaks', () => {
    const input = '<p>Line one<br>Line two</p>'
    expect(formatListingDescription(input)).toBe('Line one\nLine two')
  })
})

describe('splitListingDescription', () => {
  it('splits long descriptions for read more', () => {
    const desc = 'a'.repeat(300)
    const { short, more } = splitListingDescription(desc)
    expect(short).toHaveLength(280)
    expect(more).toHaveLength(20)
  })
})
