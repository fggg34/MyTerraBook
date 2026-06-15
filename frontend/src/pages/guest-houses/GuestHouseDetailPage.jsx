import { useEffect, useMemo, useRef, useState } from 'react'
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'
import { Bath, Bed, MapPin, Star, Users } from 'lucide-react'
import { api } from '../../api'
import useDragScroll from '../../hooks/useDragScroll'
import { useToast } from '../../context/ToastContext'
import { formatCurrencyFromCents, capitalize } from '../../utils/format'

export default function GuestHouseDetailPage() {
  const { slug } = useParams()
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const { toast } = useToast()
  const [house, setHouse] = useState(null)
  const [loading, setLoading] = useState(true)
  const [blockedDates, setBlockedDates] = useState([])
  const [checkIn, setCheckIn] = useState(
    searchParams.get('check_in') ? new Date(searchParams.get('check_in')) : null,
  )
  const [checkOut, setCheckOut] = useState(
    searchParams.get('check_out') ? new Date(searchParams.get('check_out')) : null,
  )
  const [guests, setGuests] = useState(Number(searchParams.get('guests')) || 2)
  const [quote, setQuote] = useState(null)
  const [quoteLoading, setQuoteLoading] = useState(false)
  const [activeImage, setActiveImage] = useState(0)
  const thumbTrackRef = useRef(null)

  useDragScroll(thumbTrackRef, { enabled: Boolean(house?.images?.length > 1) })

  useEffect(() => {
    api
      .get(`/guest-houses/${slug}`)
      .then((res) => setHouse(res.data?.data))
      .catch(() => setHouse(null))
      .finally(() => setLoading(false))
  }, [slug])

  useEffect(() => {
    if (!slug) return
    const from = new Date().toISOString().slice(0, 10)
    const to = new Date(Date.now() + 90 * 86400000).toISOString().slice(0, 10)
    api
      .get(`/guest-houses/${slug}/availability`, { params: { from, to } })
      .then((res) => setBlockedDates(res.data?.data?.blocked_dates ?? []))
      .catch(() => setBlockedDates([]))
  }, [slug])

  const blockedSet = useMemo(() => new Set(blockedDates), [blockedDates])

  const excludeDates = (date) => blockedSet.has(date.toISOString().slice(0, 10))

  const fetchQuote = () => {
    if (!checkIn || !checkOut) {
      toast('Select check-in and check-out dates', 'error')
      return
    }
    setQuoteLoading(true)
    api
      .post(`/guest-houses/${slug}/quote`, {
        check_in: checkIn.toISOString().slice(0, 10),
        check_out: checkOut.toISOString().slice(0, 10),
        guests_count: guests,
      })
      .then((res) => setQuote(res.data?.data))
      .catch((err) => toast(err.response?.data?.message || 'Could not get quote', 'error'))
      .finally(() => setQuoteLoading(false))
  }

  const bookNow = () => {
    if (!checkIn || !checkOut) {
      toast('Select check-in and check-out dates', 'error')
      return
    }
    const params = new URLSearchParams({
      type: 'guesthouse',
      slug: house.slug,
      check_in: checkIn.toISOString().slice(0, 10),
      check_out: checkOut.toISOString().slice(0, 10),
      guests_count: String(guests),
    })
    navigate(`/checkout?${params}`)
  }

  if (!house) {
    if (loading) return null
    return (
      <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <p className="text-red-600">Property not found.</p>
        <Link to="/guest-houses" className="btn-primary mt-4 inline-flex">
          Back to listings
        </Link>
      </div>
    )
  }

  const images = house.images?.length ? house.images : [{ path: house.thumbnail }]
  const policyText = {
    flexible: 'Free cancellation up to 24 hours before check-in.',
    moderate: 'Free cancellation up to 7 days before check-in.',
    strict: 'Free cancellation up to 14 days before check-in.',
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="grid gap-8 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="overflow-hidden rounded-xl bg-slate-100">
            <img
              src={images[activeImage]?.path || house.thumbnail}
              alt={house.name}
              className="aspect-[16/10] w-full object-cover"
            />
          </div>
          {images.length > 1 && (
            <div className="mt-2 flex gap-2 overflow-x-auto drag-scroll" ref={thumbTrackRef}>
              {images.map((img, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => setActiveImage(i)}
                  className={`h-16 w-24 shrink-0 overflow-hidden rounded-lg border-2 ${
                    activeImage === i ? 'border-accent' : 'border-transparent'
                  }`}
                >
                  <img src={img.path} alt="" className="h-full w-full object-cover" />
                </button>
              ))}
            </div>
          )}

          <h1 className="mt-6 text-3xl font-bold text-brand-950">{house.name}</h1>
          <p className="mt-1 flex items-center gap-1 text-slate-600">
            <MapPin className="h-4 w-4" />
            {house.city}, {house.country}
            {house.rating != null && (
              <span className="ml-4 flex items-center gap-1">
                <Star className="h-4 w-4 fill-amber-400 text-amber-400" />
                {house.rating}
              </span>
            )}
          </p>

          <div className="mt-4 flex flex-wrap gap-6 text-sm text-slate-600">
            <span className="flex items-center gap-1">
              <Bed className="h-4 w-4" /> {house.bedrooms} bedrooms
            </span>
            <span className="flex items-center gap-1">
              <Bath className="h-4 w-4" /> {house.bathrooms} bathrooms
            </span>
            <span className="flex items-center gap-1">
              <Users className="h-4 w-4" /> Up to {house.max_guests} guests
            </span>
          </div>

          <p className="mt-6 whitespace-pre-line text-slate-700">{house.description}</p>

          {house.amenities?.length > 0 && (
            <div className="mt-8">
              <h2 className="text-lg font-bold text-brand-950">Amenities</h2>
              <div className="mt-4 space-y-4">
                {house.amenities.map((group) => (
                  <div key={group.group}>
                    <h3 className="text-sm font-semibold uppercase text-slate-500">
                      {capitalize(group.group)}
                    </h3>
                    <ul className="mt-2 flex flex-wrap gap-2">
                      {group.items.map((a) => (
                        <li
                          key={a.id}
                          className="rounded-lg bg-slate-100 px-3 py-1 text-sm text-slate-700"
                        >
                          {a.name}
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>
            </div>
          )}

          {house.reviews?.length > 0 && (
            <div className="mt-8">
              <h2 className="text-lg font-bold text-brand-950">Reviews</h2>
              <ul className="mt-4 space-y-4">
                {house.reviews.map((r) => (
                  <li key={r.id} className="rounded-lg border border-slate-100 p-4">
                    <p className="font-medium">{r.title}</p>
                    <p className="text-sm text-amber-600">{'★'.repeat(r.rating)}</p>
                    <p className="mt-1 text-sm text-slate-600">{r.body}</p>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>

        <aside className="lg:sticky lg:top-24 lg:self-start">
          <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
            <p className="text-2xl font-bold text-brand-950">
              {house.base_price_per_night_formatted}
              <span className="text-base font-normal text-slate-500"> / night</span>
            </p>
            <div className="mt-4 space-y-3">
              <div>
                <label className="text-xs font-medium text-slate-500">Check-in</label>
                <DatePicker
                  selected={checkIn}
                  onChange={setCheckIn}
                  className="input-field mt-1 w-full"
                  minDate={new Date()}
                  excludeDates={excludeDates}
                />
              </div>
              <div>
                <label className="text-xs font-medium text-slate-500">Check-out</label>
                <DatePicker
                  selected={checkOut}
                  onChange={setCheckOut}
                  className="input-field mt-1 w-full"
                  minDate={checkIn || new Date()}
                  excludeDates={excludeDates}
                />
              </div>
              <div>
                <label className="text-xs font-medium text-slate-500">Guests</label>
                <input
                  type="number"
                  min={1}
                  max={house.max_guests}
                  value={guests}
                  onChange={(e) => setGuests(Number(e.target.value))}
                  className="input-field mt-1 w-full"
                />
              </div>
            </div>
            <button
              type="button"
              className="btn-secondary mt-4 w-full"
              onClick={fetchQuote}
              disabled={quoteLoading}
            >
              Check availability & price
            </button>
            {quote && (
              <div className="mt-4 space-y-1 border-t border-slate-100 pt-4 text-sm">
                <p>
                  {quote.nights} nights: {formatCurrencyFromCents(quote.base_total, quote.currency)}
                </p>
                {quote.cleaning_fee > 0 && (
                  <p>Cleaning: {formatCurrencyFromCents(quote.cleaning_fee, quote.currency)}</p>
                )}
                {quote.tax_amount > 0 && (
                  <p>Tax: {formatCurrencyFromCents(quote.tax_amount, quote.currency)}</p>
                )}
                <p className="font-bold text-brand-950">
                  Total: {quote.total_formatted || formatCurrencyFromCents(quote.total_amount, quote.currency)}
                </p>
              </div>
            )}
            <button type="button" className="btn-primary mt-4 w-full" onClick={bookNow}>
              Book now
            </button>
            <p className="mt-4 text-xs text-slate-500">
              {policyText[house.cancellation_policy] || policyText.moderate}
            </p>
          </div>
        </aside>
      </div>
    </div>
  )
}
