import { api, resolveStorageUrl } from '../api'

export { resolveStorageUrl }

export function getHostDashboard() {
  return api.get('/host/dashboard')
}

export function getHostCatalog(type) {
  return api.get(`/host/catalog/${type}`)
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

export function deleteHostCarDailyFare(carId, fareId) {
  return api.delete(`/host/cars/${carId}/daily-fares/${fareId}`)
}

export function getHostCarHourlyFares(id) {
  return api.get(`/host/cars/${id}/hourly-fares`)
}

export function createHostCarHourlyFare(id, payload) {
  return api.post(`/host/cars/${id}/hourly-fares`, payload)
}

export function getHostCarExtraHourFares(id) {
  return api.get(`/host/cars/${id}/extra-hour-fares`)
}

export function createHostCarExtraHourFare(id, payload) {
  return api.post(`/host/cars/${id}/extra-hour-fares`, payload)
}

export function getHostCarAvailability(id) {
  return api.get(`/host/cars/${id}/availability-blocks`)
}

export function addHostCarAvailability(id, payload) {
  return api.post(`/host/cars/${id}/availability-blocks`, payload)
}

export function getHostCarBookings(params) {
  return api.get('/host/bookings/cars', { params })
}

export function getHostGuestHouseBookings(params) {
  return api.get('/host/bookings/guest-houses', { params })
}

export function updateHostCarBookingStatus(orderId, status) {
  return api.patch(`/host/bookings/cars/${orderId}/status`, { status })
}

export function updateHostGuestHouseBookingStatus(bookingId, payload) {
  return api.patch(`/host/bookings/guest-houses/${bookingId}/status`, payload)
}
