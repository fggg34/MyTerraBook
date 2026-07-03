import PaymentBreakdownPanel from '../../../components/payments/PaymentBreakdownPanel'

/**
 * Admin order detail — full payment breakdown panel.
 *
 * Props:
 *  - order: booking with payment fields and (optionally) a rapyd payment:
 *      total_price, platform_fee, cash_due_on_arrival, currency,
 *      payment_status, rapyd_payment_id, rapyd_checkout_id
 */
export default function AdminOrderPaymentPanel({ order }) {
  if (!order) return null

  const onlineStatus = order.payment_status === 'pending' ? 'pending' : 'paid'
  const cashStatus = order.payment_status === 'confirmed' ? 'confirmed' : 'pending'

  return (
    <PaymentBreakdownPanel
      variant="admin"
      totalPrice={order.total_price}
      platformFee={order.platform_fee}
      cashDue={order.cash_due_on_arrival}
      currency={order.currency || 'USD'}
      onlineStatus={onlineStatus}
      cashStatus={cashStatus}
      paymentId={order.rapyd_payment_id}
      checkoutId={order.rapyd_checkout_id}
    />
  )
}
