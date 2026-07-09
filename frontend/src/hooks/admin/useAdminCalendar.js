import { useQuery } from '@tanstack/react-query'
import {
  fetchCalendarAlerts,
  fetchCalendarEventDetail,
  fetchCalendarEvents,
  fetchCalendarResources,
  fetchCalendarSummary,
} from '../../api/adminCalendar'

export function useAdminCalendarResources(filters, options = {}) {
  const perPage = options.perPage ?? 100
  return useQuery({
    queryKey: ['admin-calendar-resources', filters, perPage],
    queryFn: () => fetchCalendarResources({ ...filters, per_page: perPage }),
    staleTime: 60_000,
  })
}

export function useAdminCalendarEvents(range, filters) {
  return useQuery({
    queryKey: ['admin-calendar-events', range?.start, range?.end, filters],
    queryFn: () => fetchCalendarEvents({
      start: range.start,
      end: range.end,
      ...filters,
    }),
    enabled: Boolean(range?.start && range?.end),
    staleTime: 30_000,
  })
}

export function useAdminCalendarSummary(range, filters) {
  return useQuery({
    queryKey: ['admin-calendar-summary', range?.start, range?.end, filters],
    queryFn: () => fetchCalendarSummary({
      start: range.start,
      end: range.end,
      ...filters,
    }),
    enabled: Boolean(range?.start && range?.end),
    staleTime: 30_000,
  })
}

export function useAdminCalendarAlerts(filters) {
  return useQuery({
    queryKey: ['admin-calendar-alerts', filters],
    queryFn: () => fetchCalendarAlerts(filters),
    staleTime: 30_000,
    refetchInterval: 60_000,
  })
}

export function useAdminCalendarEventDetail(type, id) {
  return useQuery({
    queryKey: ['admin-calendar-event', type, id],
    queryFn: () => fetchCalendarEventDetail(type, id),
    enabled: Boolean(type && id),
  })
}
