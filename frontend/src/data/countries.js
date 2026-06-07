import countries from 'i18n-iso-countries'
import en from 'i18n-iso-countries/langs/en.json'

countries.registerLocale(en)

export const PRIORITY_COUNTRY_CODES = ['IS', 'GB', 'DE', 'FR', 'US', 'AL']

export function getCountryOptions({ includeOther = false } = {}) {
  const names = countries.getNames('en', { select: 'official' })
  const priority = PRIORITY_COUNTRY_CODES
    .map((code) => ({ code, name: names[code] }))
    .filter((item) => item.name)

  const rest = Object.entries(names)
    .filter(([code]) => !PRIORITY_COUNTRY_CODES.includes(code))
    .map(([code, name]) => ({ code, name }))
    .sort((a, b) => a.name.localeCompare(b.name))

  const options = [...priority, ...rest]
  if (includeOther) {
    options.push({ code: 'OTHER', name: 'Other' })
  }
  return options
}

/** Country display names for legacy selects (values are names, not ISO codes). */
export const COUNTRY_NAMES = getCountryOptions({ includeOther: true }).map((item) => item.name)
