import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api, resolveStorageUrl } from '../api'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'
import { usePageContent } from '../context/SiteContentContext'
import { getRequestToBookConfig, resolveBookingType } from '../data/requestToBookConfig'
import { formatCurrency, formatCurrencyFromCents } from '../utils/format'
import { combineDateAndTime, nightsBetween, toDateOnlyString } from '../utils/requestToBookUtils'
import { toApiDateTime, parseDateTimeLocal } from '../utils/format'
import { useBookingRules } from './useBookingRules'

const DEFAULT_FORM = {
  startDate: null,
  endDate: null,
  pickup_location_id: '',
  dropoff_location_id: '',
  sameReturn: true,
  pickupTime: '11:00',
  dropoffTime: '10:00',
  flightNumber: '',
  travellers: 2,
  guests_count: 2,
  price_type_id: '',
  rental_option_ids: [],
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_country: '',
  dialCode: '+354',
  dobYear: '',
  dobMonth: '',
  dobDay: '',
  licenceNumber: '',
  licenceCountry: '',
  notes: '',
  special_requests: '',
  paymentMethod: 'card',
  cardNumber: '',
  cardName: '',
  cardExpiry: '',
  cardCvc: '',
  billingStreet: '',
  billingCity: '',
  billingZip: '',
  billingCountry: '',
  agreed: false,
  coupon_code: '',
}

function parseInitialDates(params) {
  const isGh = params.get('type') === 'guesthouse'
  const startKey = isGh ? 'check_in' : 'pickup_at'
  const endKey = isGh ? 'check_out' : 'dropoff_at'
  const startRaw = params.get(startKey)
  const endRaw = params.get(endKey)
  let startDate = startRaw ? new Date(startRaw.slice(0, 10)) : null
  let endDate = endRaw ? new Date(endRaw.slice(0, 10)) : null
  if (startDate && Number.isNaN(startDate.getTime())) startDate = null
  if (endDate && Number.isNaN(endDate.getTime())) endDate = null
  return { startDate, endDate }
}

export default function useRequestToBook() {
  const [searchParams] = useSearchParams()
  const { user } = useAuth()
  const { toast } = useToast()
  const bookingType = resolveBookingType(searchParams)
  const { page: checkoutPage } = usePageContent('checkout')
  const config = useMemo(() => {
    const base = getRequestToBookConfig(bookingType || 'car')
    if (checkoutPage?.stepperSteps?.length) {
      return {
        ...base,
        stepperSteps: checkoutPage.stepperSteps.map((stp) => ({
          ...stp,
          num: Number(stp.num),
        })),
      }
    }
    return base
  }, [bookingType, checkoutPage])

  const [step, setStep] = useState(1)
  const [confirmed, setConfirmed] = useState(null)
  const [item, setItem] = useState(null)
  const [locations, setLocations] = useState([])
  const [blockedDates, setBlockedDates] = useState([])
  const [loadState, setLoadState] = useState('loading')
  const [quote, setQuote] = useState(null)
  const [quoteLoading, setQuoteLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState({})

  const initialDates = useMemo(() => parseInitialDates(searchParams), [searchParams])

  const [form, setForm] = useState(() => ({
    ...DEFAULT_FORM,
    startDate: initialDates.startDate,
    endDate: initialDates.endDate,
    pickup_location_id: searchParams.get('pickup_location_id') || '',
    dropoff_location_id: searchParams.get('dropoff_location_id') || '',
    price_type_id: searchParams.get('price_type_id') || '',
    guests_count: Number(searchParams.get('guests_count')) || 2,
    customer_name: user?.name || '',
    customer_email: user?.email || '',
    customer_phone: user?.phone || '',
  }))

  const updateForm = useCallback((patch) => {
    setForm((prev) => ({ ...prev, ...patch }))
  }, [])

  const carId = searchParams.get('car_id')
  const slug = searchParams.get('slug')
  const vehicleType = searchParams.get('vehicle_type') || (bookingType === 'campervan' ? 'campervan' : 'car')

  useEffect(() => {
    if (!bookingType) {
      setLoadState('error')
      return
    }
    setLoadState('loading')
    if (bookingType === 'guesthouse') {
      if (!slug) {
        setLoadState('error')
        return
      }
      api
        .get(`/guest-houses/${slug}`)
        .then((res) => {
          setItem(res.data?.data || null)
          setLoadState(res.data?.data ? 'ok' : 'error')
        })
        .catch(() => setLoadState('error'))
      const from = new Date().toISOString().slice(0, 10)
      const to = new Date(Date.now() + 120 * 86400000).toISOString().slice(0, 10)
      api
        .get(`/guest-houses/${slug}/availability`, { params: { from, to } })
        .then((res) => setBlockedDates(res.data?.data?.blocked_dates ?? []))
        .catch(() => setBlockedDates([]))
      return
    }
    if (!carId) {
      setLoadState('error')
      return
    }
    api.get('/locations').then((res) => setLocations(res.data?.data || []))
    api
      .get(`/cars/${carId}`)
      .then((res) => {
        const data = res.data?.data
        setItem(data)
        setForm((prev) => {
          const pt = prev.price_type_id || String(data?.price_types?.[0]?.id || '')
          const pickup = prev.pickup_location_id || String(locations[0]?.id || '')
          return {
            ...prev,
            price_type_id: pt,
            pickup_location_id: prev.pickup_location_id || pickup,
            dropoff_location_id: prev.dropoff_location_id || prev.pickup_location_id || pickup,
          }
        })
        setLoadState(data ? 'ok' : 'error')
      })
      .catch(() => setLoadState('error'))
  }, [bookingType, carId, slug])

  useEffect(() => {
    if (bookingType !== 'guesthouse' && locations.length && item) {
      setForm((prev) => {
        if (prev.pickup_location_id) return prev
        const id = String(locations[0].id)
        return { ...prev, pickup_location_id: id, dropoff_location_id: id }
      })
    }
  }, [locations, item, bookingType])

  useEffect(() => {
    if (bookingType === 'guesthouse' || !item?.price_types?.length) return
    setForm((prev) => {
      if (prev.price_type_id) return prev
      return { ...prev, price_type_id: String(item.price_types[0].id) }
    })
  }, [item, bookingType])

  const pickupAt = useMemo(() => {
    if (bookingType === 'guesthouse' || !form.startDate) return ''
    return combineDateAndTime(toDateOnlyString(form.startDate), form.pickupTime)
  }, [bookingType, form.startDate, form.pickupTime])

  const dropoffAt = useMemo(() => {
    if (bookingType === 'guesthouse' || !form.endDate) return ''
    return combineDateAndTime(toDateOnlyString(form.endDate), form.dropoffTime)
  }, [bookingType, form.endDate, form.dropoffTime])

  const pickupDateParsed = useMemo(() => parseDateTimeLocal(pickupAt), [pickupAt])
  const dropoffDateParsed = useMemo(() => parseDateTimeLocal(dropoffAt), [dropoffAt])
  const rules = useBookingRules(
    bookingType !== 'guesthouse' ? pickupDateParsed : null,
    bookingType !== 'guesthouse' ? dropoffDateParsed : null,
  )

  const nights = useMemo(
    () => (bookingType === 'guesthouse' ? nightsBetween(form.startDate, form.endDate) : quote?.rental_days || nightsBetween(form.startDate, form.endDate)),
    [bookingType, form.startDate, form.endDate, quote],
  )

  const dropoffLocationId = form.sameReturn ? form.pickup_location_id : form.dropoff_location_id

  const quoteReady = useMemo(() => {
    if (bookingType === 'guesthouse') {
      return !!(slug && form.startDate && form.endDate && form.guests_count)
    }
    return !!(
      carId &&
      form.price_type_id &&
      form.pickup_location_id &&
      dropoffLocationId &&
      pickupAt &&
      dropoffAt
    )
  }, [bookingType, slug, carId, form, dropoffLocationId, pickupAt, dropoffAt])

  useEffect(() => {
    if (!quoteReady) {
      setQuote(null)
      return
    }
    const t = setTimeout(() => {
      setQuoteLoading(true)
      if (bookingType === 'guesthouse') {
        api
          .post(`/guest-houses/${slug}/quote`, {
            check_in: toDateOnlyString(form.startDate),
            check_out: toDateOnlyString(form.endDate),
            guests_count: form.guests_count,
            coupon_code: form.coupon_code.trim() || undefined,
          })
          .then((res) => setQuote(res.data?.data))
          .catch(() => setQuote(null))
          .finally(() => setQuoteLoading(false))
        return
      }
      const payload = {
        car_id: Number(carId),
        price_type_id: Number(form.price_type_id),
        pickup_location_id: Number(form.pickup_location_id),
        dropoff_location_id: Number(dropoffLocationId),
        pickup_at: toApiDateTime(pickupAt),
        dropoff_at: toApiDateTime(dropoffAt),
        rental_options: form.rental_option_ids.map(Number),
      }
      if (form.coupon_code.trim()) payload.coupon_code = form.coupon_code.trim()
      api
        .post('/orders/quote', payload)
        .then((res) => setQuote(res.data))
        .catch(() => setQuote(null))
        .finally(() => setQuoteLoading(false))
    }, 350)
    return () => clearTimeout(t)
  }, [
    quoteReady,
    bookingType,
    slug,
    carId,
    form.price_type_id,
    form.pickup_location_id,
    dropoffLocationId,
    pickupAt,
    dropoffAt,
    form.rental_option_ids,
    form.coupon_code,
    form.startDate,
    form.endDate,
    form.guests_count,
  ])

  const goStep = useCallback((n) => {
    setStep(n)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, [])

  const validateStep = useCallback(
    (s) => {
      const e = {}
      if (s === 1) {
        if (!form.startDate) e.startDate = 'Required'
        if (!form.endDate) e.endDate = 'Required'
        if (bookingType !== 'guesthouse') {
          if (!form.pickup_location_id) e.pickup_location_id = 'Required'
          if (!dropoffLocationId) e.dropoff_location_id = 'Required'
        }
      }
      if (s === 2 && bookingType !== 'guesthouse') {
        if (!form.price_type_id) e.price_type_id = 'Required'
      }
      if (s === 3) {
        if (!form.customer_name.trim()) e.customer_name = 'Required'
        if (!form.customer_email.trim()) e.customer_email = 'Required'
        else if (!/\S+@\S+\.\S+/.test(form.customer_email)) e.customer_email = 'Invalid email'
        if (bookingType === 'guesthouse' && !form.customer_phone.trim()) e.customer_phone = 'Required'
        if (bookingType !== 'guesthouse') {
          if (!form.customer_country) e.customer_country = 'Required'
          if (!form.licenceNumber.trim()) e.licenceNumber = 'Required'
        }
      }
      if (s === 4) {
        if (!form.agreed) e.agreed = 'Required'
        if (form.paymentMethod === 'card') {
          if (!form.cardNumber.replace(/\s/g, '').match(/^\d{13,16}$/)) e.cardNumber = 'Invalid'
          if (!form.cardName.trim()) e.cardName = 'Required'
        }
      }
      setErrors(e)
      return e
    },
    [form, bookingType, dropoffLocationId],
  )

  const stepValidationMessage = useCallback(
    (s, e) => {
      if (s === 1) {
        if (e.startDate || e.endDate) {
          return bookingType === 'guesthouse'
            ? 'Select your check-in and check-out dates to continue'
            : 'Select your pick-up and drop-off dates to continue'
        }
        if (e.pickup_location_id || e.dropoff_location_id) {
          return 'Select your pick-up and drop-off locations to continue'
        }
      }
      if (s === 2 && e.price_type_id) return 'Choose a protection plan to continue'
      if (s === 3) return 'Complete the required fields to continue'
      return 'Complete the required fields to continue'
    },
    [bookingType],
  )

  const nextStep = useCallback(() => {
    const validationErrors = validateStep(step)
    if (Object.keys(validationErrors).length) {
      toast(stepValidationMessage(step, validationErrors), 'error')
      return
    }
    const next = Math.min(step + 1, 4)
    if (next === 2 && bookingType !== 'guesthouse' && item?.price_types?.length) {
      setForm((prev) => {
        if (prev.price_type_id) return prev
        return { ...prev, price_type_id: String(item.price_types[0].id) }
      })
    }
    goStep(next)
  }, [validateStep, step, goStep, bookingType, item, stepValidationMessage, toast])

  const prevStep = useCallback(() => {
    goStep(Math.max(step - 1, 1))
  }, [step, goStep])

  const toggleAddon = useCallback((id) => {
    const numId = Number(id)
    setForm((prev) => {
      const ids = prev.rental_option_ids.includes(numId)
        ? prev.rental_option_ids.filter((x) => x !== numId)
        : [...prev.rental_option_ids, numId]
      return { ...prev, rental_option_ids: ids }
    })
  }, [])

  const submit = useCallback(async () => {
    const validationErrors = validateStep(4)
    if (Object.keys(validationErrors).length) {
      if (validationErrors.agreed) toast('Please confirm the terms', 'error')
      else toast('Complete the required payment fields to continue', 'error')
      return
    }
    if (!quote) {
      toast('Complete trip details for pricing', 'error')
      return
    }
    setSaving(true)
    try {
      if (bookingType === 'guesthouse') {
        const { data } = await api.post('/guest-houses/bookings', {
          guest_house_slug: slug,
          check_in: toDateOnlyString(form.startDate),
          check_out: toDateOnlyString(form.endDate),
          guests_count: form.guests_count,
          guest_name: form.customer_name,
          guest_email: form.customer_email,
          guest_phone: `${form.dialCode} ${form.customer_phone}`.trim(),
          special_requests: form.special_requests || form.notes || undefined,
          coupon_code: form.coupon_code.trim() || undefined,
        })
        setConfirmed({
          type: 'guesthouse',
          reference: data?.data?.booking_reference,
          total: data?.data?.total_formatted,
          name: form.customer_name.split(' ')[0],
        })
      } else {
        const { data } = await api.post('/orders', {
          car_id: Number(carId),
          price_type_id: Number(form.price_type_id),
          pickup_location_id: Number(form.pickup_location_id),
          dropoff_location_id: Number(dropoffLocationId),
          pickup_at: toApiDateTime(pickupAt),
          dropoff_at: toApiDateTime(dropoffAt),
          customer_name: form.customer_name,
          customer_email: form.customer_email,
          customer_phone: `${form.dialCode} ${form.customer_phone}`.trim() || undefined,
          customer_country: form.customer_country || undefined,
          rental_options: form.rental_option_ids.map(Number),
          coupon_code: form.coupon_code.trim() || undefined,
        })
        setConfirmed({
          type: 'vehicle',
          reference: data?.data?.reference,
          total: data?.data?.total,
          currency: data?.data?.currency,
          name: form.customer_name.split(' ')[0],
        })
      }
      toast('Request sent successfully', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not complete booking', 'error')
    } finally {
      setSaving(false)
    }
  }, [validateStep, form, quote, bookingType, slug, carId, dropoffLocationId, pickupAt, dropoffAt, toast])

  const itemImage = useMemo(() => {
    if (!item) return ''
    if (bookingType === 'guesthouse') {
      return resolveStorageUrl(item.thumbnail || item.images?.[0]?.path)
    }
    return resolveStorageUrl(item.main_image_path)
  }, [item, bookingType])

  const locationName = useCallback(
    (id) => locations.find((l) => String(l.id) === String(id))?.name || '—',
    [locations],
  )

  const selectedPriceType = useMemo(
    () => item?.price_types?.find((pt) => String(pt.id) === String(form.price_type_id)),
    [item, form.price_type_id],
  )

  const locationFeeLabel = useCallback(
    (locId, role) => {
      const id = String(locId)
      const baseId = locations[0] ? String(locations[0].id) : ''
      if (role === 'pickup' && id === baseId) return 'Free'
      if (role === 'dropoff' && (form.sameReturn || id === String(form.pickup_location_id))) return 'Free'

      const isRelevant =
        (role === 'pickup' && id === String(form.pickup_location_id))
        || (role === 'dropoff' && id === String(form.dropoff_location_id))

      if (quote?.fees_lines?.length && isRelevant) {
        const locFee = quote.fees_lines.find(
          (l) => l.kind === 'location_fee' || l.kind === 'one_way_fee',
        )
        if (locFee && Number(locFee.amount) > 0) {
          return `+${formatCurrency(locFee.amount, quote.currency)}`
        }
      }

      const loc = locations.find((l) => String(l.id) === id)
      if (loc?.pickup_fee_cents > 0) {
        return `+${formatCurrencyFromCents(loc.pickup_fee_cents, quote?.currency || 'EUR')}`
      }

      return id === baseId ? 'Free' : '—'
    },
    [locations, form.pickup_location_id, form.dropoff_location_id, form.sameReturn, quote],
  )

  return {
    bookingType,
    vehicleType,
    config,
    step,
    goStep,
    nextStep,
    prevStep,
    confirmed,
    item,
    itemImage,
    locations,
    blockedDates,
    loadState,
    form,
    updateForm,
    quote,
    quoteLoading,
    saving,
    errors,
    nights,
    pickupAt,
    dropoffAt,
    dropoffLocationId,
    locationName,
    selectedPriceType,
    locationFeeLabel,
    toggleAddon,
    submit,
    rules,
    slug,
    carId,
  }
}
