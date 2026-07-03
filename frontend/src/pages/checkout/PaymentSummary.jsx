import { useMemo, useState } from 'react'
import { initiateRapydCheckout, formatMoney } from '../../api/rapyd'

/**
 * Guest-facing payment summary card shown at the booking/payment step.
 *
 * Shows the 20% / 80% split and starts the Rapyd hosted checkout for the 20%
 * platform fee. The 80% is collected in cash by the host on arrival.
 *
 * Props:
 *  - orderId (number, required)
 *  - totalPrice (number, required) — full listing price
 *  - currency (string, default 'USD')
 *  - hostId (number, optional)
 *  - commissionRate (number, default 0.20)
 */
export default function PaymentSummary({
  orderId,
  totalPrice,
  currency = 'USD',
  hostId,
  commissionRate = 0.2,
}) {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const { platformFee, cashDue } = useMemo(() => {
    const fee = Math.round(Number(totalPrice) * commissionRate * 100) / 100
    return { platformFee: fee, cashDue: Math.round((Number(totalPrice) - fee) * 100) / 100 }
  }, [totalPrice, commissionRate])

  const percentOnline = Math.round(commissionRate * 100)
  const percentCash = 100 - percentOnline

  async function handlePay() {
    setError('')
    setLoading(true)
    try {
      const { data } = await initiateRapydCheckout({
        order_id: orderId,
        total_price: Number(totalPrice),
        currency,
        host_id: hostId,
      })
      if (data?.checkout_url) {
        window.location.href = data.checkout_url
        return
      }
      setError('Could not start the card payment. Please try again.')
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not start the card payment. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <h3 className="text-xs font-semibold uppercase tracking-wider text-gray-500">Payment Summary</h3>

      <div className="mt-4 flex items-center justify-between border-b border-gray-100 pb-3">
        <span className="text-gray-700">Total Price</span>
        <span className="text-lg font-semibold text-gray-900">{formatMoney(totalPrice, currency)}</span>
      </div>

      <div className="mt-3 flex items-start justify-between">
        <div>
          <p className="font-medium text-emerald-700">✅ Pay Now (Card)</p>
          <p className="text-xs text-gray-500">Platform booking fee</p>
        </div>
        <span className="text-lg font-bold text-emerald-700">
          {formatMoney(platformFee, currency)} <span className="text-xs font-normal">({percentOnline}%)</span>
        </span>
      </div>

      <div className="mt-4 flex items-start justify-between rounded-lg bg-amber-50 p-3">
        <div>
          <p className="font-medium text-amber-700">💵 Pay on Arrival (Cash)</p>
          <p className="text-xs text-amber-600">Pay directly to the host on arrival</p>
        </div>
        <span className="text-lg font-bold text-amber-700">
          {formatMoney(cashDue, currency)} <span className="text-xs font-normal">({percentCash}%)</span>
        </span>
      </div>

      {error ? <p className="mt-4 text-sm text-red-600">{error}</p> : null}

      <button
        type="button"
        onClick={handlePay}
        disabled={loading || !orderId || !totalPrice}
        className="mt-5 w-full rounded-xl bg-teal-600 px-4 py-3 text-center font-semibold text-white transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60"
      >
        {loading ? 'Redirecting…' : `Pay ${formatMoney(platformFee, currency)} by Card Now`}
      </button>
    </div>
  )
}
