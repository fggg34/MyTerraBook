import { LISTING_TYPES } from './listingConfig'

export const PREPAY_PERCENT = 20

export const STEPPER_STEPS = [
  { num: 1, sk: 'Step 1', sl: 'Trip & times' },
  { num: 2, sk: 'Step 2', sl: 'Extras & cover' },
  { num: 3, sk: 'Step 3', sl: 'Your details' },
  { num: 4, sk: 'Step 4', sl: 'Payment' },
]

/** Protection plan presentation keyed by price_type slug (fallback by index). */
export const PROTECTION_PLAN_PRESENTATION = {
  basic: {
    deposit: '€1,500 deposit',
    features: ['Collision damage waiver', '24/7 roadside assistance'],
    included: true,
    mostPopular: false,
  },
  plus: {
    deposit: '€500 deposit',
    features: ['Everything in Basic', 'Lower €500 excess', 'Tyres & windscreen'],
    included: false,
    mostPopular: true,
  },
  max: {
    deposit: '€0 deposit',
    features: ['Everything in Plus', 'Zero excess, zero deposit', 'Gravel, ash & underbody'],
    included: false,
    mostPopular: false,
  },
  'standard-rate': {
    deposit: '€1,500 deposit',
    features: ['Collision damage waiver', '24/7 roadside assistance'],
    included: true,
    mostPopular: false,
  },
  'premium-rate': {
    deposit: '€500 deposit',
    features: ['Everything in Basic', 'Lower €500 excess', 'Tyres & windscreen'],
    included: false,
    mostPopular: true,
  },
  'long-term-rate': {
    deposit: '€0 deposit',
    features: ['Everything in Plus', 'Zero excess, zero deposit', 'Extended rental cover'],
    included: false,
    mostPopular: false,
  },
}

export function getProtectionPresentation(priceType, index = 0) {
  const slug = priceType?.slug?.toLowerCase()
  if (slug && PROTECTION_PLAN_PRESENTATION[slug]) {
    return PROTECTION_PLAN_PRESENTATION[slug]
  }
  const keys = Object.keys(PROTECTION_PLAN_PRESENTATION)
  return PROTECTION_PLAN_PRESENTATION[keys[index % keys.length]]
}

export const CONFIRMATION_TIMELINE = {
  vehicle: [
    { title: 'Request sent', text: 'Today · Your host has been notified of your trip.' },
    { title: 'Host approves your trip', text: "Usually within the hour. We'll email and text you." },
    { title: 'Prepayment confirmed', text: `A ${PREPAY_PERCENT}% prepayment is charged after approval to hold your booking. This prepayment is non-refundable. The remaining balance is due on pick-up.` },
    { title: 'Pick up & hit the Ring Road', text: 'Meet your host for a full walkover of the vehicle, then you\'re off.' },
  ],
  guesthouse: [
    { title: 'Request sent', text: 'Today · Your host has been notified of your stay.' },
    { title: 'Host approves your stay', text: "Usually within the hour. We'll email you when it's confirmed." },
    { title: 'Prepayment confirmed', text: `A ${PREPAY_PERCENT}% prepayment is charged after approval to hold your stay. This prepayment is non-refundable. The remaining balance is due at check-in.` },
    { title: 'Check in & enjoy', text: 'Arrive at the agreed time; your host will show you in.' },
  ],
}

export function resolveBookingType(searchParams) {
  const type = searchParams.get('type')
  if (type === 'guesthouse') return 'guesthouse'
  const vehicleType = searchParams.get('vehicle_type')
  if (vehicleType === 'campervan') return 'campervan'
  if (vehicleType === 'car') return 'car'
  if (searchParams.get('car_id')) return 'car'
  return null
}

export function getRequestToBookConfig(bookingType) {
  const listing = LISTING_TYPES[bookingType] || LISTING_TYPES.car
  const isGuesthouse = bookingType === 'guesthouse'
  const isVehicle = !isGuesthouse

  return {
    bookingType,
    listing,
    stepperSteps: STEPPER_STEPS,
    step1: {
      title: isGuesthouse ? 'When is your stay?' : 'When & where is your trip?',
      subtitle: isGuesthouse
        ? 'Confirm your check-in and check-out dates. Minimum stay rules apply for this property.'
        : 'Confirm your dates and choose your pick-up and drop-off times. Office hours are 08:00–20:00 daily — out-of-hours handover is available for a small fee.',
      dateStartLabel: listing.dateStartLabel,
      dateEndLabel: listing.dateEndLabel,
      rateUnit: listing.rateUnit,
      showLocations: isVehicle,
      showTimes: isVehicle,
      showFlightNumber: isVehicle,
      showSameReturn: isVehicle,
      showTravellers: isVehicle,
      showGuests: isGuesthouse,
      showPropertyAddress: isGuesthouse,
      continueLabel: isGuesthouse ? 'Continue' : 'Continue to extras',
      stepNote: `${PREPAY_PERCENT}% prepayment on approval (non-refundable) · balance due on pick-up`,
    },
    step2: {
      title: 'Add extras & choose your cover',
      subtitle: isGuesthouse
        ? 'Everything below is optional. Add what you need for your stay — you can change these before check-in.'
        : 'Everything below is optional. Add what you need for the road — you can change these right up until pick-up.',
      protectionSubtitle: 'CDW included on every trip',
      addonsSubtitle: 'Optional extras',
      showProtection: isVehicle,
      showAddons: isVehicle,
      showIncludedNote: isGuesthouse,
    },
    step3: {
      title: 'Your information',
      subtitle: isGuesthouse
        ? 'We need your contact details to prepare your booking. Everything is encrypted and shared only with your host.'
        : 'We need the main driver\'s details to prepare the rental agreement. Everything is encrypted and shared only with your host.',
      showLicence: isVehicle,
      showDob: isVehicle,
      showCountry: isVehicle,
      showSpecialRequests: isGuesthouse,
      showNotes: isVehicle,
      licenceSubtitle: 'Held 2+ years, age 25+',
    },
    step4: {
      title: 'Payment',
      subtitle: `Choose how you'd like to pay. Once your host approves, we charge a ${PREPAY_PERCENT}% prepayment to hold the booking — the remaining balance is paid on pick-up or check-in.`,
      submitLabel: 'Request to Book',
      isGuesthouse,
    },
    summaryKick: (item) => {
      if (isGuesthouse) {
        return `Guesthouse · ${item?.city || 'Iceland'}`
      }
      if (bookingType === 'campervan') {
        return `Campervan · Sleeps ${item?.units_available || '—'}`
      }
      const cat = item?.category?.name || 'Car'
      const trans = item?.transmission && item.transmission !== '—' ? item.transmission : ''
      return trans ? `${cat} · ${trans}` : cat
    },
    backLink: (item, bt) => {
      if (bt === 'guesthouse' && item?.slug) return `/guest-houses/${item.slug}`
      if (bt === 'guesthouse' && item?.id) return `/guesthouses/${item.id}`
      if (bt === 'campervan' && item?.id) return `/campervans/${item.id}`
      if (item?.id) return `/cars/${item.id}`
      return '/'
    },
    confirmationTimeline: isGuesthouse ? CONFIRMATION_TIMELINE.guesthouse : CONFIRMATION_TIMELINE.vehicle,
    confirmationHero: isGuesthouse
      ? (name) => `You're almost checked in, ${name}!`
      : (name) => `You're almost on the road, ${name}!`,
    confirmationSubtext: isGuesthouse
      ? `Your request has been sent to your host. They typically reply within the hour — we'll email you when your stay is approved. A ${PREPAY_PERCENT}% prepayment is charged then; the balance is due at check-in.`
      : `Your request has been sent to your host. They typically reply within the hour — we'll email you when your trip is approved. A ${PREPAY_PERCENT}% prepayment is charged then; the balance is due on pick-up.`,
    reassurance: [
      { bold: `${PREPAY_PERCENT}% prepayment on approval (non-refundable).`, text: 'The remaining balance is paid on pick-up or check-in.' },
      { text: 'Comprehensive insurance & 24/7 roadside assistance.' },
      { bold: 'Average host reply in under 1 hour.', text: '' },
    ],
  }
}

export const TIME_OPTIONS = [
  '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00',
]

export const OOH_TIME_VALUE = '20:30'
export const OOH_FEE_DISPLAY = '€35'

export function formatOohTimeOption(feeDisplay = OOH_FEE_DISPLAY) {
  return `${OOH_TIME_VALUE} — out of hours (+${feeDisplay})`
}

export { COUNTRY_NAMES as COUNTRIES } from './countries'
