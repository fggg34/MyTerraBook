import { useRef } from 'react'
import { Check, Clock, Plane, Users } from 'lucide-react'
import TripCalendarPicker from './TripCalendarPicker'
import RadioOptionCard from './RadioOptionCard'
import { TIME_OPTIONS, OOH_TIME_VALUE, formatOohTimeOption } from '../../data/requestToBookConfig'
import { buildDateDisabledChecker } from '../../utils/bookingRestrictions'
import { guideToElement, fmtDisplayDate } from '../../utils/requestToBookUtils'

export default function Step1TripTimes({
  config,
  bookingType,
  form,
  updateForm,
  locations,
  dropoffLocations,
  blockedDates,
  restrictions = [],
  nights,
  item,
  locationName,
  locationFeeLabel,
  pickupTimeOptions = TIME_OPTIONS,
  dropoffTimeOptions = TIME_OPTIONS,
  errors = {},
  onNext,
}) {
  const pickupRef = useRef(null)
  const dropoffRef = useRef(null)
  const pickTimeRef = useRef(null)
  const continueRef = useRef(null)

  const pickupLocName = locationName(form.pickup_location_id)
  const dropoffList = dropoffLocations?.length ? dropoffLocations : locations

  const handleRangeComplete = () => {
    if (bookingType === 'guesthouse') return
    guideToElement(pickupRef.current)
  }

  const oohLabel = formatOohTimeOption()
  const pickupDisabled = buildDateDisabledChecker({ blockedDates, restrictions, role: 'pickup' })
  const dropoffDisabled = buildDateDisabledChecker({ blockedDates, restrictions, role: 'dropoff' })

  return (
    <div data-step="1">
      <h2 className="panel-title">{config.step1.title}</h2>
      <p className="panel-sub">{config.step1.subtitle}</p>

      <div className="block">
        <div className="block-head">
          <span className="bnum">1</span>
          <h3>{bookingType === 'guesthouse' ? 'Stay dates' : 'Trip dates'}</h3>
          <span className="bh-sub">{nights} night{nights !== 1 ? 's' : ''}</span>
        </div>
        <TripCalendarPicker
          startDate={form.startDate}
          endDate={form.endDate}
          startLabel={config.step1.dateStartLabel}
          endLabel={config.step1.dateEndLabel}
          blockedDates={blockedDates}
          dateDisabled={(date, role) => (role === 'end' ? dropoffDisabled(date) : pickupDisabled(date))}
          onChange={(start, end) => updateForm({ startDate: start, endDate: end })}
          onRangeComplete={handleRangeComplete}
        />
        {(errors.startDate || errors.endDate) && (
          <p className="hint" style={{ color: 'var(--rtb-red)', marginTop: 10 }}>
            {errors.startDate && errors.endDate
              ? `Select your ${config.step1.dateStartLabel.toLowerCase()} and ${config.step1.dateEndLabel.toLowerCase()} dates`
              : errors.startDate
                ? `Select your ${config.step1.dateStartLabel.toLowerCase()} date`
                : `Select your ${config.step1.dateEndLabel.toLowerCase()} date`}
          </p>
        )}
      </div>

      {config.step1.showPropertyAddress && item && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">2</span>
            <h3>Property</h3>
          </div>
          <p style={{ margin: 0, fontSize: 15, color: 'var(--rtb-slate)' }}>
            {[item.address, item.city, item.country].filter(Boolean).join(' · ')}
          </p>
        </div>
      )}

      {config.step1.showLocations && (
        <div className="block" ref={pickupRef}>
          {(errors.pickup_location_id || errors.dropoff_location_id) && (
            <p className="hint" style={{ color: 'var(--rtb-red)', marginBottom: 12 }}>
              Select your pick-up and drop-off locations to continue
            </p>
          )}
          <div className="block-head">
            <span className="bnum">2</span>
            <h3>Pick-up</h3>
            <span className="bh-sub">{form.startDate ? fmtDisplayDate(form.startDate) : ''}</span>
          </div>
          <div className="opt-list" style={{ marginBottom: 18 }}>
            {locations.map((loc) => (
              <RadioOptionCard
                key={loc.id}
                location={loc}
                selected={String(form.pickup_location_id) === String(loc.id)}
                priceLabel={locationFeeLabel(loc.id, 'pickup')}
                onSelect={(id) => {
                  updateForm({
                    pickup_location_id: String(id),
                    dropoff_location_id: form.sameReturn ? String(id) : form.dropoff_location_id,
                  })
                  guideToElement(pickTimeRef.current, pickTimeRef.current?.querySelector('select'))
                }}
              />
            ))}
          </div>
          <div className="frow">
            <div className="field" ref={pickTimeRef}>
              <label>Pick-up time <span className="req">*</span></label>
              <div className="control ic">
                <Clock className="lead" aria-hidden />
                <select
                  className="sel"
                  value={form.pickupTime}
                  onChange={(e) => {
                    updateForm({ pickupTime: e.target.value })
                    guideToElement(dropoffRef.current)
                  }}
                >
                  {pickupTimeOptions.map((t) => (
                    <option key={t} value={t}>{t}</option>
                  ))}
                  <option value={OOH_TIME_VALUE}>{oohLabel}</option>
                </select>
              </div>
            </div>
            {config.step1.showFlightNumber && (
              <div className="field">
                <label>Flight number <span className="hint">(optional)</span></label>
                <div className="control ic">
                  <Plane className="lead" aria-hidden />
                  <input
                    className="inp"
                    placeholder="e.g. FI452"
                    value={form.flightNumber}
                    onChange={(e) => updateForm({ flightNumber: e.target.value })}
                  />
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      {config.step1.showLocations && (
        <div className="block" ref={dropoffRef}>
          <div className="block-head">
            <span className="bnum">3</span>
            <h3>Drop-off</h3>
            <span className="bh-sub">{form.endDate ? fmtDisplayDate(form.endDate) : ''}</span>
          </div>
          <label
            className={`agree${form.sameReturn ? ' on' : ''}`}
            style={{ marginTop: 0, marginBottom: 18 }}
            onClick={() => {
              updateForm({ sameReturn: !form.sameReturn })
              if (!form.sameReturn) {
                guideToElement(dropoffRef.current?.querySelector('.field'))
              }
            }}
          >
            <span className="cbx"><Check aria-hidden /></span>
            <span>
              Return to the <b>same location</b> I picked up from
              {pickupLocName && pickupLocName !== '—' ? ` — ${pickupLocName}.` : '.'}
            </span>
          </label>
          {!form.sameReturn && (
            <div className="opt-list" style={{ marginBottom: 18 }}>
              {dropoffList.map((loc) => (
                <RadioOptionCard
                  key={loc.id}
                  location={loc}
                  selected={String(form.dropoff_location_id) === String(loc.id)}
                  priceLabel={locationFeeLabel(loc.id, 'dropoff')}
                  onSelect={(id) => updateForm({ dropoff_location_id: String(id) })}
                />
              ))}
            </div>
          )}
          <div className="frow">
            <div className="field">
              <label>Drop-off time <span className="req">*</span></label>
              <div className="control ic">
                <Clock className="lead" aria-hidden />
                <select
                  className="sel"
                  value={form.dropoffTime}
                  onChange={(e) => {
                    updateForm({ dropoffTime: e.target.value })
                    guideToElement(continueRef.current)
                  }}
                >
                  {dropoffTimeOptions.map((t) => (
                    <option key={t} value={t}>{t}</option>
                  ))}
                </select>
              </div>
            </div>
            {config.step1.showTravellers && (
              <div className="field">
                <label>Estimated travellers</label>
                <div className="control ic">
                  <Users className="lead" aria-hidden />
                  <select
                    className="sel"
                    value={form.travellers}
                    onChange={(e) => updateForm({ travellers: Number(e.target.value) })}
                  >
                    {[1, 2, 3].map((n) => (
                      <option key={n} value={n}>{n} traveller{n > 1 ? 's' : ''}</option>
                    ))}
                  </select>
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      {config.step1.showGuests && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">2</span>
            <h3>Guests</h3>
          </div>
          <div className="frow">
            <div className="field">
              <label>Number of guests <span className="req">*</span></label>
              <div className="control ic">
                <Users className="lead" aria-hidden />
                <select
                  className="sel"
                  value={form.guests_count}
                  onChange={(e) => updateForm({ guests_count: Number(e.target.value) })}
                >
                  {Array.from({ length: item?.max_guests || 8 }, (_, i) => i + 1).map((n) => (
                    <option key={n} value={n}>{n} guest{n > 1 ? 's' : ''}</option>
                  ))}
                </select>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="step-nav" ref={continueRef}>
        <button type="button" className="btn-next" onClick={onNext}>
          {config.step1.continueLabel}
        </button>
        <span className="sn-note">
          <Check aria-hidden />
          {config.step1.stepNote}
        </span>
      </div>
    </div>
  )
}
