import { Link } from 'react-router-dom'
import { Calendar, Check, Globe, Mail } from 'lucide-react'
import { getProtectionPresentation } from '../../data/requestToBookConfig'
import { fmtDisplayDate } from '../../utils/requestToBookUtils'

export default function BookingConfirmation({
  confirmed,
  config,
  item,
  itemImage,
  form,
  nights,
  quote,
  bookingType,
  locationName,
  selectedPriceType,
}) {
  const timeline = config.confirmationTimeline
  const isVehicle = bookingType !== 'guesthouse'
  const hostInitial = item?.name?.charAt(0)?.toUpperCase() || 'H'

  const protectionSummary = isVehicle && selectedPriceType
    ? `${selectedPriceType.name} protection · ${selectedPriceType.attribute_value_per_day || getProtectionPresentation(selectedPriceType).deposit}`
    : null

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
                    <>Hosted by <b>your host</b> · {config.summaryKick(item)}</>
                  ) : (
                    <>{item?.city || 'Iceland'} · {config.summaryKick(item)}</>
                  )}
                </div>
              </div>
            </div>
            <div className="cgrid2">
              <div className="cf">
                <span className="cfk">{config.step1.dateStartLabel}</span>
                <span className="cfv">
                  {form.startDate ? `${fmtDisplayDate(form.startDate)} · ${form.startDate.getFullYear()}` : ','}
                </span>
                <span className="cfs">
                  {bookingType === 'guesthouse'
                    ? item?.check_in_time
                    : `${form.pickupTime} · ${locationName(form.pickup_location_id)}`}
                </span>
              </div>
              <div className="cf">
                <span className="cfk">{config.step1.dateEndLabel}</span>
                <span className="cfv">
                  {form.endDate ? `${fmtDisplayDate(form.endDate)} · ${form.endDate.getFullYear()}` : ','}
                </span>
                <span className="cfs">
                  {bookingType === 'guesthouse'
                    ? item?.check_out_time
                    : `${form.dropoffTime} · ${locationName(form.sameReturn ? form.pickup_location_id : form.dropoff_location_id)}`}
                </span>
              </div>
              <div className="cf">
                <span className="cfk">Trip length</span>
                <span className="cfv">{nights} night{nights !== 1 ? 's' : ''}</span>
                {isVehicle && <span className="cfs">Unlimited mileage included</span>}
              </div>
              <div className="cf">
                <span className="cfk">Total</span>
                <span className="cfv">{confirmed.total}</span>
                {protectionSummary && <span className="cfs">{protectionSummary}</span>}
              </div>
            </div>

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
          </div>
        </div>

        <div className="cside">
          <div className="cactions">
            <button type="button" className="ca-primary">
              <Calendar aria-hidden />
              Add trip to calendar
            </button>
            <button type="button" className="ca-ghost">
              <Mail aria-hidden />
              Email me the details
            </button>
            <Link to={config.backLink(item, bookingType)} className="ca-ghost">
              <Globe aria-hidden />
              Back to listing
            </Link>
          </div>
          <div className="help-card">
            <h5>Questions before you go?</h5>
            <p>Message your host directly, they know the local roads, seasons and shortcuts.</p>
            <div className="hc-host">
              <span className="hc-av">{hostInitial}</span>
              <span className="hc-meta">
                <span className="n">Your host</span>
                <span className="r">★ Replies in ~1h</span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
