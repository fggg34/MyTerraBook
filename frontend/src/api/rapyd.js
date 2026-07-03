import { api } from '../api'

/**
 * Start a Rapyd hosted checkout for the 20% platform fee.
 * The remaining 80% is paid in cash to the host on arrival.
 */
export function initiateRapydCheckout(payload) {
  // payload: { order_id, total_price, currency?, host_id? }
  return api.post('/rapyd/initiate-checkout', payload)
}

export function getRapydCheckoutStatus(checkoutId) {
  return api.get(`/rapyd/checkout-status/${checkoutId}`)
}

/** Host confirms the cash balance was received on arrival. */
export function confirmRapydCashReceived(orderId) {
  return api.post(`/host/rapyd/confirm-cash/${orderId}`)
}

/** Admin: list of Rapyd payments. */
export function getAdminRapydPayments(params) {
  return api.get('/admin/rapyd/payments', { params })
}

/** Admin: list payment methods (with Rapyd config). */
export function getAdminPaymentMethods() {
  return api.get('/admin/payment-methods')
}

/** Admin: create/update the Rapyd card payment method config. */
export function saveRapydPaymentMethod(payload) {
  // payload: { access_key?, secret_key?, environment, is_active }
  return api.post('/admin/payment-methods/rapyd', payload)
}

/** Format a monetary amount with its currency code. */
export function formatMoney(amount, currency = 'USD') {
  const value = Number(amount || 0)
  try {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value)
  } catch {
    return `${currency} ${value.toFixed(2)}`
  }
}
