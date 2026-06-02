import { Check, ChevronRight } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { useToast } from '../context/ToastContext'
import LoadingSpinner from '../components/ui/LoadingSpinner'
import DateTimePicker from '../components/ui/DateTimePicker'
import { QuotePricingBreakdown } from '../components/cars/QuotePricingBreakdown'
import { useBookingRules } from '../hooks/useBookingRules'
import {
  computeMaxDropoffDate,
  computeMinDropoffDate,
  dropoffFilterDate,
  ensureValidDropoff,
  minRentalHint,
} from '../utils/bookingRules'
import { formatCurrency, parseDateTimeLocal, toApiDateTime } from '../utils/format'

const STEPS = ['Details', 'Review', 'Confirm']

export default function CheckoutPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const { toast } = useToast()
  const defaults = useMemo(() => Object.fromEntries(searchParams.entries()), [searchParams])

  const [step, setStep] = useState(0)
  const [car, setCar] = useState(null)
  const [locations, setLocations] = useState({})
  const [quote, setQuote] = useState(null)
  const [quoteLoading, setQuoteLoading] = useState(false)
  const [saving, setSaving] = useState(false)
  const [errors, setErrors] = useState({})

  const [form, setForm] = useState({
    car_id: defaults.car_id || '',
    price_type_id: defaults.price_type_id || '',
    pickup_location_id: defaults.pickup_location_id || '',
    dropoff_location_id: defaults.dropoff_location_id || '',
    pickup_at: defaults.pickup_at || '',
    dropoff_at: defaults.dropoff_at || '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    coupon_code: '',
  })

  useEffect(() => {
    api.get('/locations').then((res) => {
      const map = {}
      ;(res.data.data || []).forEach((l) => {
        map[l.id] = l.name
      })
      setLocations(map)
    })
  }, [])

  useEffect(() => {
    if (!form.car_id) return
    api.get(`/cars/${form.car_id}`).then((res) => {
      setCar(res.data.data)
      setForm((prev) => {
        if (prev.price_type_id) return prev
        const first = res.data.data?.price_types?.[0]
        return first ? { ...prev, price_type_id: String(first.id) } : prev
      })
    })
  }, [form.car_id])

  useEffect(() => {
    if (
      !form.car_id ||
      !form.price_type_id ||
      !form.pickup_at ||
      !form.dropoff_at ||
      !form.pickup_location_id ||
      !form.dropoff_location_id
    ) {
      setQuote(null)
      return
    }

    setQuoteLoading(true)
    const payload = {
      car_id: Number(form.car_id),
      price_type_id: Number(form.price_type_id),
      pickup_location_id: Number(form.pickup_location_id),
      dropoff_location_id: Number(form.dropoff_location_id),
      pickup_at: toApiDateTime(form.pickup_at),
      dropoff_at: toApiDateTime(form.dropoff_at),
    }
    if (form.coupon_code.trim()) payload.coupon_code = form.coupon_code.trim()

    api
      .post('/orders/quote', payload)
      .then((res) => setQuote(res.data))
      .catch(() => setQuote(null))
      .finally(() => setQuoteLoading(false))
  }, [
    form.car_id,
    form.price_type_id,
    form.pickup_at,
    form.dropoff_at,
    form.pickup_location_id,
    form.dropoff_location_id,
    form.coupon_code,
  ])

  const pickupDate = useMemo(() => parseDateTimeLocal(form.pickup_at), [form.pickup_at])
  const dropoffDate = useMemo(() => parseDateTimeLocal(form.dropoff_at), [form.dropoff_at])
  const minPickup = useMemo(() => {
    const now = new Date()
    now.setMinutes(0, 0, 0)
    return now
  }, [])

  const rules = useBookingRules(pickupDate, dropoffDate)
  const minRentalDays = rules.min_rental_days || 1
  const rentalHint = minRentalHint(minRentalDays)

  const minDropoffDate = useMemo(() => {
    const base = pickupDate || minPickup
    if (minRentalDays > 1) return computeMinDropoffDate(base, minRentalDays)
    return base
  }, [pickupDate, minPickup, minRentalDays])

  const maxDropoffDate = useMemo(
    () => (pickupDate && rules.max_rental_days ? computeMaxDropoffDate(pickupDate, rules.max_rental_days) : null),
    [pickupDate, rules.max_rental_days],
  )

  const dropoffDateFilter = useMemo(
    () => (pickupDate ? dropoffFilterDate(pickupDate, minRentalDays) : undefined),
    [pickupDate, minRentalDays],
  )

  const handlePickupChange = (pickup_at) => {
    const nextPickup = parseDateTimeLocal(pickup_at)
    const dropoff_at = ensureValidDropoff(nextPickup, form.dropoff_at, minRentalDays)
    setForm((prev) => ({ ...prev, pickup_at, dropoff_at }))
  }

  useEffect(() => {
    if (rules.loading || !pickupDate || !form.dropoff_at) return
    const corrected = ensureValidDropoff(pickupDate, form.dropoff_at, minRentalDays)
    if (corrected !== form.dropoff_at) {
      setForm((prev) => ({ ...prev, dropoff_at: corrected }))
    }
  }, [rules.loading, minRentalDays, pickupDate?.getTime()])

  const validateStep = () => {
    const e = {}
    if (step === 0) {
      if (!form.pickup_location_id) e.pickup_location_id = 'Required'
      if (!form.dropoff_location_id) e.dropoff_location_id = 'Required'
      if (!form.pickup_at) e.pickup_at = 'Required'
      if (!form.dropoff_at) e.dropoff_at = 'Required'
      if (!form.price_type_id) e.price_type_id = 'Required'
    }
    if (step === 1) {
      if (!form.customer_name.trim()) e.customer_name = 'Required'
      if (!form.customer_email.trim()) e.customer_email = 'Required'
      else if (!/\S+@\S+\.\S+/.test(form.customer_email)) e.customer_email = 'Invalid email'
    }
    setErrors(e)
    return Object.keys(e).length === 0
  }

  const nextStep = () => {
    if (validateStep()) setStep((s) => Math.min(s + 1, 2))
  }

  const submitOrder = async () => {
    if (!validateStep()) return
    setSaving(true)
    try {
      await api.post('/orders', {
        car_id: Number(form.car_id),
        price_type_id: Number(form.price_type_id),
        pickup_location_id: Number(form.pickup_location_id),
        dropoff_location_id: Number(form.dropoff_location_id),
        pickup_at: toApiDateTime(form.pickup_at),
        dropoff_at: toApiDateTime(form.dropoff_at),
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_phone: form.customer_phone || undefined,
        coupon_code: form.coupon_code.trim() || undefined,
      })
      setStep(2)
      toast('Booking confirmed! Check your email for details.', 'success')
    } catch (err) {
      const msg = err.response?.data?.message || 'Could not complete booking. Please try again.'
      toast(msg, 'error')
    } finally {
      setSaving(false)
    }
  }

  if (!form.car_id) {
    return (
      <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <p className="text-slate-600">No vehicle selected.</p>
        <Link to="/cars" className="btn-primary mt-4 inline-flex">
          Browse cars
        </Link>
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <h1 className="section-title">Complete Your Booking</h1>

      {/* Step indicator */}
      <div className="mt-8 flex items-center justify-center">
        {STEPS.map((label, i) => (
          <div key={label} className="flex items-center">
            <div
              className={`flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold ${
                i <= step ? 'bg-accent text-white' : 'bg-slate-200 text-slate-500'
              }`}
            >
              {i < step ? <Check className="h-5 w-5" aria-hidden /> : i + 1}
            </div>
            <span className={`ml-2 hidden text-sm font-medium sm:inline ${i <= step ? 'text-brand-950' : 'text-slate-400'}`}>
              {label}
            </span>
            {i < STEPS.length - 1 && (
              <ChevronRight className="mx-3 h-5 w-5 text-slate-300" aria-hidden />
            )}
          </div>
        ))}
      </div>

      <div className="mt-10 grid gap-8 lg:grid-cols-5">
        <div className="lg:col-span-3">
          {step === 0 && (
            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
              <h2 className="text-lg font-bold text-brand-950">Trip details</h2>
              <div className="mt-5 space-y-4">
                <div>
                  <label className="label-field">Rate plan</label>
                  <select
                    className={`input-field ${errors.price_type_id ? 'input-field-error' : ''}`}
                    value={form.price_type_id}
                    onChange={(e) => setForm({ ...form, price_type_id: e.target.value })}
                  >
                    <option value="">Select</option>
                    {(car?.price_types || []).map((pt) => (
                      <option key={pt.id} value={pt.id}>
                        {pt.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="label-field">Pick-up location</label>
                  <select
                    className={`input-field ${errors.pickup_location_id ? 'input-field-error' : ''}`}
                    value={form.pickup_location_id}
                    onChange={(e) => setForm({ ...form, pickup_location_id: e.target.value })}
                  >
                    <option value="">Select</option>
                    {Object.entries(locations).map(([id, name]) => (
                      <option key={id} value={id}>{name}</option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="label-field">Drop-off location</label>
                  <select
                    className={`input-field ${errors.dropoff_location_id ? 'input-field-error' : ''}`}
                    value={form.dropoff_location_id}
                    onChange={(e) => setForm({ ...form, dropoff_location_id: e.target.value })}
                  >
                    <option value="">Select</option>
                    {Object.entries(locations).map(([id, name]) => (
                      <option key={id} value={id}>{name}</option>
                    ))}
                  </select>
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div>
                    <label className="label-field" htmlFor="checkout-pickup-at">Pick-up</label>
                    <DateTimePicker
                      id="checkout-pickup-at"
                      value={form.pickup_at}
                      onChange={handlePickupChange}
                      minDate={minPickup}
                      hasError={!!errors.pickup_at}
                      placeholder="Pick-up date & time"
                      required
                    />
                  </div>
                  <div>
                    <label className="label-field" htmlFor="checkout-dropoff-at">Drop-off</label>
                    <DateTimePicker
                      id="checkout-dropoff-at"
                      value={form.dropoff_at}
                      onChange={(dropoff_at) => setForm({ ...form, dropoff_at })}
                      minDate={minDropoffDate}
                      maxDate={maxDropoffDate}
                      filterDate={dropoffDateFilter}
                      hasError={!!errors.dropoff_at}
                      placeholder="Drop-off date & time"
                      required
                    />
                    {rentalHint && (
                      <p className="mt-1 text-xs text-slate-500">{rentalHint}</p>
                    )}
                  </div>
                </div>
                <div>
                  <label className="label-field">Coupon code (optional)</label>
                  <input
                    className="input-field"
                    placeholder="e.g. WELCOME10"
                    value={form.coupon_code}
                    onChange={(e) => setForm({ ...form, coupon_code: e.target.value })}
                  />
                </div>
              </div>
              <button type="button" className="btn-primary mt-6" onClick={nextStep}>
                Continue to review
              </button>
            </div>
          )}

          {step === 1 && (
            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
              <h2 className="text-lg font-bold text-brand-950">Your information</h2>
              <div className="mt-5 space-y-4">
                <div>
                  <label className="label-field">Full name</label>
                  <input
                    className={`input-field ${errors.customer_name ? 'input-field-error' : ''}`}
                    value={form.customer_name}
                    onChange={(e) => setForm({ ...form, customer_name: e.target.value })}
                  />
                  {errors.customer_name && <p className="mt-1 text-xs text-red-600">{errors.customer_name}</p>}
                </div>
                <div>
                  <label className="label-field">Email</label>
                  <input
                    type="email"
                    className={`input-field ${errors.customer_email ? 'input-field-error' : ''}`}
                    value={form.customer_email}
                    onChange={(e) => setForm({ ...form, customer_email: e.target.value })}
                  />
                  {errors.customer_email && <p className="mt-1 text-xs text-red-600">{errors.customer_email}</p>}
                </div>
                <div>
                  <label className="label-field">Phone</label>
                  <input
                    className="input-field"
                    value={form.customer_phone}
                    onChange={(e) => setForm({ ...form, customer_phone: e.target.value })}
                  />
                </div>
              </div>
              <div className="mt-6 flex gap-3">
                <button type="button" className="btn-secondary" onClick={() => setStep(0)}>
                  Back
                </button>
                <button
                  type="button"
                  className="btn-primary flex-1"
                  onClick={submitOrder}
                  disabled={saving || !quote}
                >
                  {saving ? (
                    <>
                      <LoadingSpinner size="sm" className="text-white" />
                      Processing…
                    </>
                  ) : (
                    'Confirm booking'
                  )}
                </button>
              </div>
            </div>
          )}

          {step === 2 && (
            <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-8 text-center">
              <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500 text-white">
                <Check className="h-8 w-8" aria-hidden />
              </div>
              <h2 className="mt-4 text-2xl font-bold text-brand-950">Booking Confirmed!</h2>
              <p className="mt-2 text-slate-600">
                Thank you, {form.customer_name}. A confirmation has been sent to {form.customer_email}.
              </p>
              <div className="mt-6 flex flex-wrap justify-center gap-3">
                <Link to="/dashboard" className="btn-primary">View my bookings</Link>
                <Link to="/cars" className="btn-secondary">Book another car</Link>
              </div>
            </div>
          )}
        </div>

        {/* Summary sidebar */}
        <div className="lg:col-span-2">
          <div className="sticky top-24 rounded-xl border border-slate-200 bg-white p-6 shadow-card">
            <h3 className="font-bold text-brand-950">Booking summary</h3>
            {car && (
              <p className="mt-2 text-sm font-medium text-brand-800">{car.name}</p>
            )}
            {form.pickup_location_id && (
              <dl className="mt-4 space-y-2 text-sm">
                <div className="flex justify-between gap-2">
                  <dt className="text-slate-500">Pick-up</dt>
                  <dd className="text-right font-medium">{locations[form.pickup_location_id]}</dd>
                </div>
                <div className="flex justify-between gap-2">
                  <dt className="text-slate-500">Drop-off</dt>
                  <dd className="text-right font-medium">{locations[form.dropoff_location_id]}</dd>
                </div>
              </dl>
            )}
            <div className="mt-4 border-t border-slate-100 pt-4">
              {quoteLoading ? (
                <p className="text-sm text-slate-500">Calculating…</p>
              ) : quote ? (
                <div className="space-y-1 text-sm">
                  <QuotePricingBreakdown quote={quote} />
                  <div className="flex justify-between border-t border-slate-100 pt-2 text-base font-bold text-brand-950">
                    <span>Total</span>
                    <span className="text-accent">{formatCurrency(quote.total, quote.currency)}</span>
                  </div>
                </div>
              ) : (
                <p className="text-sm text-slate-500">Complete trip details for pricing</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
