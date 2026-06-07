import { api } from '../api'

export function getMeHistory(params) {
  return api.get('/me/history', { params })
}

export function getMeHistoryExportUrl() {
  return '/me/history/export.csv'
}

export function getMeOrders(params) {
  return api.get('/me/orders', { params })
}

export function getMeGuestHouseBookings(params) {
  return api.get('/me/guest-house-bookings', { params })
}

export function getMeGuestHouseBooking(ref) {
  return api.get(`/me/guest-house-bookings/${ref}`)
}

export function cancelMeGuestHouseBooking(ref, reason) {
  return api.post(`/me/guest-house-bookings/${ref}/cancel`, { reason })
}

export function getMeOrderCalendarUrl(orderId) {
  return `/me/orders/${orderId}/calendar.ics`
}

export function getMeOrderContractUrl(orderId) {
  return `/me/orders/${orderId}/contract.pdf`
}

export function getMeGuestHouseContractUrl(ref) {
  return `/me/guest-house-bookings/${ref}/contract.pdf`
}

export function updateProfile(payload) {
  return api.patch('/me/profile', payload)
}

export function updatePassword(payload) {
  return api.patch('/me/password', payload)
}
