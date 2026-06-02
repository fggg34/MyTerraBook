import { Check, ChevronRight, Gauge } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom'
import { api, resolveStorageUrl } from '../api'
import BookingForm, { buildCheckoutParams } from '../components/cars/BookingForm'
import CarCard from '../components/cars/CarCard'
import { PageLoader } from '../components/ui/LoadingSpinner'
import EmptyState from '../components/ui/EmptyState'
import { toApiDateTime } from '../utils/format'

export default function CarDetailsPage() {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const queryDefaults = Object.fromEntries(searchParams.entries())

  const [car, setCar] = useState(null)
  const [relatedCars, setRelatedCars] = useState([])
  const [loadState, setLoadState] = useState('loading')
  const [selectedPriceTypeId, setSelectedPriceTypeId] = useState('')
  const [quote, setQuote] = useState(null)
  const [quoteLoading, setQuoteLoading] = useState(false)
  const [bookingForm, setBookingForm] = useState(null)

  useEffect(() => {
    setLoadState('loading')
    api
      .get(`/cars/${id}`)
      .then((res) => {
        setCar(res.data.data)
        setLoadState('ok')
        const pts = res.data.data?.price_types || []
        if (pts.length) setSelectedPriceTypeId(String(pts[0].id))
      })
      .catch(() => setLoadState('error'))
  }, [id])

  useEffect(() => {
    if (!car?.category?.id) return
    api.get('/cars').then((res) => {
      const all = res.data.data || []
      setRelatedCars(all.filter((c) => c.id !== car.id && c.category_id === car.category.id).slice(0, 3))
    })
  }, [car])

  useEffect(() => {
    if (!bookingForm || !car) {
      setQuote(null)
      return
    }
    const { pickup_at, dropoff_at, pickup_location_id, dropoff_location_id, price_type_id } = bookingForm
    if (!pickup_at || !dropoff_at || !pickup_location_id || !dropoff_location_id || !price_type_id) {
      setQuote(null)
      return
    }

    setQuoteLoading(true)
    api
      .post('/orders/quote', {
        car_id: car.id,
        price_type_id: Number(price_type_id),
        pickup_location_id: Number(pickup_location_id),
        dropoff_location_id: Number(dropoff_location_id),
        pickup_at: toApiDateTime(pickup_at),
        dropoff_at: toApiDateTime(dropoff_at),
      })
      .then((res) => setQuote(res.data))
      .catch(() => setQuote(null))
      .finally(() => setQuoteLoading(false))
  }, [bookingForm, car])

  const searchQuery = useMemo(() => searchParams.toString(), [searchParams])

  if (loadState === 'loading') return <PageLoader message="Loading vehicle details…" />

  if (loadState === 'error' || !car) {
    return (
      <div className="mx-auto max-w-7xl px-4 py-16">
        <EmptyState
          title="Vehicle not found"
          description="This car may no longer be available."
          action={
            <Link to="/cars" className="btn-primary">
              Browse all cars
            </Link>
          }
        />
      </div>
    )
  }

  const imageSrc = resolveStorageUrl(car.main_image_path)
  const characteristics = car.characteristics ?? []
  const rentalOptions = car.rental_options ?? []
  const priceTypes = car.price_types ?? []

  const handleBook = (form) => {
    const params = buildCheckoutParams({ ...form, car_id: car.id })
    navigate(`/checkout?${params}`)
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <nav className="mb-6 flex items-center gap-1 text-sm text-slate-500" aria-label="Breadcrumb">
        <Link to="/" className="hover:text-accent">Home</Link>
        <ChevronRight className="h-4 w-4" aria-hidden />
        <Link to="/cars" className="hover:text-accent">Cars</Link>
        <ChevronRight className="h-4 w-4" aria-hidden />
        <span className="font-medium text-brand-950">{car.name}</span>
      </nav>

      <div className="grid gap-8 lg:grid-cols-3">
        <div className="lg:col-span-2">
          <div className="overflow-hidden rounded-xl bg-white shadow-card">
            {imageSrc ? (
              <img src={imageSrc} alt={car.name} className="aspect-[16/9] w-full object-cover" />
            ) : (
              <div className="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                <Gauge className="h-24 w-24 text-white/20" aria-hidden />
              </div>
            )}

            <div className="p-6 sm:p-8">
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  {car.category?.name && (
                    <span className="text-sm font-semibold uppercase tracking-wide text-accent">
                      {car.category.name}
                    </span>
                  )}
                  <h1 className="mt-1 text-2xl font-bold text-brand-950 sm:text-3xl">{car.name}</h1>
                </div>
              </div>

              <div className="mt-6 grid gap-4 sm:grid-cols-3">
                {[
                  ['Transmission', car.transmission],
                  ['Fuel', car.fuel_type],
                  ['Available units', car.units_available],
                ].map(([label, value]) => (
                  <div key={label} className="rounded-lg bg-slate-50 p-4">
                    <dt className="text-xs font-semibold uppercase tracking-wide text-slate-500">{label}</dt>
                    <dd className="mt-1 font-semibold capitalize text-brand-950">{value ?? '—'}</dd>
                  </div>
                ))}
              </div>

              {car.description && (
                <div className="mt-8">
                  <h2 className="text-lg font-bold text-brand-950">Description</h2>
                  <p className="mt-2 whitespace-pre-wrap text-slate-600 leading-relaxed">{car.description}</p>
                </div>
              )}

              {characteristics.length > 0 && (
                <div className="mt-8">
                  <h2 className="text-lg font-bold text-brand-950">Features &amp; Specs</h2>
                  <ul className="mt-4 grid gap-2 sm:grid-cols-2">
                    {characteristics.map((c) => (
                      <li key={c.id} className="flex items-center gap-2 text-sm text-slate-700">
                        <Check className="h-4 w-4 shrink-0 text-emerald-500" aria-hidden />
                        {c.display_text || c.name}
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {rentalOptions.length > 0 && (
                <div className="mt-8">
                  <h2 className="text-lg font-bold text-brand-950">Available Add-ons</h2>
                  <ul className="mt-4 divide-y divide-slate-100 rounded-lg border border-slate-200">
                    {rentalOptions.map((opt) => (
                      <li key={opt.id} className="flex items-center justify-between px-4 py-3 text-sm">
                        <span className="font-medium text-brand-900">{opt.name}</span>
                        <span className="text-slate-600">
                          {opt.cost}{opt.is_daily_cost ? '/day' : ''}
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="lg:col-span-1">
          <div className="sticky top-24">
            <BookingForm
              carId={car.id}
              priceTypes={priceTypes}
              initialValues={queryDefaults}
              selectedPriceTypeId={selectedPriceTypeId}
              onPriceTypeChange={setSelectedPriceTypeId}
              quote={quote}
              quoteLoading={quoteLoading}
              onFormChange={setBookingForm}
              onSubmit={handleBook}
              submitLabel="Continue to Checkout"
            />
          </div>
        </div>
      </div>

      {relatedCars.length > 0 && (
        <section className="mt-16">
          <h2 className="section-title">Similar Cars</h2>
          <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {relatedCars.map((c) => (
              <CarCard key={c.id} car={c} searchQuery={searchQuery} categoryName={car.category?.name} />
            ))}
          </div>
        </section>
      )}
    </div>
  )
}
