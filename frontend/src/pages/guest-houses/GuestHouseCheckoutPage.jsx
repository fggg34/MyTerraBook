import { useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { api } from '../../api'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import LoadingSpinner from '../../components/ui/LoadingSpinner'
import { formatCurrencyFromCents } from '../../utils/format'

export default function GuestHouseCheckoutPage() {
  const { state } = useLocation()
  const navigate = useNavigate()
  const { user } = useAuth()
  const { toast } = useToast()
  const [saving, setSaving] = useState(false)
  const [success, setSuccess] = useState(null)
  const [form, setForm] = useState({
    guest_name: user?.name || '',
    guest_email: user?.email || '',
    guest_phone: user?.phone || '',
    special_requests: '',
  })

  if (!state?.house || !state?.quote) {
    return (
      <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <p className="text-slate-600">No booking in progress.</p>
        <Link to="/guest-houses" className="btn-primary mt-4 inline-flex">
          Browse stays
        </Link>
      </div>
    )
  }

  const { house, quote, check_in, check_out, guests_count } = state

  const submit = async (e) => {
    e.preventDefault()
    setSaving(true)
    try {
      const { data } = await api.post('/guest-houses/bookings', {
        guest_house_slug: house.slug,
        check_in,
        check_out,
        guests_count,
        ...form,
      })
      setSuccess(data?.data)
      toast('Booking created successfully', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Booking failed', 'error')
    } finally {
      setSaving(false)
    }
  }

  if (success) {
    return (
      <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <h1 className="text-2xl font-bold text-brand-950">Booking confirmed</h1>
        <p className="mt-2 text-slate-600">Reference</p>
        <p className="font-mono text-xl font-bold text-accent">{success.booking_reference}</p>
        <p className="mt-4 text-sm text-slate-600">
          {house.name} · {check_in} → {check_out}
        </p>
        <p className="mt-2 font-semibold">{success.total_formatted}</p>
        {user ? (
          <Link to="/dashboard" className="btn-primary mt-8 inline-flex">
            View my stays
          </Link>
        ) : (
          <Link to="/guest-houses" className="btn-primary mt-8 inline-flex">
            Done
          </Link>
        )}
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
      <h1 className="section-title">Complete your booking</h1>
      <div className="mt-8 grid gap-8 lg:grid-cols-2">
        <form onSubmit={submit} className="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-card">
          <h2 className="font-bold text-brand-950">Guest details</h2>
          {['guest_name', 'guest_email', 'guest_phone'].map((field) => (
            <div key={field}>
              <label className="text-sm font-medium text-slate-600">
                {field.replace('guest_', '').replace('_', ' ')}
              </label>
              <input
                type={field === 'guest_email' ? 'email' : 'text'}
                required={field !== 'guest_phone'}
                className="input-field mt-1 w-full"
                value={form[field]}
                onChange={(e) => setForm((s) => ({ ...s, [field]: e.target.value }))}
              />
            </div>
          ))}
          <div>
            <label className="text-sm font-medium text-slate-600">Special requests</label>
            <textarea
              className="input-field mt-1 w-full"
              rows={3}
              maxLength={1000}
              value={form.special_requests}
              onChange={(e) => setForm((s) => ({ ...s, special_requests: e.target.value }))}
            />
          </div>
          <button type="submit" className="btn-primary w-full" disabled={saving}>
            {saving ? <LoadingSpinner size="sm" /> : 'Confirm booking'}
          </button>
        </form>

        <div className="rounded-xl border border-slate-200 bg-slate-50 p-6">
          <h2 className="font-bold text-brand-950">{house.name}</h2>
          <p className="text-sm text-slate-600">
            {check_in} → {check_out} · {guests_count} guests · {quote.nights} nights
          </p>
          <ul className="mt-4 space-y-2 text-sm">
            <li className="flex justify-between">
              <span>Accommodation</span>
              <span>{formatCurrencyFromCents(quote.base_total, quote.currency)}</span>
            </li>
            {quote.cleaning_fee > 0 && (
              <li className="flex justify-between">
                <span>Cleaning</span>
                <span>{formatCurrencyFromCents(quote.cleaning_fee, quote.currency)}</span>
              </li>
            )}
            {quote.tax_amount > 0 && (
              <li className="flex justify-between">
                <span>Tax</span>
                <span>{formatCurrencyFromCents(quote.tax_amount, quote.currency)}</span>
              </li>
            )}
            <li className="flex justify-between border-t border-slate-200 pt-2 font-bold">
              <span>Total</span>
              <span>{quote.total_formatted}</span>
            </li>
          </ul>
          <button
            type="button"
            className="mt-4 text-sm text-accent hover:underline"
            onClick={() => navigate(-1)}
          >
            ← Back to property
          </button>
        </div>
      </div>
    </div>
  )
}
