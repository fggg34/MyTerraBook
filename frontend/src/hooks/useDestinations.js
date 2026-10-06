import { useQuery } from '@tanstack/react-query'
import { api } from '../api'

const DEFAULT_DESTINATIONS = [{ code: 'IS', name: 'Iceland' }]

export default function useDestinations(mainCategory = '') {
  const query = useQuery({
    queryKey: ['destinations', mainCategory],
    queryFn: async () => {
      const { data } = await api.get('/destinations', { params: { main_category: mainCategory || undefined } })
      return data?.data || []
    },
    staleTime: 60000,
  })
  return {
    ...query,
    // Iceland remains available by default while the live catalogue is loading.
    destinations: query.data?.length ? query.data : DEFAULT_DESTINATIONS,
  }
}
