import icelandFlag from '../../assets/flags/iceland.svg'
import { countryFlagEmoji } from '../../utils/countryFlag'

export default function CountryFlag({ code, decorative = false }) {
  const countryName = /^[A-Z]{2}$/.test(code || '')
    ? new Intl.DisplayNames(['en'], { type: 'region' }).of(code)
    : 'Country'

  if (code === 'IS') {
    return (
      <img
        className="country-flag"
        src={icelandFlag}
        alt={decorative ? '' : countryName + ' flag'}
        aria-hidden={decorative || undefined}
      />
    )
  }

  return (
    <span
      className="country-flag"
      role={decorative ? undefined : 'img'}
      aria-label={decorative ? undefined : countryName + ' flag'}
      aria-hidden={decorative || undefined}
    >
      {countryFlagEmoji(code)}
    </span>
  )
}