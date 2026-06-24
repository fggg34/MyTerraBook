import { useEffect, useMemo, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { api, resolveStorageUrl } from '../api'
import BookingConfirmation from '../components/request-to-book/BookingConfirmation'
import RequestToBookSubbar from '../components/request-to-book/RequestToBookSubbar'
import PageHead from '../components/seo/PageHead'
import { useShopConfig } from '../context/ShopConfigContext'
import { getRequestToBookConfig } from '../data/requestToBookConfig'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/request-to-book.css'

function parseDateOnly(value) {
  if (!value) return null
  const date = new Date(`${value.slice(0, 10)}T12:00:00`)
  return Number.isNaN(date.getTime()) ? null : date
}

function buildPropsFromPayload(data, prepayPercent) {
  const bookingType = data.booking_type || 'car'
  const config = getRequestToBookConfig(bookingType, prepayPercent)
  const isVehicle = data.bookable_kind === 'order'
  const firstName = (data.customer_name || '').split(' ')[0] || 'there'

  const startDate = isVehicle
    ? parseDateOnly(data.pickup_at)
    : parseDateOnly(data.check_in)
  const endDate = isVehicle
    ? parseDateOnly(data.dropoff_at)
    : parseDateOnly(data.check_out)

  const itemImage = isVehicle
    ? resolveStorageUrl(data.item?.main_image_path)
    : resolveStorageUrl(data.item?.thumbnail)

  return {
    confirmed: {
      type: isVehicle ? 'vehicle' : 'guesthouse',
      id: data.order_id,
      reference: data.reference,
      total: data.total_formatted || data.total,
      currency: data.currency,
      name: firstName,
      customerEmail: data.customer_email,
    },
    config,
    item: data.item,
    itemImage,
    form: {
      startDate,
      endDate,
      pickup_location_id: data.pickup_location_id,
      dropoff_location_id: data.dropoff_location_id,
      sameReturn: data.same_return ?? true,
      pickupTime: data.pickup_time || '10:00',
      dropoffTime: data.dropoff_time || '10:00',
      customer_email: data.customer_email,
      rental_option_ids: data.rental_option_ids || [],
      guests_count: data.guests_count,
    },
    nights: data.nights,
    bookingType,
    locationName: (id) => {
      if (String(id) === String(data.pickup_location_id)) return data.pickup_location_name || '—'
      if (String(id) === String(data.dropoff_location_id)) return data.dropoff_location_name || '—'
      return '—'
    },
    selectedPriceType: data.price_type,
    pickupAt: data.pickup_at,
    dropoffAt: data.dropoff_at,
  }
}

export default function BookingConfirmationPage() {
  const { token } = useParams()
  const { prepayPercent } = useShopConfig()
  const seo = usePageSeo('checkout', { robots: 'noindex', title: 'Booking confirmed' })
  const [payload, setPayload] = useState(null)
  const [error, setError] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!token) {
      setError('Invalid confirmation link.')
      setLoading(false)
      return
    }

    setLoading(true)
    api.get(`/booking-confirmation/${encodeURIComponent(token)}`)
      .then((res) => {
        setPayload(res.data.data)
        setError(null)
      })
      .catch((err) => {
        setPayload(null)
        setError(err.response?.data?.message || 'Could not load booking confirmation.')
      })
      .finally(() => setLoading(false))
  }, [token])

  const viewProps = useMemo(
    () => (payload ? buildPropsFromPayload(payload, prepayPercent) : null),
    [payload, prepayPercent],
  )

  const backHref = viewProps
    ? viewProps.config.backLink(viewProps.item, viewProps.bookingType)
    : '/'

  if (loading) {
    return (
      <>
        <PageHead {...seo} />
        <div className="rtb-page">
          <div className="rtb-page-inner">
            <p className="rtb-empty-lead">Loading your confirmation…</p>
          </div>
        </div>
      </>
    )
  }

  if (error || !viewProps) {
    return (
      <>
        <PageHead {...seo} />
        <div className="rtb-page">
          <div className="rtb-page-inner">
            <div className="rtb-empty-card">
              <h1>Confirmation not found</h1>
              <p className="rtb-empty-lead">{error || 'This link may have expired or is invalid.'}</p>
              <Link to="/" className="client-btn primary">Back to home</Link>
            </div>
          </div>
        </div>
      </>
    )
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="rtb-page">
        <RequestToBookSubbar backHref={backHref} />
        <div className="rtb-page-inner">
          <div className="rtb-wrap">
            <BookingConfirmation {...viewProps} />
          </div>
        </div>
      </div>
    </>
  )
}
