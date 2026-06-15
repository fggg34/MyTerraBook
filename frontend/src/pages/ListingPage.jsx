import { useCallback, useMemo, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { buildCheckoutParams } from '../components/cars/BookingForm'
import ListingPageContent from '../components/listing/ListingPageContent'
import PageHead from '../components/seo/PageHead'
import EmptyState from '../components/ui/EmptyState'
import { useToast } from '../context/ToastContext'
import useListingEffects from '../hooks/useListingEffects'
import useListingPage from '../hooks/useListingPage'
import usePageSeo from '../hooks/usePageSeo'
import { formatDateOnly, formatDateTimeAt, parseTimeParts } from '../utils/format'
import '../styles/listing.css'

export default function ListingPage({ listingType = 'campervan' }) {
  const rootRef = useRef(null)
  const bookingDatesRef = useRef({ pickupDate: null, dropoffDate: null })
  const openCalendarRef = useRef(null)
  const navigate = useNavigate()
  const { toast } = useToast()
  const { listing, related, loadState, queryDefaults, searchQuery, typeConfig, car, reviewTarget, refetchReviews } =
    useListingPage(listingType)

  const listingSource = useMemo(
    () => ({
      name: car?.name || listing?.name,
      description: car?.description || listing?.description,
      short_description: car?.short_description || listing?.short_description,
      meta_title: car?.meta_title,
      meta_description: car?.meta_description,
      og_image: car?.og_image,
      main_image_path: car?.main_image_path || listing?.main_image_path,
      thumbnail: car?.thumbnail || listing?.thumbnail,
      listingType,
    }),
    [car, listing, listingType],
  )
  const seo = usePageSeo(null, { skipPageSeo: true, source: listingSource })

  const [selectedAddonIds, setSelectedAddonIds] = useState([])

  const toggleAddon = useCallback((id) => {
    if (listingType === 'guesthouse' || id == null || id === '') return
    const numId = Number(id)
    if (Number.isNaN(numId)) return
    setSelectedAddonIds((prev) =>
      prev.includes(numId) ? prev.filter((x) => x !== numId) : [...prev, numId],
    )
  }, [listingType])

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
        const checkIn = formatDateOnly(selectedPickup) || queryDefaults.check_in || ''
        const checkOut = formatDateOnly(selectedDropoff) || queryDefaults.check_out || ''
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

      const priceTypeId =
        car.price_types?.[0]?.id || listing?.priceTypes?.[0]?.id || queryDefaults.price_type_id
      const pickupParts = parseTimeParts(car.pickup_time_from) || { hours: 9, minutes: 0 }
      const dropoffParts = parseTimeParts(car.dropoff_time_from) || { hours: 10, minutes: 0 }
      const pickup_at = formatDateTimeAt(selectedPickup, pickupParts.hours, pickupParts.minutes) || queryDefaults.pickup_at || ''
      const dropoff_at = formatDateTimeAt(selectedDropoff, dropoffParts.hours, dropoffParts.minutes) || queryDefaults.dropoff_at || ''
      if (!pickup_at || !dropoff_at) {
        openDatePicker()
        toast('Select pick-up and drop-off dates to continue', 'info')
        return
      }
      if (!priceTypeId) {
        toast('Pricing is not set up for this listing yet. Please contact support.', 'error')
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
        rental_option_ids: selectedAddonIds,
      })
      navigate(`/checkout?${params}`)
    },
    [car, listing, listingType, queryDefaults, navigate, toast, openDatePicker, selectedAddonIds],
  )

  useListingEffects(rootRef, {
    enabled: loadState === 'ok' && !!listing,
  })

  if (loadState === 'loading') {
    return <PageHead {...seo} />
  }

  if (loadState === 'error' || !listing) {
    return (
      <>
        <PageHead {...seo} robots="noindex" />
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
      </>
    )
  }

  return (
    <>
      <PageHead {...seo} />
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
        selectedAddonIds={selectedAddonIds}
        onToggleAddon={listingType === 'guesthouse' ? undefined : toggleAddon}
      />
      </div>
    </>
  )
}
