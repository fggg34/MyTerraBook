import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'

export default function CheckoutPage() {
  const [searchParams] = useSearchParams()
  const defaults = useMemo(() => Object.fromEntries(searchParams.entries()), [searchParams])
  const [quote, setQuote] = useState(null)
  const [availability, setAvailability] = useState({ booked: [], blocked: [] })
  const [saving, setSaving] = useState(false)
  const [form, setForm] = useState({
    car_id: defaults.car_id || '',
    pickup_location_id: defaults.pickup_location_id || '',
    dropoff_location_id: defaults.dropoff_location_id || '',
    pickup_at: defaults.pickup_at || '',
    dropoff_at: defaults.dropoff_at || '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
  })

  useEffect(() => {
    if (!form.car_id || !form.pickup_at || !form.dropoff_at || !form.pickup_location_id || !form.dropoff_location_id) return

    api.post('/bookings/quote', form)
      .then((res) => setQuote(res.data))
      .catch(() => setQuote(null))
  }, [form])

  useEffect(() => {
    if (!form.car_id) {
      setAvailability({ booked: [], blocked: [] })
      return
    }

    api
      .get(`/cars/${form.car_id}/availability-calendar`)
      .then((res) => setAvailability({
        booked: res.data.booked || [],
        blocked: res.data.blocked || [],
      }))
      .catch(() => setAvailability({ booked: [], blocked: [] }))
  }, [form.car_id])

  const submitBooking = async (event) => {
    event.preventDefault()
    setSaving(true)
    try {
      await api.post('/bookings', form)
      alert('Booking created.')
    } finally {
      setSaving(false)
    }
  }

  return (
    <section>
      <h1>Checkout</h1>
      <form className="card" onSubmit={submitBooking}>
        <input placeholder="Car ID" value={form.car_id} onChange={(e) => setForm({ ...form, car_id: e.target.value })} required />
        <input placeholder="Pickup location ID" value={form.pickup_location_id} onChange={(e) => setForm({ ...form, pickup_location_id: e.target.value })} required />
        <input placeholder="Dropoff location ID" value={form.dropoff_location_id} onChange={(e) => setForm({ ...form, dropoff_location_id: e.target.value })} required />
        <input type="datetime-local" value={form.pickup_at} onChange={(e) => setForm({ ...form, pickup_at: e.target.value })} required />
        <input type="datetime-local" value={form.dropoff_at} onChange={(e) => setForm({ ...form, dropoff_at: e.target.value })} required />
        <input placeholder="Full name" value={form.customer_name} onChange={(e) => setForm({ ...form, customer_name: e.target.value })} required />
        <input placeholder="Email" type="email" value={form.customer_email} onChange={(e) => setForm({ ...form, customer_email: e.target.value })} required />
        <input placeholder="Phone" value={form.customer_phone} onChange={(e) => setForm({ ...form, customer_phone: e.target.value })} />
        <button disabled={saving} type="submit">{saving ? 'Saving...' : 'Confirm booking'}</button>
      </form>

      {quote && (
        <div className="card">
          <h3>Price summary</h3>
          <p>Rental: ${quote.rental_subtotal}</p>
          <p>Extras: ${quote.extras_subtotal}</p>
          <p>Discount: ${quote.discount_amount}</p>
          <p><strong>Total: ${quote.total}</strong></p>
        </div>
      )}

      {(availability.booked.length > 0 || availability.blocked.length > 0) && (
        <div className="card">
          <h3>Unavailable dates</h3>
          {availability.booked.map((range) => (
            <p key={`booked-${range.id}`}>
              Booking: {range.start} to {range.end}
            </p>
          ))}
          {availability.blocked.map((range) => (
            <p key={`blocked-${range.id}`}>
              Blocked: {range.start} to {range.end} {range.reason ? `(${range.reason})` : ''}
            </p>
          ))}
        </div>
      )}
    </section>
  )
}
