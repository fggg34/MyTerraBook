import { resolveStorageUrl } from '../api'
import {
  DEFAULT_OWNER,
  DEFAULT_RATING,
  LISTING_TYPES,
  MOCK_FAQS,
  MOCK_REVIEWS,
} from '../data/listingConfig'

const FALLBACK_IMAGES = {
  campervan: ['/images/homepage/cardcamper.jpg', '/images/homepage/hero-van.jpg'],
  car: ['/images/homepage/cardcar.jpg', '/images/homepage/hero-van.jpg'],
  guesthouse: ['/images/homepage/cardhouse.jpg', '/images/homepage/cardcamper.jpg'],
}

function formatPrice(centsOrDecimal, fallback = '94') {
  if (centsOrDecimal == null) return fallback
  const n = Number.parseFloat(centsOrDecimal)
  if (Number.isNaN(n)) return fallback
  return n >= 100 ? String(Math.round(n / 100)) : String(Math.round(n))
}

function buildImages(car, listingType) {
  const paths = [
    car.main_image_path,
    ...(car.details_image_paths || []),
  ].filter(Boolean)
  const urls = paths.map((p) => resolveStorageUrl(p)).filter(Boolean)
  const fallbacks = FALLBACK_IMAGES[listingType] || FALLBACK_IMAGES.car
  const merged = [...urls, ...fallbacks].slice(0, 5)
  return merged.map((url, i) => ({
    url,
    alt: i === 0 ? `${car.name} — main photo` : `${car.name} — photo ${i + 1}`,
  }))
}

function buildDetailSpecs(car, listingType) {
  if (listingType === 'guesthouse') {
    return [
      { label: 'Sleeps 4' },
      { label: '2 bedrooms' },
      { label: 'Hot tub' },
      { label: 'Self check-in' },
    ]
  }
  if (listingType === 'car') {
    return [
      { label: car.transmission ? `${car.transmission}` : 'Automatic' },
      { label: car.fuel_type || 'Petrol' },
      { label: `${car.units_available || 1} available` },
      { label: 'Unlimited mileage' },
    ].map((s) => ({ label: s.label }))
  }
  return [
    { label: 'Pet friendly' },
    { label: 'Age 25+' },
    { label: 'Sleeps 3' },
    { label: '4×4 ready' },
  ]
}

function buildAmenities(car, listingType) {
  const fromApi = (car.characteristics || []).map((c, i) => ({
    name: c.display_text || c.name,
    featured: i < 4,
  }))
  if (fromApi.length) return fromApi

  if (listingType === 'guesthouse') {
    return [
      { name: 'Geothermal hot tub', featured: true },
      { name: 'Full kitchen', featured: true },
      { name: 'Fast Wi-Fi', featured: true },
      { name: 'Sea views', featured: true },
      { name: 'Private parking' },
      { name: 'Washing machine' },
      { name: 'Underfloor heating' },
      { name: 'BBQ area' },
    ]
  }
  if (listingType === 'car') {
    return [
      { name: 'Bluetooth', featured: true },
      { name: 'USB charging', featured: true },
      { name: 'Winter tyres', featured: true },
      { name: 'GPS ready', featured: true },
      { name: 'Air conditioning' },
      { name: 'Cruise control' },
    ]
  }
  return [
    { name: 'Induction cooktop', featured: true },
    { name: '12V fridge', featured: true },
    { name: 'Lithium + solar', featured: true },
    { name: 'Diesel heating', featured: true },
    { name: 'Wet bath & shower' },
    { name: 'Unlimited mileage', featured: false },
  ]
}

function buildConditions(listingType) {
  if (listingType === 'guesthouse') {
    return [
      { title: 'Check-in from 15:00', desc: 'Flexible early check-in when available — message the host.' },
      { title: 'Quiet hours 22:00–08:00', desc: 'Please respect neighbours in this residential area.' },
      { title: 'No smoking indoors', desc: 'Smoking allowed on the terrace only.' },
      { title: 'Max 4 guests', desc: 'Includes children; infants under 2 free.' },
      { title: 'Free cancellation', desc: 'Full refund up to 48 hours before check-in.' },
      { title: 'Pets on request', desc: 'Small dogs welcome — ask before booking.' },
    ]
  }
  return [
    { title: 'Driver age 25+', desc: 'All drivers must be at least 25 years old.' },
    { title: 'Licence held 2+ years', desc: 'Full driving licence valid for the trip.' },
    { title: '€1,500 deposit', desc: 'Refundable hold, reducible with Excess Insurance.' },
    { title: 'Unlimited mileage', desc: 'Drive the whole Ring Road, no extra per-km fees.' },
    { title: 'Return with full tank', desc: 'Same fuel level as pick-up, or pre-pay fuel.' },
    { title: 'CDW insurance included', desc: 'Collision damage waiver comes with every booking.' },
    { title: 'Free cancellation', desc: 'Full refund up to 48 hours before pick-up.' },
    { title: 'Pets welcome', desc: 'Bring the dog — no extra cleaning charge on most campers.' },
  ]
}

function buildSleeping(listingType) {
  if (listingType === 'car') return null
  if (listingType === 'guesthouse') {
    return {
      kicker: 'Sleeps up to 4 guests',
      beds: [
        { title: 'Master bedroom', text: 'Queen bed with en-suite shower.', dim: '160 × 200 cm', image: '/images/homepage/cardhouse.jpg' },
        { title: 'Twin room', text: 'Two single beds, shared bathroom.', dim: '90 × 200 cm each', image: '/images/homepage/cardcamper.jpg' },
        { title: 'Sofa bed', text: 'Optional extra guest in the lounge.', dim: '140 × 200 cm', image: '/images/homepage/cardhouse.jpg' },
      ],
    }
  }
  return {
    kicker: 'Sleeps up to 3 guests',
    beds: [
      { title: 'Power lift bed', text: 'Electric drop-down rear bed, sleeps 2 adults comfortably.', dim: '49" × 79"', image: '/images/homepage/cardcamper.jpg' },
      { title: 'Convertible dinette', text: 'Folds flat into a single berth for a third guest or child.', dim: '28" × 70"', image: '/images/homepage/cardcamper.jpg' },
      { title: 'Bedding included', text: 'Duvets, pillows and fresh linen provided and laundered.', dim: 'Complimentary', image: '/images/homepage/cardcamper.jpg' },
    ],
  }
}

function buildAddons(car, listingType) {
  const fromApi = (car.rental_options || []).map((opt) => ({
    name: opt.name,
    sub: opt.is_daily_cost ? 'Per day' : 'One-time',
    price: opt.cost || '—',
    free: false,
  }))
  if (fromApi.length) return fromApi

  if (listingType === 'guesthouse') {
    return [
      { name: 'Late check-out', sub: 'Until 13:00 when available', price: '€25' },
      { name: 'Breakfast basket', sub: 'Local produce for two', price: '€35' },
      { name: 'Airport transfer', sub: 'KEF door-to-door, one way', price: '€55' },
      { name: 'Hot tub private session', sub: 'Extra hour, pre-heated', price: '€20' },
    ]
  }
  return [
    { name: 'Camping chairs & table', sub: 'Foldable set for two', price: '€15' },
    { name: 'Extra bedding kit', sub: 'Duvet, pillow, linen for guest 3', price: '€25' },
    { name: 'Kitchen kit', sub: 'Pots, pans, cutlery, French press', price: '€20' },
    { name: 'Unlimited mileage', sub: 'Included with every trip', price: 'Free', free: true },
  ]
}

export function mapCarToListing(car, listingType = 'campervan') {
  const typeConfig = LISTING_TYPES[listingType] || LISTING_TYPES.campervan
  const priceType = car.price_types?.[0]
  const priceFrom = formatPrice(
    priceType?.from_price_per_day ?? priceType?.from_price_per_day_cents,
    listingType === 'guesthouse' ? '89' : '94',
  )

  const desc = car.description || ''
  const shortDesc = desc.length > 280 ? desc.slice(0, 280).trim() : desc
  const moreDesc = desc.length > 280 ? desc.slice(280).trim() : ''

  return {
    listingType,
    typeConfig,
    car,
    id: car.id,
    name: car.name,
    title: car.name,
    categoryName: car.category?.name || typeConfig.id,
    images: buildImages(car, listingType),
    photoCount: Math.max(buildImages(car, listingType).length, 10),
    owner: DEFAULT_OWNER,
    rating: DEFAULT_RATING,
    detailSpecs: buildDetailSpecs(car, listingType),
    description: { short: shortDesc || 'Description coming soon.', more: moreDesc },
    amenities: buildAmenities(car, listingType),
    conditions: buildConditions(listingType),
    sleeping: buildSleeping(listingType),
    addons: buildAddons(car, listingType),
    priceFrom,
    priceTypes: car.price_types || [],
    reviews: MOCK_REVIEWS,
    faqs: MOCK_FAQS,
    reviewCategories: [
      { label: 'Communication', value: '5.0', width: '100%' },
      { label: 'Cleanliness', value: '5.0', width: '100%' },
      { label: 'Maintenance', value: '5.0', width: '100%' },
      { label: 'Value', value: '4.9', width: '98%' },
      { label: 'Listing accuracy', value: '5.0', width: '100%' },
    ],
    guestPhotoUrls: buildImages(car, listingType).map((i) => i.url),
  }
}
