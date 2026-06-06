import { useEffect, useState } from 'react'
import { api } from '../api'

export default function useSearchPromotions(context) {
  const [promotions, setPromotions] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    api
      .get('/search-promotions', { params: { context } })
      .then((res) => setPromotions(res.data?.data ?? []))
      .catch(() => setPromotions([]))
      .finally(() => setLoading(false))
  }, [context])

  return { promotions, loading }
}
