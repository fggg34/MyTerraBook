import { useCallback, useRef } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { buildCheckoutParams } from '../components/cars/BookingForm'
import ListingPageContent from '../components/listing/ListingPageContent'
import EmptyState from '../components/ui/EmptyState'
import { PageLoader } from '../components/ui/LoadingSpinner'
import { useToast } from '../context/ToastContext'
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
  const bookingDatesRef = useRef({ pickupDate: null, dropoffDate: null })
  const openCalendarRef = useRef(null)
  const navigate = useNavigate()
  const { toast } = useToast()
  const { listing, related, loadState, queryDefaults, searchQuery, typeConfig, car, reviewTarget, refetchReviews } =
    useListingPage(listingType)

  const openDatePicker = useCallback(() => {
    openCalendarRef.current?.open?.()
  }, [])

  const handleBook = useCallback(
    ({ pickupDate, dropoffDate } = {}) => {
      if (!car) return

      const selectedPickup = pickupDate ?? bookingDatesRef.current.pickupDate
      const selectedDropoff = dropoffDate ?? bookingDatesRef.current.dropoffDate

      if (listingType === 'guesthouse') {
        const slug = car.slug || car.id
        const checkIn = queryDefaults.check_in || (selectedPickup ? selectedPickup.toISOString().slice(0, 10) : '')
        const checkOut = queryDefaults.check_out || (selectedDropoff ? selectedDropoff.toISOString().slice(0, 10) : '')
        if (!checkIn || !checkOut) {
          openDatePicker()
          toast('Select check-in and check-out dates to continue', 'info')
          return
        }
        const params = new URLSearchParams({
          type: 'guesthouse',
          slug: String(slug),
          check_in: checkIn,
          check_out: checkOut,
          guests_count: String(queryDefaults.guests_count || 2),
        })
        navigate(`/checkout?${params}`)
        return
      }

      const priceTypeId = car.price_types?.[0]?.id || queryDefaults.price_type_id
      const pickup_at = queryDefaults.pickup_at || (selectedPickup ? toDateTimeLocal(selectedPickup) : '')
      const dropoff_at = queryDefaults.dropoff_at || (selectedDropoff ? toDateTimeLocal(selectedDropoff) : '')
      if (!pickup_at || !dropoff_at || !priceTypeId) {
        openDatePicker()
        toast('Select pick-up and drop-off dates to continue', 'info')
        return
      }
      const params = buildCheckoutParams({
        car_id: car.id,
        price_type_id: priceTypeId,
        vehicle_type: listingType,
        pickup_location_id: queryDefaults.pickup_location_id || '',
        dropoff_location_id: queryDefaults.dropoff_location_id || queryDefaults.pickup_location_id || '',
        pickup_at,
        dropoff_at,
      })
      navigate(`/checkout?${params}`)
    },
    [car, listingType, queryDefaults, navigate, toast, openDatePicker],
  )

  useListingEffects(rootRef, {
    enabled: loadState === 'ok' && !!listing,
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
        onRequestBook={handleBook}
        initialPickup={queryDefaults.pickup_at || queryDefaults.check_in}
        initialDropoff={queryDefaults.dropoff_at || queryDefaults.check_out}
        bookingDatesRef={bookingDatesRef}
        openCalendarRef={openCalendarRef}
      />
    </div>
  )
}
