import { describe, expect, it } from 'vitest'
import { convertFromBase, convertToBase, createPriceFormatter } from '../format'
import {
  buildHostRentalOptionSyncPayload,
  defaultRentalOptionAmountEuros,
  normalizeHostRentalOptionFromApi,
} from '../rentalOptionPricing'

const rates = { EUR: 1, USD: 1.08 }
const usdHost = {
  code: 'USD',
  fromBaseAmount: (amount) => convertFromBase(amount, 'USD', rates, 'EUR'),
  toBaseAmount: (amount) => convertToBase(amount, 'USD', rates, 'EUR'),
}

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

describe('convertToBase', () => {
  it('converts display currency back into shop base currency', () => {
    expect(convertToBase(108, 'USD', rates, 'EUR')).toBeCloseTo(100, 5)
    expect(convertToBase(100, 'EUR', rates, 'EUR')).toBe(100)
  })
})

describe('rentalOptionPricing host currency', () => {
  it('loads base cents into host display currency for editing', () => {
    const row = normalizeHostRentalOptionFromApi({ id: 5, cost_cents: 9259 }, usdHost)
    expect(row.cost_euros).toBeCloseTo(100, 2)
  })

  it('saves host display currency as base major units for the API', () => {
    const payload = buildHostRentalOptionSyncPayload({ id: 5, cost_euros: 100 }, [], usdHost)
    expect(payload.cost_euros).toBeCloseTo(92.59, 2)
  })

  it('suggests catalog defaults in host display currency', () => {
    const amount = defaultRentalOptionAmountEuros({ cost_cents: 10800 }, usdHost)
    expect(amount).toBeCloseTo(116.64, 2)
  })
})
