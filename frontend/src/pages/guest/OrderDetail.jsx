import PaymentBreakdownPanel from '../../components/payments/PaymentBreakdownPanel'

/**
 * Guest order detail — payment section.
 *
 * Props: { booking } where booking has payment fields:
 *   total_price, platform_fee, cash_due_on_arrival, currency,
 *   payment_status ('pending' | 'partially_paid' | 'confirmed'),
 *   rapyd_payment_id, rapyd_checkout_id
 */
export default function GuestOrderDetail({ booking }) {
  if (!booking) return null

  const onlineStatus = booking.payment_status === 'pending' ? 'pending' : 'paid'
  const cashStatus = booking.payment_status === 'confirmed' ? 'confirmed' : 'pending'

  const label =
    booking.payment_status === 'confirmed'
      ? 'Confirmed'
      : booking.payment_status === 'partially_paid'
        ? 'Partially Paid'
        : 'Pending'

  return (
    <section className="space-y-4">
      <div className="flex items-center gap-2">
        <h2 className="text-lg font-semibold text-gray-900">Payment</h2>
        <span className="rounded-full bg-teal-50 px-3 py-0.5 text-sm font-medium text-teal-700">
          {label}
        </span>
      </div>

      <PaymentBreakdownPanel
        variant="guest"
        totalPrice={booking.total_price}
        platformFee={booking.platform_fee}
        cashDue={booking.cash_due_on_arrival}
        currency={booking.currency || 'USD'}
        onlineStatus={onlineStatus}
        cashStatus={cashStatus}
      />
    </section>
  )
}
