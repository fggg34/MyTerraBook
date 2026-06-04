import { useCallback, useRef } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { buildCheckoutParams } from '../components/cars/BookingForm'
import ListingPageContent from '../components/listing/ListingPageContent'
import EmptyState from '../components/ui/EmptyState'
import { PageLoader } from '../components/ui/LoadingSpinner'
import useListingEffects from '../hooks/useListingEffects'
import useListingPage from '../hooks/useListingPage'
import '../styles/listing.css'

function toDateTimeLocal(date) {
  if (!date) return ''
  const d = new Date(date)
  d.setHours(10, 0, 0, 0)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

export default function ListingPage({ listingType = 'campervan' }) {
  const rootRef = useRef(null)
  const navigate = useNavigate()
  const { listing, related, loadState, queryDefaults, searchQuery, typeConfig, car, reviewTarget, refetchReviews } =
    useListingPage(listingType)

  const handleBook = useCallback(
    ({ pickupDate, dropoffDate } = {}) => {
      if (!car?.id) return
      const priceTypeId = car.price_types?.[0]?.id || queryDefaults.price_type_id
      const pickup_at = queryDefaults.pickup_at || (pickupDate ? toDateTimeLocal(pickupDate) : '')
      const dropoff_at = queryDefaults.dropoff_at || (dropoffDate ? toDateTimeLocal(dropoffDate) : '')
      if (!pickup_at || !dropoff_at || !priceTypeId) {
        document.getElementById('dateField')?.click()
        return
      }
      const params = buildCheckoutParams({
        car_id: car.id,
        price_type_id: priceTypeId,
        pickup_location_id: queryDefaults.pickup_location_id || '',
        dropoff_location_id: queryDefaults.dropoff_location_id || queryDefaults.pickup_location_id || '',
        pickup_at,
        dropoff_at,
      })
      navigate(`/checkout?${params}`)
    },
    [car, queryDefaults, navigate],
  )

  useListingEffects(rootRef, {
    priceFrom: Number(listing?.priceFrom) || 94,
    onBook: handleBook,
  })

  if (loadState === 'loading') {
    return <PageLoader message="Loading listing…" />
  }

  if (loadState === 'error' || !listing) {
    return (
      <div className="wrap" style={{ padding: '4rem 0' }}>
        <EmptyState
          title="Listing not found"
          description="This listing may no longer be available."
          action={
            <Link to={typeConfig.archiveRoute} className="btn-primary">
              {typeConfig.archiveLabel}
            </Link>
          }
        />
      </div>
    )
  }

  return (
    <div className="listing-page" ref={rootRef}>
      <ListingPageContent
        listing={listing}
        related={related}
        searchQuery={searchQuery}
        typeConfig={typeConfig}
        reviewTarget={reviewTarget}
        onReviewsChange={refetchReviews}
      />
    </div>
  )
}
