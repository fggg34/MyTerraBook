import { describe, expect, it } from 'vitest'
import { convertFromBase, createPriceFormatter } from '../format'

describe('createPriceFormatter', () => {
  it('converts from base currency using exchange rates', () => {
    const formatter = createPriceFormatter({
      baseCurrency: 'EUR',
      displayCurrency: 'USD',
      exchangeRates: { EUR: 1, USD: 1.1 },
    })
    expect(formatter.format(100)).toMatch(/110/)
    expect(formatter.isConverted).toBe(true)
  })

  it('returns same amount when display matches base', () => {
    const formatter = createPriceFormatter({
      baseCurrency: 'EUR',
      displayCurrency: 'EUR',
      exchangeRates: { EUR: 1, USD: 1.1 },
    })
    expect(convertFromBase(50, 'EUR', { EUR: 1, USD: 1.1 }, 'EUR')).toBe(50)
    expect(formatter.isConverted).toBe(false)
  })
})
