import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Calendar, Check, Globe, MessageCircle } from 'lucide-react'
import BookingModificationSection from '../booking/BookingModificationSection'
import { useToast } from '../../context/ToastContext'
import { getProtectionPresentation } from '../../data/requestToBookConfig'
import {
  buildHostMessageHref,
  downloadConfirmationCalendar,
  hostMemberLabel,
} from '../../utils/bookingConfirmationActions'
import { fmtDisplayDate, toDateOnlyString } from '../../utils/requestToBookUtils'

export default function BookingConfirmation({
  confirmed,
  config,
  item,
  itemImage,
  form,
  nights,
  bookingType,
  locationName,
  selectedPriceType,
  pickupAt,
  dropoffAt,
  host,
  confirmationToken,
}) {
  const { toast } = useToast()
  const [calendarLoading, setCalendarLoading] = useState(false)
  const timeline = config.confirmationTimeline
  const isVehicle = bookingType !== 'guesthouse'
  const payment = confirmed.payment
  const fmtMoney = (amount, currency = 'EUR') => {
    const value = Number(amount || 0)
    try {
      return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value)
    } catch {
      return `${currency} ${value.toFixed(2)}`
    }
  }
  const hostName = host?.name || 'Your host'
  const hostInitial = host?.initial || hostName.charAt(0).toUpperCase() || 'H'
  const hostSubtitle = hostMemberLabel(host)
  const listingPath = config.backLink(item, bookingType)

  const protectionSummary = isVehicle && selectedPriceType
    ? `${selectedPriceType.name} protection · ${selectedPriceType.attribute_value_per_day || getProtectionPresentation(selectedPriceType).deposit}`
    : null

  const hostMessageHref = buildHostMessageHref({
    hostName,
    reference: confirmed.reference,
    itemName: item?.name || 'your stay',
  })

  const handleCalendarDownload = async () => {
    if (!confirmationToken) {
      toast('Calendar download is not available for this booking', 'error')
      return
    }
    setCalendarLoading(true)
    try {
      await downloadConfirmationCalendar(confirmationToken, confirmed.reference)
      toast('Calendar file downloaded', 'success')
    } catch {
      toast('Could not download calendar file', 'error')
    } finally {
      setCalendarLoading(false)
    }
  }

  return (
    <div className="confirm-wrap show">
      <div className="confirm-hero">
        <div className="check-burst">
          <Check aria-hidden />
        </div>
        <div className="ch-kick">Booking confirmed</div>
        <h1>{config.confirmationHero(confirmed.name)}</h1>
        <p>{config.confirmationSubtext}</p>
      </div>

      <div className="confirm-grid">
        <div className="cdetail">
          <div className="cdetail-head">
            <span className="ref">
              Booking reference <b>{confirmed.reference}</b>
            </span>
            <span className="pill">
              <Check aria-hidden />
              Confirmed
            </span>
          </div>
          <div className="cbody">
            <div className="cvan">
              {itemImage && (
                <div className="ct">
                  <img src={itemImage} alt={item?.name} />
                </div>
              )}
              <div>
                <h3>{item?.name}</h3>
                <div className="cmeta">
                  {isVehicle ? (
                    <>Hosted by <b>{hostName}</b> · {config.summaryKick(item)}</>
                  ) : (
                    <>{item?.city || 'Iceland'} · Hosted by <b>{hostName}</b></>
                  )}
                </div>
              </div>
            </div>

            <div className="cgrid2">
              <div className="cf">
                <span className="cfk">{config.step1.dateStartLabel}</span>
                <span className="cfv">
                  {form.startDate ? fmtDisplayDate(form.startDate) : '—'}
                </span>
                <span className="cfs">
                  {bookingType === 'guesthouse'
                    ? item?.check_in_time || 'From 15:00'
                    : `${form.pickupTime} · ${locationName(form.pickup_location_id)}`}
                </span>
              </div>
              <div className="cf">
                <span className="cfk">{config.step1.dateEndLabel}</span>
                <span className="cfv">
                  {form.endDate ? fmtDisplayDate(form.endDate) : '—'}
                </span>
                <span className="cfs">
                  {bookingType === 'guesthouse'
                    ? item?.check_out_time || 'By 11:00'
                    : `${form.dropoffTime} · ${locationName(form.sameReturn ? form.pickup_location_id : form.dropoff_location_id)}`}
                </span>
              </div>
              <div className="cf">
                <span className="cfk">Trip length</span>
                <span className="cfv">
                  {nights} {bookingType === 'guesthouse' ? 'night' : 'day'}{nights !== 1 ? 's' : ''}
                </span>
                {isVehicle && <span className="cfs">Unlimited mileage included</span>}
              </div>
              {payment ? (
                <>
                  <div className="cf cf-total">
                    <span className="cfk">Total booking value</span>
                    <span className="cfv">{fmtMoney(payment.total_price, payment.currency)}</span>
                  </div>
                  <div className="cf">
                    <span className="cfk">Paid online (card)</span>
                    <span className="cfv">{fmtMoney(payment.platform_fee, payment.currency)}</span>
                    <span className="cfs">{payment.status === 'paid' ? 'Paid' : 'Pending'}</span>
                  </div>
                  <div className="cf">
                    <span className="cfk">Cash due on arrival</span>
                    <span className="cfv">{fmtMoney(payment.cash_due_on_arrival, payment.currency)}</span>
                    <span className="cfs">Pay the host directly</span>
                  </div>
                </>
              ) : (
                <div className="cf cf-total">
                  <span className="cfk">Total paid</span>
                  <span className="cfv">{confirmed.total}</span>
                  {protectionSummary && <span className="cfs">{protectionSummary}</span>}
                </div>
              )}
            </div>

            {payment && Number(payment.cash_due_on_arrival) > 0 && (
              <div
                className="cash-notice"
                style={{
                  marginTop: 16,
                  padding: '12px 16px',
                  borderRadius: 12,
                  background: '#fff7ed',
                  border: '1px solid #fed7aa',
                  color: '#9a3412',
                  fontSize: 14,
                  fontWeight: 600,
                }}
              >
                Please bring {fmtMoney(payment.cash_due_on_arrival, payment.currency)} in cash to pay {hostName} directly upon arrival.
              </div>
            )}

            <div className="ctimeline">
              {timeline.map((step, i) => (
                <div
                  key={step.title}
                  className={`ctl${i <= 1 ? ' done' : ''}${i === 2 ? ' now' : ''}`}
                >
                  <span className="tl-dot">{i <= 1 ? <Check aria-hidden /> : i + 1}</span>
                  <div className="tl-tx">
                    <h5>{step.title}</h5>
                    <p>{step.text}</p>
                  </div>
                </div>
              ))}
            </div>

            <BookingModificationSection
              className="cdetail-mod"
              bookableKind={isVehicle ? 'order' : 'guesthouse'}
              reference={confirmed.reference}
              customerEmail={confirmed.customerEmail || form.customer_email}
              orderId={isVehicle ? confirmed.id : null}
              isVehicle={isVehicle}
              pickupAt={isVehicle ? pickupAt : (form.startDate ? `${toDateOnlyString(form.startDate)}T00:00:00` : null)}
              dropoffAt={isVehicle ? dropoffAt : (form.endDate ? `${toDateOnlyString(form.endDate)}T00:00:00` : null)}
              rentalOptionIds={form.rental_option_ids || []}
            />
          </div>
        </div>

        <div className="cside">
          <div className="cactions">
            <button
              type="button"
              className="ca-primary"
              onClick={handleCalendarDownload}
              disabled={calendarLoading || !confirmationToken}
            >
              <Calendar aria-hidden />
              {calendarLoading ? 'Preparing calendar…' : 'Add trip to calendar'}
            </button>
            <Link to={listingPath} className="ca-ghost">
              <Globe aria-hidden />
              Back to listing
            </Link>
          </div>

          <div className="help-card">
            <h5>Questions before you go?</h5>
            <p>
              {host
                ? `Message ${hostName} through our team — they know the local roads, seasons and shortcuts.`
                : 'Our team can help connect you with your host about local roads, seasons and shortcuts.'}
            </p>
            <div className="hc-host">
              <span className="hc-av" aria-hidden>{hostInitial}</span>
              <span className="hc-meta">
                <span className="n">{hostName}</span>
                <span className="r">{hostSubtitle}</span>
              </span>
            </div>
            <a href={hostMessageHref} className="hc-contact">
              <MessageCircle aria-hidden size={16} strokeWidth={2.2} />
              Contact about this booking
            </a>
          </div>
        </div>
      </div>
    </div>
  )
}
