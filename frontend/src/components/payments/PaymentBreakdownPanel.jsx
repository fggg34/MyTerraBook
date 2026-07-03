import { formatMoney } from '../../api/rapyd'

/**
 * Reusable payment breakdown panel used across admin/guest/host order pages.
 *
 * Props:
 *  - totalPrice, platformFee, cashDue (numbers)
 *  - currency (string)
 *  - onlineStatus ('pending' | 'paid' | 'failed' | 'refunded')
 *  - cashStatus ('pending' | 'confirmed')
 *  - paymentId, checkoutId (strings, optional)
 *  - variant ('admin' | 'guest' | 'host')
 */
export default function PaymentBreakdownPanel({
  totalPrice,
  platformFee,
  cashDue,
  currency = 'USD',
  onlineStatus = 'pending',
  cashStatus = 'pending',
  paymentId,
  checkoutId,
  variant = 'admin',
}) {
  const onlinePaid = onlineStatus === 'paid'
  const cashConfirmed = cashStatus === 'confirmed'

  const total = Number(totalPrice) || 0
  const onlinePct = total ? Math.round((Number(platformFee) / total) * 100) : null
  const cashPct = onlinePct != null ? 100 - onlinePct : null
  const onlinePctLabel = onlinePct != null ? ` (${onlinePct}%)` : ''
  const cashPctLabel = cashPct != null ? ` (${cashPct}%)` : ''

  return (
    <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <h3 className="text-xs font-semibold uppercase tracking-wider text-gray-500">Payment Breakdown</h3>

      <div className="mt-4 space-y-3 text-sm">
        <div className="flex items-center justify-between border-b border-gray-100 pb-3">
          <span className="text-gray-700">Total Booking Value</span>
          <span className="font-semibold text-gray-900">{formatMoney(totalPrice, currency)}</span>
        </div>

        <div className="flex items-center justify-between">
          <span className="text-emerald-700">✅ Platform Fee{onlinePctLabel}{variant === 'host' ? ' — handled by platform' : ' — paid online'}</span>
          <span className="font-semibold text-emerald-700">
            {formatMoney(platformFee, currency)}
            <StatusPill tone={onlinePaid ? 'green' : 'gray'}>{onlinePaid ? 'PAID' : onlineStatus.toUpperCase()}</StatusPill>
          </span>
        </div>

        <div className="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
          <span className="font-medium text-amber-700">
            💵 Cash on Arrival{cashPctLabel}{variant === 'host' ? ' — you collect' : ''}
          </span>
          <span className="font-bold text-amber-700">
            {formatMoney(cashDue, currency)}
            <StatusPill tone={cashConfirmed ? 'green' : 'amber'}>
              {cashConfirmed ? 'RECEIVED' : 'DUE ON ARRIVAL'}
            </StatusPill>
          </span>
        </div>
      </div>

      {variant === 'admin' ? (
        <dl className="mt-5 space-y-2 border-t border-gray-100 pt-4 text-sm">
          <Row label="Online Payment Status" value={onlineStatus.toUpperCase()} />
          <Row label="Cash Payment Status" value={cashConfirmed ? 'RECEIVED' : 'PENDING'} />
          {paymentId ? <Row label="Rapyd Payment ID" value={paymentId} mono /> : null}
          {checkoutId ? <Row label="Rapyd Checkout ID" value={checkoutId} mono /> : null}
        </dl>
      ) : null}
    </div>
  )
}

function StatusPill({ tone, children }) {
  const tones = {
    green: 'bg-emerald-100 text-emerald-700',
    amber: 'bg-amber-100 text-amber-700',
    gray: 'bg-gray-100 text-gray-600',
  }
  return <span className={`ml-2 rounded px-2 py-0.5 text-xs font-medium ${tones[tone] || tones.gray}`}>{children}</span>
}

function Row({ label, value, mono }) {
  return (
    <div className="flex items-center justify-between">
      <dt className="text-gray-500">{label}</dt>
      <dd className={`text-gray-800 ${mono ? 'font-mono text-xs' : ''}`}>{value}</dd>
    </div>
  )
}
