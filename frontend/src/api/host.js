import { api, resolveStorageUrl } from '../api'

export { resolveStorageUrl }

export function getHostDashboard() {
  return api.get('/host/dashboard')
}

export function getHostCatalog(type) {
  return api.get(`/host/catalog/${type}`)
}

export function createHostLocation(payload) {
  return api.post('/host/catalog/locations', payload)
}

/** Public catalog, no auth required; used as fallback for host vehicle editor. */
export function getPublicMainCategories() {
  return api.get('/main-categories')
}

export function getPublicSubCategories() {
  return api.get('/sub-categories')
}

export function listHostGuestHouses(params) {
  return api.get('/host/guest-houses', { params })
}

export function getHostGuestHouse(id) {
  return api.get(`/host/guest-houses/${id}`)
}

export function createHostGuestHouse(payload) {
  return api.post('/host/guest-houses', payload)
}

export function updateHostGuestHouse(id, payload) {
  return api.patch(`/host/guest-houses/${id}`, payload)
}

export function deleteHostGuestHouse(id) {
  return api.delete(`/host/guest-houses/${id}`)
}

export function submitHostGuestHouse(id) {
  return api.post(`/host/guest-houses/${id}/submit`)
}

export function uploadHostGuestHouseImages(id, formData) {
  return api.post(`/host/guest-houses/${id}/images`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}

export function deleteHostGuestHouseImage(id, imageId) {
  return api.delete(`/host/guest-houses/${id}/images/${imageId}`)
}

export function getHostGuestHouseAvailability(id) {
  return api.get(`/host/guest-houses/${id}/availability-blocks`)
}

export function addHostGuestHouseAvailability(id, payload) {
  return api.post(`/host/guest-houses/${id}/availability-blocks`, payload)
}

export function removeHostGuestHouseAvailability(id, blockId) {
  return api.delete(`/host/guest-houses/${id}/availability-blocks/${blockId}`)
}

export function listHostCars(params) {
  return api.get('/host/cars', { params })
}

export function getHostCar(id) {
  return api.get(`/host/cars/${id}`)
}

export function createHostCar(payload) {
  return api.post('/host/cars', payload)
}

export function updateHostCar(id, payload) {
  return api.patch(`/host/cars/${id}`, payload)
}

export function deleteHostCar(id) {
  return api.delete(`/host/cars/${id}`)
}

export function submitHostCar(id) {
  return api.post(`/host/cars/${id}/submit`)
}

export function uploadHostCarImages(id, formData) {
  return api.post(`/host/cars/${id}/images`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}

export function syncHostCarRelations(id, payload) {
  return api.patch(`/host/cars/${id}/relations`, payload)
}

export function getHostCarUnits(id) {
  return api.get(`/host/cars/${id}/units`)
}

export function createHostCarUnit(id, payload) {
  return api.post(`/host/cars/${id}/units`, payload)
}

export function deleteHostCarUnit(carId, unitId) {
  return api.delete(`/host/cars/${carId}/units/${unitId}`)
}

export function getHostCarDailyFares(id) {
  return api.get(`/host/cars/${id}/daily-fares`)
}

export function createHostCarDailyFare(id, payload) {
  return api.post(`/host/cars/${id}/daily-fares`, payload)
}

export function updateHostCarDailyFare(carId, fareId, payload) {
  return api.patch(`/host/cars/${carId}/daily-fares/${fareId}`, payload)
}

export function deleteHostCarDailyFare(carId, fareId) {
  return api.delete(`/host/cars/${carId}/daily-fares/${fareId}`)
}

export function getHostCarHourlyFares(id) {
  return api.get(`/host/cars/${id}/hourly-fares`)
}

export function createHostCarHourlyFare(id, payload) {
  return api.post(`/host/cars/${id}/hourly-fares`, payload)
}

export function updateHostCarHourlyFare(carId, fareId, payload) {
  return api.patch(`/host/cars/${carId}/hourly-fares/${fareId}`, payload)
}

export function deleteHostCarHourlyFare(carId, fareId) {
  return api.delete(`/host/cars/${carId}/hourly-fares/${fareId}`)
}

export function getHostCarExtraHourFares(id) {
  return api.get(`/host/cars/${id}/extra-hour-fares`)
}

export function createHostCarExtraHourFare(id, payload) {
  return api.post(`/host/cars/${id}/extra-hour-fares`, payload)
}

export function updateHostCarExtraHourFare(carId, fareId, payload) {
  return api.patch(`/host/cars/${carId}/extra-hour-fares/${fareId}`, payload)
}

export function deleteHostCarExtraHourFare(carId, fareId) {
  return api.delete(`/host/cars/${carId}/extra-hour-fares/${fareId}`)
}

export function getHostCarAvailability(id) {
  return api.get(`/host/cars/${id}/availability-blocks`)
}

export function addHostCarAvailability(id, payload) {
  return api.post(`/host/cars/${id}/availability-blocks`, payload)
}

export function removeHostCarAvailability(id, blockId) {
  return api.delete(`/host/cars/${id}/availability-blocks/${blockId}`)
}

export function getHostCarSpecialPrices(id) {
  return api.get(`/host/cars/${id}/special-prices`)
}

export function addHostCarSpecialPrice(id, payload) {
  return api.post(`/host/cars/${id}/special-prices`, payload)
}

export function updateHostCarSpecialPrice(carId, priceId, payload) {
  return api.patch(`/host/cars/${carId}/special-prices/${priceId}`, payload)
}

export function removeHostCarSpecialPrice(id, priceId) {
  return api.delete(`/host/cars/${id}/special-prices/${priceId}`)
}

export function getHostCarLocationFees(id) {
  return api.get(`/host/cars/${id}/location-fees`)
}

export function createHostCarLocationFee(id, payload) {
  return api.post(`/host/cars/${id}/location-fees`, payload)
}

export function updateHostCarLocationFee(carId, feeId, payload) {
  return api.patch(`/host/cars/${carId}/location-fees/${feeId}`, payload)
}

export function deleteHostCarLocationFee(carId, feeId) {
  return api.delete(`/host/cars/${carId}/location-fees/${feeId}`)
}

export function getHostCarOutOfHoursFees(id) {
  return api.get(`/host/cars/${id}/out-of-hours-fees`)
}

export function createHostCarOutOfHoursFee(id, payload) {
  return api.post(`/host/cars/${id}/out-of-hours-fees`, payload)
}

export function updateHostCarOutOfHoursFee(carId, feeId, payload) {
  return api.patch(`/host/cars/${carId}/out-of-hours-fees/${feeId}`, payload)
}

export function deleteHostCarOutOfHoursFee(carId, feeId) {
  return api.delete(`/host/cars/${carId}/out-of-hours-fees/${feeId}`)
}

export function getHostCarBookings(params) {
  return api.get('/host/bookings/cars', { params })
}

export function getHostCarBooking(id) {
  return api.get(`/host/bookings/cars/${id}`)
}

export function getHostGuestHouseBookings(params) {
  return api.get('/host/bookings/guest-houses', { params })
}

export function getHostGuestHouseBooking(id) {
  return api.get(`/host/bookings/guest-houses/${id}`)
}

export function applyHostBookingChangeRequest(id, adminResponse, requestedChanges) {
  return api.post(`/host/booking-change-requests/${id}/apply`, {
    admin_response: adminResponse || undefined,
    requested_changes: requestedChanges || undefined,
  })
}

export function rejectHostBookingChangeRequest(id, adminResponse) {
  return api.post(`/host/booking-change-requests/${id}/reject`, {
    admin_response: adminResponse,
  })
}

export function previewHostCarOrderModification(orderId, changes) {
  return api.post(`/host/bookings/cars/${orderId}/preview-modification`, changes)
}

export function updateHostCarOrder(orderId, changes) {
  return api.patch(`/host/bookings/cars/${orderId}`, changes)
}

export function getHostIntegrations() {
  return api.get('/host/integrations')
}

export function regenerateHostIntegrationToken() {
  return api.post('/host/integration-token/regenerate')
}

export function fetchHostBlockedDays(token, params = {}) {
  return api.get('/integrations/blocked-days', {
    params,
    headers: { 'X-Integration-Token': token },
  })
}
