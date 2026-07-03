import { useCallback, useEffect, useRef, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { getRapydCheckoutStatus, getRapydOrderStatus } from '../../api/rapyd'
import BookingConfirmation from './BookingConfirmation'

/**
 * Rapyd return handler.
 *
 * Rapyd redirects the guest back to /booking/rapyd/success?order_id=&order_type=
 * (or /booking/rapyd/failed). This page polls the payment status until it is
 * marked as paid, then renders the confirmation. It falls back to checkout_id
 * for backwards compatibility with older redirect URLs.
 *
 * Props:
 *  - outcome: 'success' | 'failed'
 */
const MAX_POLLS = 10
const POLL_INTERVAL_MS = 2000

export default function RapydCheckout({ outcome = 'success' }) {
  const [params] = useSearchParams()
  const orderId = params.get('order_id')
  const orderType = params.get('order_type') || 'guesthouse'
  const checkoutId = params.get('checkout_id')
  // Ignore the un-substituted placeholder from older/misconfigured redirects.
  const validCheckoutId = checkoutId && !checkoutId.includes('{') ? checkoutId : null

  const [status, setStatus] = useState('loading') // loading | paid | pending | failed | error
  const [payment, setPayment] = useState(null)
  const pollsRef = useRef(0)
  const timerRef = useRef(null)

  const poll = useCallback(async () => {
    if (!orderId && !validCheckoutId) {
      setStatus('error')
      return
    }
    try {
      const { data } = orderId
        ? await getRapydOrderStatus({ order_id: orderId, order_type: orderType })
        : await getRapydCheckoutStatus(validCheckoutId)
      setPayment(data)
      if (data?.status === 'paid') {
        setStatus('paid')
        return
      }
      if (data?.status === 'failed') {
        setStatus('failed')
        return
      }
      pollsRef.current += 1
      if (pollsRef.current >= MAX_POLLS) {
        setStatus('pending')
        return
      }
      timerRef.current = setTimeout(poll, POLL_INTERVAL_MS)
    } catch {
      setStatus('error')
    }
  }, [orderId, orderType, validCheckoutId])

  useEffect(() => {
    if (outcome === 'failed') {
      setStatus('failed')
      return undefined
    }
    poll()
    return () => {
      if (timerRef.current) clearTimeout(timerRef.current)
    }
  }, [outcome, poll])

  if (status === 'paid' && payment) {
    return (
      <BookingConfirmation
        bookingReference={payment.order_id ? `#${payment.order_id}` : undefined}
        totalPrice={payment.total_price}
        platformFee={payment.platform_fee}
        cashDue={payment.cash_due_on_arrival}
        currency={payment.currency || 'USD'}
      />
    )
  }

  if (status === 'loading' || status === 'pending') {
    return (
      <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <div className="mx-auto h-10 w-10 animate-spin rounded-full border-4 border-teal-200 border-t-teal-600" />
        <h1 className="mt-6 text-xl font-semibold text-gray-900">Confirming your payment…</h1>
        <p className="mt-2 text-sm text-gray-500">
          {status === 'pending'
            ? 'Your payment is being processed. This can take a moment — you can safely check "My Bookings" shortly.'
            : 'Please wait while we confirm your card payment.'}
        </p>
        <Link to="/dashboard?type=guesthouse" className="mt-6 inline-block font-semibold text-teal-600">
          Go to My Bookings
        </Link>
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-lg px-4 py-16 text-center">
      <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-3xl">⚠️</div>
      <h1 className="mt-4 text-xl font-semibold text-gray-900">Payment not completed</h1>
      <p className="mt-2 text-sm text-gray-500">
        Your card payment was not completed. No platform fee has been charged. Please try again.
      </p>
      <Link
        to="/dashboard?type=guesthouse"
        className="mt-6 inline-block rounded-xl bg-teal-600 px-5 py-2.5 font-semibold text-white hover:bg-teal-700"
      >
        Back to My Bookings
      </Link>
    </div>
  )
}
