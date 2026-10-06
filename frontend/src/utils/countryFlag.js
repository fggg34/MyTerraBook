export function countryFlagEmoji(countryCode) {
  if (!/^[A-Z]{2}$/.test(countryCode || '')) return '🌐'
  return String.fromCodePoint(...[...countryCode].map((letter) => 0x1f1a5 + letter.charCodeAt(0)))
}