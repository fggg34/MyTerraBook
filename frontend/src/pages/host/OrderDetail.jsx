import { useState } from 'react'
import PaymentBreakdownPanel from '../../components/payments/PaymentBreakdownPanel'
import { confirmRapydCashReceived, formatMoney } from '../../api/rapyd'

/**
 * Host order detail — payment info with a "Mark Cash Received" action.
 *
 * Props:
 *  - booking: { id, total_price, platform_fee, cash_due_on_arrival, currency, payment_status }
 *  - onConfirmed: optional callback after cash is confirmed
 */
export default function HostOrderDetail({ booking, onConfirmed }) {
  const [status, setStatus] = useState(booking?.payment_status || 'pending')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  if (!booking) return null

  const onlineStatus = status === 'pending' ? 'pending' : 'paid'
  const cashStatus = status === 'confirmed' ? 'confirmed' : 'pending'
  const currency = booking.currency || 'USD'

  async function handleConfirm() {
    setError('')
    setLoading(true)
    try {
      const { data } = await confirmRapydCashReceived(booking.id)
      setStatus(data?.payment_status || 'confirmed')
      onConfirmed?.(data)
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not confirm cash receipt. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <section className="space-y-4">
      <h2 className="text-lg font-semibold text-gray-900">Payment Info</h2>

      <PaymentBreakdownPanel
        variant="host"
        totalPrice={booking.total_price}
        platformFee={booking.platform_fee}
        cashDue={booking.cash_due_on_arrival}
        currency={currency}
        onlineStatus={onlineStatus}
        cashStatus={cashStatus}
      />

      {error ? <p className="text-sm text-red-600">{error}</p> : null}

      {cashStatus === 'confirmed' ? (
        <p className="rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
          ✅ Cash balance of {formatMoney(booking.cash_due_on_arrival, currency)} marked as received.
        </p>
      ) : (
        <button
          type="button"
          onClick={handleConfirm}
          disabled={loading || onlineStatus !== 'paid'}
          className="rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
          title={onlineStatus !== 'paid' ? 'Platform fee not paid yet' : undefined}
        >
          {loading ? 'Saving…' : '✅ Mark Cash Received'}
        </button>
      )}
    </section>
  )
}
