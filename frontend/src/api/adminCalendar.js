import { api } from '../api'

function buildParams(filters = {}) {
  const params = new URLSearchParams()
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      params.set(key, String(value))
    }
  })
  return params
}

export async function fetchCalendarResources(filters = {}) {
  const { data } = await api.get('/admin/calendar/resources', { params: buildParams(filters) })
  return data
}

export async function fetchCalendarEvents(filters = {}) {
  const { data } = await api.get('/admin/calendar/events', { params: buildParams(filters) })
  return data
}

export async function fetchCalendarSummary(filters = {}) {
  const { data } = await api.get('/admin/calendar/summary', { params: buildParams(filters) })
  return data.data
}

export async function fetchCalendarAlerts(filters = {}) {
  const { data } = await api.get('/admin/calendar/alerts', { params: buildParams(filters) })
  return data.data
}

export async function fetchCalendarEventDetail(type, id) {
  const { data } = await api.get(`/admin/calendar/events/${type}/${id}`)
  return data.data
}
