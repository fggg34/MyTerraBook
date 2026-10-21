import { useEffect, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../../api'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { carenPriceLabel, formatCarenPrice } from '../../utils/carenPricing'

export default function CarenPricePanel({ car }) {
  const [query] = useSearchParams()
  const formatter = useFormatPrice()
  const [trip, setTrip] = useState(() => ({
    pickup_location_id: query.get('pickup_location_id') || '',
    dropoff_location_id: query.get('dropoff_location_id') || '',
    pickup_at: query.get('pickup_at')?.replace(' ', 'T').slice(0, 16) || '',
    dropoff_at: query.get('dropoff_at')?.replace(' ', 'T').slice(0, 16) || '',
  }))
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)
  const [attempt, setAttempt] = useState(0)
  const complete = Object.values(trip).every(Boolean)

  useEffect(() => {
    setResult(null)
    if (!complete || trip.dropoff_at <= trip.pickup_at) {
      setLoading(false)
      return undefined
    }
    let cancelled = false
    setLoading(true)
    const timer = window.setTimeout(() => {
      api.get(`/cars/${car.id}/caren-price`, { params: trip })
        .then(({ data }) => { if (!cancelled) setResult(data) })
        .catch(() => { if (!cancelled) setResult({ status: 'error' }) })
        .finally(() => { if (!cancelled) setLoading(false) })
    }, 350)
    return () => { cancelled = true; window.clearTimeout(timer) }
  }, [car.id, trip, complete, attempt])

  const update = (event) => {
    setResult(null)
    setTrip((previous) => ({ ...previous, [event.target.name]: event.target.value }))
  }
  const pricing = result?.pricing
  const fields = [
    ['pickup_location_id', 'Pick-up location', car.pickup_locations || []],
    ['dropoff_location_id', 'Drop-off location', car.dropoff_locations || []],
  ]

  return (
    <div className="listing-bcard">
      <h3>Check rental price</h3>
      {fields.map(([name, label, locations]) => (
        <label key={name} style={{ display: 'block', margin: '12px 0' }}>
          {label}
          <select name={name} value={trip[name]} onChange={update} style={{ display: 'block', width: '100%', padding: 10 }}>
            <option value="">Select location</option>
            {locations.map((location) => <option key={location.id} value={location.id}>{location.name}</option>)}
          </select>
        </label>
      ))}
      {[['pickup_at', 'Pick-up date and time'], ['dropoff_at', 'Drop-off date and time']].map(([name, label]) => (
        <label key={name} style={{ display: 'block', margin: '12px 0' }}>
          {label}
          <input type="datetime-local" name={name} value={trip[name]} onChange={update} style={{ display: 'block', width: '100%', padding: 10 }} />
        </label>
      ))}
      <p>Times are local to the rental location.</p>
      <div aria-live="polite">
        {loading ? <p>Checking rental price…</p> : pricing ? (
          <>
            <p><strong>{formatCarenPrice(pricing.rental_subtotal, pricing.currency, formatter)}</strong> rental estimate for {pricing.rental_days} days</p>
            <p>{formatCarenPrice(pricing.daily_price, pricing.currency, formatter)} per day on average</p>
          </>
        ) : <p>{complete && trip.dropoff_at <= trip.pickup_at ? 'Drop-off must be after pick-up.' : carenPriceLabel(result?.status)}</p>}
      </div>
      {result?.status === 'error' && <button type="button" className="book-btn" onClick={() => setAttempt((value) => value + 1)}>Retry price check</button>}
      <p className="listing-fees-note">Rental estimate only. Extras and additional charges may apply. Online booking is not yet available for this vehicle.</p>
    </div>
  )
}
