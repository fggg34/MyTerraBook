import { useEffect, useMemo, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { api, resolveStorageUrl } from '../api'

function buildCheckoutQuery(carId, searchParams) {
  const next = new URLSearchParams()
  next.set('car_id', String(carId))
  for (const [key, value] of searchParams.entries()) {
    if (key === 'car_id') continue
    if (value) next.set(key, value)
  }
  return next.toString()
}

export default function CarDetailsPage() {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const [car, setCar] = useState(null)
  const [loadState, setLoadState] = useState('loading')
  const [selectedPriceTypeId, setSelectedPriceTypeId] = useState('')

  useEffect(() => {
    setLoadState('loading')
    setCar(null)
    api
      .get(`/cars/${id}`)
      .then((res) => {
        setCar(res.data.data)
        setLoadState('ok')
      })
      .catch(() => {
        setCar(null)
        setLoadState('error')
      })
  }, [id])

  const checkoutHref = useMemo(() => {
    if (!car) return '#'
    const base = buildCheckoutQuery(car.id, searchParams)
    const qs = new URLSearchParams(base)
    if (selectedPriceTypeId) qs.set('price_type_id', selectedPriceTypeId)
    return `/checkout?${qs.toString()}`
  }, [car, searchParams, selectedPriceTypeId])

  useEffect(() => {
    if (!car?.price_types?.length) {
      setSelectedPriceTypeId('')
      return
    }
    setSelectedPriceTypeId(String(car.price_types[0].id))
  }, [car])

  if (loadState === 'loading') {
    return (
      <main className="car-detail-page">
        <p className="car-detail-muted">Loading…</p>
      </main>
    )
  }

  if (loadState === 'error' || !car) {
    return (
      <main className="car-detail-page">
        <p className="car-detail-error">Could not load this vehicle.</p>
        <Link to="/cars" className="car-detail-link">
          Back to list
        </Link>
      </main>
    )
  }

  const imageSrc = resolveStorageUrl(car.main_image_path)
  const characteristics = car.characteristics ?? []
  const rentalOptions = car.rental_options ?? []
  const priceTypes = car.price_types ?? []

  return (
    <main className="car-detail-page">
      <nav className="car-detail-breadcrumb" aria-label="Breadcrumb">
        <Link to="/cars" className="car-detail-link">
          Cars
        </Link>
        <span className="car-detail-muted" aria-hidden="true">
          {' / '}
        </span>
        <span>{car.name}</span>
      </nav>

      <article className="car-detail-card">
        <div className="car-detail-layout">
          <div className="car-detail-media">
            {imageSrc ? (
              <img src={imageSrc} alt={car.name} className="car-detail-image" />
            ) : (
              <div className="car-detail-placeholder" aria-hidden="true">
                No photo
              </div>
            )}
          </div>

          <div className="car-detail-body">
            <h1 className="car-detail-title">{car.name}</h1>
            {car.category?.name && (
              <p className="car-detail-muted">{car.category.name}</p>
            )}

            <dl className="car-detail-facts">
              <div>
                <dt>Capacity (bookable units)</dt>
                <dd>{car.units_available ?? '—'}</dd>
              </div>
              <div>
                <dt>Transmission</dt>
                <dd>{car.transmission || '—'}</dd>
              </div>
              <div>
                <dt>Fuel</dt>
                <dd>{car.fuel_type || '—'}</dd>
              </div>
            </dl>

            {car.description ? (
              <div className="car-detail-block">
                <h2 className="car-detail-heading">Description</h2>
                <p className="car-detail-text">{car.description}</p>
              </div>
            ) : null}

            <div className="car-detail-block">
              <h2 className="car-detail-heading">Price type</h2>
              {priceTypes.length === 0 ? (
                <p className="car-detail-muted">No prices configured for this vehicle.</p>
              ) : (
                <ul className="car-detail-list car-detail-list--choice">
                  {priceTypes.map((pt) => (
                    <li key={pt.id}>
                      <label className="car-detail-radio">
                        <input
                          type="radio"
                          name="price_type"
                          value={String(pt.id)}
                          checked={selectedPriceTypeId === String(pt.id)}
                          onChange={() => setSelectedPriceTypeId(String(pt.id))}
                        />
                        <span>
                          {pt.name}
                          <span className="car-detail-muted">
                            {' '}
                            — from {pt.from_price_per_day} / day
                          </span>
                        </span>
                      </label>
                      {pt.attribute_label ? (
                        <p className="car-detail-muted car-detail-sub">
                          {pt.attribute_label}
                          {pt.attribute_value_per_day != null &&
                          pt.attribute_value_per_day !== ''
                            ? `: ${pt.attribute_value_per_day}`
                            : ''}
                        </p>
                      ) : null}
                    </li>
                  ))}
                </ul>
              )}
            </div>

            {characteristics.length > 0 && (
              <div className="car-detail-block">
                <h2 className="car-detail-heading">Characteristics</h2>
                <ul className="car-detail-tags">
                  {characteristics.map((c) => (
                    <li key={c.id}>{c.display_text || c.name}</li>
                  ))}
                </ul>
              </div>
            )}

            {rentalOptions.length > 0 && (
              <div className="car-detail-block">
                <h2 className="car-detail-heading">Add-ons</h2>
                <ul className="car-detail-list">
                  {rentalOptions.map((opt) => (
                    <li key={opt.id}>
                      <span className="car-detail-list-main">{opt.name}</span>
                      <span className="car-detail-muted">
                        {opt.cost}
                        {opt.is_daily_cost ? ' / day' : ' (once)'}
                        {opt.has_quantity ? ' · quantity' : ''}
                        {opt.is_mandatory ? ' · required' : ''}
                      </span>
                      {opt.description ? (
                        <p className="car-detail-muted car-detail-sub">{opt.description}</p>
                      ) : null}
                    </li>
                  ))}
                </ul>
              </div>
            )}

            <div className="car-detail-actions">
              <Link
                to={checkoutHref}
                className="car-detail-button"
              >
                Continue to booking
              </Link>
              <Link to="/cars" className="car-detail-link car-detail-link--secondary">
                Cancel
              </Link>
            </div>
          </div>
        </div>
      </article>
    </main>
  )
}
