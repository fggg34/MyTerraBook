import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'

function toApiDateTime(localValue) {
  if (!localValue) return ''
  const d = new Date(localValue)
  if (Number.isNaN(d.getTime())) return localValue
  return d.toISOString().slice(0, 19).replace('T', ' ')
}

export default function CheckoutPage() {
  const [searchParams] = useSearchParams()
  const defaults = useMemo(() => Object.fromEntries(searchParams.entries()), [searchParams])
  const [quote, setQuote] = useState(null)
  const [availability, setAvailability] = useState({ booked: [], blocked: [] })
  const [priceTypes, setPriceTypes] = useState([])
  const [saving, setSaving] = useState(false)
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
    if (!form.car_id) {
      setPriceTypes([])
      return
    }
    api
      .get(`/cars/${form.car_id}`)
      .then((res) => {
        const pts = res.data.data?.price_types || []
        setPriceTypes(pts)
        setForm((prev) => {
          if (prev.price_type_id) return prev
          const first = pts[0]
          if (!first) return prev
          return { ...prev, price_type_id: String(first.id) }
        })
      })
      .catch(() => setPriceTypes([]))
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

    const payload = {
      car_id: Number(form.car_id),
      price_type_id: Number(form.price_type_id),
      pickup_location_id: Number(form.pickup_location_id),
      dropoff_location_id: Number(form.dropoff_location_id),
      pickup_at: toApiDateTime(form.pickup_at),
      dropoff_at: toApiDateTime(form.dropoff_at),
    }
    if (form.coupon_code.trim()) {
      payload.coupon_code = form.coupon_code.trim()
    }

    api
      .post('/orders/quote', payload)
      .then((res) => setQuote(res.data))
      .catch(() => setQuote(null))
  }, [
    form.car_id,
    form.price_type_id,
    form.pickup_at,
    form.dropoff_at,
    form.pickup_location_id,
    form.dropoff_location_id,
    form.coupon_code,
  ])

  useEffect(() => {
    if (!form.car_id) {
      setAvailability({ booked: [], blocked: [] })
      return
    }

    api
      .get(`/cars/${form.car_id}/availability-calendar`)
      .then((res) =>
        setAvailability({
          booked: res.data.booked || [],
          blocked: res.data.blocked || [],
        }),
      )
      .catch(() => setAvailability({ booked: [], blocked: [] }))
  }, [form.car_id])

  const submitOrder = async (event) => {
    event.preventDefault()
    setSaving(true)
    try {
      const payload = {
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
      }
      await api.post('/orders', payload)
      alert('Order created.')
    } finally {
      setSaving(false)
    }
  }

  const currency = quote?.currency || 'EUR'

  return (
    <section>
      <h1>Checkout</h1>
      <form className="card" onSubmit={submitOrder}>
        <input
          placeholder="Car ID"
          value={form.car_id}
          onChange={(e) => setForm({ ...form, car_id: e.target.value })}
          required
        />
        <label>
          Price type
          <select
            value={form.price_type_id}
            onChange={(e) => setForm({ ...form, price_type_id: e.target.value })}
            required
          >
            <option value="">Select rate plan</option>
            {priceTypes.map((pt) => (
              <option key={pt.id} value={pt.id}>
                {pt.name}
              </option>
            ))}
          </select>
        </label>
        <input
          placeholder="Pickup location ID"
          value={form.pickup_location_id}
          onChange={(e) => setForm({ ...form, pickup_location_id: e.target.value })}
          required
        />
        <input
          placeholder="Dropoff location ID"
          value={form.dropoff_location_id}
          onChange={(e) => setForm({ ...form, dropoff_location_id: e.target.value })}
          required
        />
        <input
          type="datetime-local"
          value={form.pickup_at}
          onChange={(e) => setForm({ ...form, pickup_at: e.target.value })}
          required
        />
        <input
          type="datetime-local"
          value={form.dropoff_at}
          onChange={(e) => setForm({ ...form, dropoff_at: e.target.value })}
          required
        />
        <input
          placeholder="Full name"
          value={form.customer_name}
          onChange={(e) => setForm({ ...form, customer_name: e.target.value })}
          required
        />
        <input
          placeholder="Email"
          type="email"
          value={form.customer_email}
          onChange={(e) => setForm({ ...form, customer_email: e.target.value })}
          required
        />
        <input
          placeholder="Phone"
          value={form.customer_phone}
          onChange={(e) => setForm({ ...form, customer_phone: e.target.value })}
        />
        <input
          placeholder="Coupon code (optional)"
          value={form.coupon_code}
          onChange={(e) => setForm({ ...form, coupon_code: e.target.value })}
        />
        <button disabled={saving} type="submit">
          {saving ? 'Saving...' : 'Confirm order'}
        </button>
      </form>

      {quote && (
        <div className="card">
          <h3>Price summary</h3>
          <p>
            Rental ({quote.rental_days} days): {currency} {quote.rental_subtotal}
          </p>
          {quote.fees_subtotal && Number(quote.fees_subtotal) !== 0 && (
            <p>
              Fees: {currency} {quote.fees_subtotal}
            </p>
          )}
          <p>
            Extras: {currency} {quote.extras_subtotal}
          </p>
          <p>
            Discount: {currency} {quote.discount_amount}
          </p>
          <p>
            Tax: {currency} {quote.tax_amount}
          </p>
          <p>
            <strong>
              Total: {currency} {quote.total}
            </strong>
          </p>
        </div>
      )}

      {(availability.booked.length > 0 || availability.blocked.length > 0) && (
        <div className="card">
          <h3>Unavailable dates</h3>
          {availability.booked.map((range) => (
            <p key={`booked-${range.id}`}>
              Order: {range.start} to {range.end}
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
