import { Navigate, useLocation } from 'react-router-dom'

/** Legacy route, redirects to unified /checkout wizard. */
export default function GuestHouseCheckoutPage() {
  const { state } = useLocation()

  if (!state?.house) {
    return <Navigate to="/guest-houses" replace />
  }

  const { house, check_in, check_out, guests_count } = state
  const params = new URLSearchParams({
    type: 'guesthouse',
    slug: house.slug,
    check_in,
    check_out,
    guests_count: String(guests_count || 2),
  })

  return <Navigate to={`/checkout?${params}`} replace />
}
