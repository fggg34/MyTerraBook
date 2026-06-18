import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import DateRangePicker, { parseDateOnly } from '../ui/DateRangePicker'
import FieldSelect from '../ui/FieldSelect'
import PredictiveSearchField from '../ui/PredictiveSearchField'
import { useBookingRules } from '../../hooks/useBookingRules'
import useMediaQuery from '../../hooks/useMediaQuery'
import useLocationOptions, { toFieldSelectOptions, useAutoSelectLocation } from '../../hooks/useLocationOptions'
import { ensureValidDropoff } from '../../utils/bookingRules'
import { formatDateTimeLocal, parseDateTimeLocal } from '../../utils/format'

const TAB_ROUTES = {
  campervan: '/campervans',
  cars: '/cars',
  guesthouses: '/guesthouses',
}

const TAB_ICONS = {
  campervan: (
    <svg viewBox="0 0 28 18" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M1 13V6.5C1 5.7 1.7 5 2.5 5H16l5 4h4.5C26.3 9 27 9.7 27 10.5V13" />
      <path d="M1 13h3M11 13h6M24 13h3" />
      <circle cx="7.5" cy="13.5" r="2.5" fill="white" />
      <circle cx="20.5" cy="13.5" r="2.5" fill="white" />
      <path d="M5 5V2.5h7V5" />
    </svg>
  ),
  cars: (
    <svg viewBox="0 0 28 18" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M2 12V9.2c0-.5.3-.9.8-1L6 7l2.8-3.4c.4-.5 1-.8 1.6-.8h7.5c.7 0 1.3.3 1.7.9L22 7l3.2.9c.5.1.8.5.8 1V12" />
      <path d="M2 12h3M11 12h6M23 12h3M6 7h16" />
      <circle cx="8" cy="12.5" r="2.4" fill="white" />
      <circle cx="20" cy="12.5" r="2.4" fill="white" />
    </svg>
  ),
  guesthouses: (
    <svg viewBox="0 0 24 22" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round">
      <path d="M4 20V9l8-6 8 6v11" />
      <path d="M2 20h20" />
      <rect x="9.5" y="12" width="5" height="8" />
      <path d="M7 9.5v3M17 9.5v3" />
    </svg>
  ),
}

const PIN_ICON = (
  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
    <path d="M12 21s7-6.3 7-11a7 7 0 1 0-14 0c0 4.7 7 11 7 11Z" />
    <circle cx="12" cy="10" r="2.5" />
  </svg>
)

const PERSON_ICON = (
  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
    <circle cx="12" cy="8" r="4" />
    <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
  </svg>
)

const PEOPLE_OPTIONS = Array.from({ length: 8 }, (_, i) => i + 1)

function toDateInputValue(iso) {
  if (!iso) return ''
  const d = iso instanceof Date ? iso : new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

function defaultCheckIn() {
  const d = new Date()
  d.setDate(d.getDate() + 7)
  return toDateInputValue(d)
}

function defaultCheckOut(checkIn) {
  const base = checkIn ? new Date(checkIn) : new Date()
  if (Number.isNaN(base.getTime())) {
    const d = new Date()
    d.setDate(d.getDate() + 9)
    return toDateInputValue(d)
  }
  const d = new Date(base)
  d.setDate(d.getDate() + 2)
  return toDateInputValue(d)
}

function dateTimeWithTime(date, time) {
  if (!date) return ''
  const [hours, minutes] = time.split(':').map(Number)
  const d = new Date(date)
  d.setHours(hours, minutes, 0, 0)
  return formatDateTimeLocal(d)
}

export default function BookingModule({
  tabs = [],
  searchLabel,
  footerHint,
  footerLinkLabel,
  footerLinkHref,
}) {
  const navigate = useNavigate()
  const tabList = tabs.length
    ? tabs
    : [
        { id: 'campervan', label: 'Campervan' },
        { id: 'cars', label: 'Cars' },
        { id: 'guesthouses', label: 'Guesthouses' },
      ]
  const [activeTab, setActiveTab] = useState(tabList[0]?.id || 'campervan')
  const [mobileDetailsOpen, setMobileDetailsOpen] = useState(false)
  const isMobileCompact = useMediaQuery('(max-width: 768px)')
  const isGuesthouse = activeTab === 'guesthouses'
  const vehicleMainCategory =
    activeTab === 'campervan' ? 'campervan' : activeTab === 'cars' ? 'car' : ''

  const [vehicleForm, setVehicleForm] = useState({
    pickup_location_id: '',
    dropoff_location_id: '',
    pickup_at: '',
    dropoff_at: '',
  })
  const [guestForm, setGuestForm] = useState({
    city: '',
    check_in: defaultCheckIn(),
    check_out: defaultCheckOut(defaultCheckIn()),
    guests: '2',
  })
  const [guestCityLabel, setGuestCityLabel] = useState('')

  const { options: pickupOptions, isEmpty: pickupEmpty, loading: pickupLoading } = useLocationOptions({
    role: 'pickup',
    mainCategory: vehicleMainCategory,
    enabled: !isGuesthouse,
    limit: 50,
  })

  const { options: dropoffOptions } = useLocationOptions({
    role: 'dropoff',
    pickupLocationId: vehicleForm.pickup_location_id,
    mainCategory: vehicleMainCategory,
    enabled: !isGuesthouse && !!vehicleForm.pickup_location_id,
    limit: 50,
  })

  useAutoSelectLocation({
    options: pickupOptions,
    value: vehicleForm.pickup_location_id,
    onSelect: (id) => {
      setVehicleForm((prev) => ({
        ...prev,
        pickup_location_id: id,
        dropoff_location_id: prev.dropoff_location_id || id,
      }))
    },
  })

  useAutoSelectLocation({
    options: dropoffOptions,
    value: vehicleForm.dropoff_location_id,
    pickupValueForDropoff: vehicleForm.pickup_location_id,
    onSelect: (id) => {
      setVehicleForm((prev) => ({ ...prev, dropoff_location_id: id }))
    },
  })

  const pickupFieldOptions = useMemo(() => toFieldSelectOptions(pickupOptions), [pickupOptions])
  const dropoffFieldOptions = useMemo(() => toFieldSelectOptions(dropoffOptions), [dropoffOptions])

  const pickupDate = useMemo(() => parseDateTimeLocal(vehicleForm.pickup_at), [vehicleForm.pickup_at])
  const dropoffDate = useMemo(() => parseDateTimeLocal(vehicleForm.dropoff_at), [vehicleForm.dropoff_at])
  const rules = useBookingRules(pickupDate, dropoffDate)
  const minRentalDays = rules.min_rental_days || 1
  const showMobileDetails = !isMobileCompact || mobileDetailsOpen

  useEffect(() => {
    setMobileDetailsOpen(false)
  }, [activeTab])

  const handleVehicleDates = ({ start, end }) => {
    const pickup_at = dateTimeWithTime(start, '11:00')
    let dropoff_at = dateTimeWithTime(end, '10:00')
    if (pickup_at && dropoff_at) {
      dropoff_at = ensureValidDropoff(parseDateTimeLocal(pickup_at), dropoff_at, minRentalDays)
    }
    setVehicleForm((prev) => ({ ...prev, pickup_at, dropoff_at }))
  }

  const handleGuestDates = ({ start, end }) => {
    setGuestForm((prev) => ({
      ...prev,
      check_in: start ? toDateInputValue(start) : '',
      check_out: end ? toDateInputValue(end) : '',
    }))
  }

  const handleSearch = () => {
    const route = TAB_ROUTES[activeTab] || TAB_ROUTES.campervan
    const params = new URLSearchParams()

    if (isGuesthouse) {
      if (guestForm.city.trim()) params.set('city', guestForm.city.trim())
      if (guestForm.check_in) params.set('check_in', guestForm.check_in)
      if (guestForm.check_out) params.set('check_out', guestForm.check_out)
      if (guestForm.guests) params.set('guests', guestForm.guests)
    } else {
      if (vehicleForm.pickup_location_id) params.set('pickup_location_id', vehicleForm.pickup_location_id)
      if (vehicleForm.dropoff_location_id) params.set('dropoff_location_id', vehicleForm.dropoff_location_id)
      if (vehicleForm.pickup_at) params.set('pickup_at', vehicleForm.pickup_at)
      if (vehicleForm.dropoff_at) params.set('dropoff_at', vehicleForm.dropoff_at)
    }

    const qs = params.toString()
    navigate(qs ? `${route}?${qs}` : route)
  }

  const renderVehicleFields = () => (
    <>
      {!pickupLoading && pickupEmpty && (
        <p className="location-empty-hint" role="status">
          Pickup locations are being configured. Assign locations to vehicles in admin to enable search.
        </p>
      )}
      <div className="field field--primary">
        <span className="flabel">Pick-up location</span>
        <FieldSelect
          value={vehicleForm.pickup_location_id}
          onChange={(value) => {
            const sameAsPickup = vehicleForm.dropoff_location_id === vehicleForm.pickup_location_id
            setVehicleForm((prev) => ({
              ...prev,
              pickup_location_id: value,
              dropoff_location_id: sameAsPickup ? value : prev.dropoff_location_id,
            }))
            if (isMobileCompact) setMobileDetailsOpen(true)
          }}
          options={pickupFieldOptions}
          placeholder="Select location"
          icon={PIN_ICON}
          ariaLabel="Pick-up location"
          disabled={pickupEmpty}
        />
      </div>

      <div className="field field--detail">
        <span className="flabel">Drop-off location</span>
        <FieldSelect
          value={vehicleForm.dropoff_location_id}
          onChange={(value) => setVehicleForm((prev) => ({ ...prev, dropoff_location_id: value }))}
          options={dropoffFieldOptions}
          placeholder="Select location"
          icon={PIN_ICON}
          ariaLabel="Drop-off location"
          disabled={!vehicleForm.pickup_location_id || pickupEmpty}
        />
      </div>

      <div className="field dates field--detail">
        <span className="flabel">Pick-up → Drop-off</span>
        <DateRangePicker
          variant="embedded compact"
          fixedPopper
          startLabel="Pick-up"
          endLabel="Drop-off"
          startDate={parseDateOnly(vehicleForm.pickup_at)}
          endDate={parseDateOnly(vehicleForm.dropoff_at)}
          minNights={minRentalDays}
          maxNights={rules.max_rental_days}
          onChange={handleVehicleDates}
        />
      </div>
    </>
  )

  const renderGuesthouseFields = () => (
    <>
      <div className="field field--primary">
        <span className="flabel">City or area</span>
        <PredictiveSearchField
          scope="guesthouse"
          allowFreeText
          value={guestForm.city}
          displayValue={guestCityLabel}
          placeholder="e.g. Reykjavík, Akureyri"
          icon={PIN_ICON}
          ariaLabel="City or area"
          onChange={({ value, label }) => {
            setGuestCityLabel(label)
            setGuestForm((prev) => ({ ...prev, city: value }))
            if (isMobileCompact && value.trim()) setMobileDetailsOpen(true)
          }}
        />
      </div>

      <div className="field dates field--detail">
        <span className="flabel">Check-in → Check-out</span>
        <DateRangePicker
          variant="embedded compact"
          fixedPopper
          startLabel="Check-in"
          endLabel="Check-out"
          startDate={parseDateOnly(guestForm.check_in)}
          endDate={parseDateOnly(guestForm.check_out)}
          minNights={1}
          onChange={handleGuestDates}
        />
      </div>

      <div className="field travelers field--detail">
        <span className="flabel">Guests</span>
        <FieldSelect
          value={guestForm.guests}
          onChange={(value) => setGuestForm((prev) => ({ ...prev, guests: value }))}
          options={PEOPLE_OPTIONS.map((n) => ({
            value: String(n),
            label: `${n} ${n === 1 ? 'guest' : 'guests'}`,
          }))}
          icon={PERSON_ICON}
          ariaLabel="Number of guests"
        />
      </div>
    </>
  )

  return (
    <div className="booking">
      <div className="booking-card">
        <div className="tabs">
          {tabList.map((tab) => (
            <button
              key={tab.id}
              type="button"
              className={`tab ${activeTab === tab.id ? 'active' : ''}`}
              onClick={() => setActiveTab(tab.id)}
            >
              {TAB_ICONS[tab.id] || TAB_ICONS.cars}
              {tab.label}
            </button>
          ))}
        </div>

        <div className="booking-body">
          <div
            className={`search-row ${isGuesthouse ? 'mode-guesthouse' : 'mode-vehicle'}${showMobileDetails ? ' search-row--expanded' : ' search-row--compact'}`}
          >
            {isGuesthouse ? renderGuesthouseFields() : renderVehicleFields()}
            <button className="search-btn search-btn--detail" type="button" onClick={handleSearch}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.2-3.2" />
              </svg>
              {searchLabel || 'Search Now'}
            </button>
          </div>
        </div>

        {(footerHint || footerLinkLabel) && (
          <div className="booking-foot">
            {footerHint}{' '}
            {footerLinkLabel && <a href={footerLinkHref || '#'}>{footerLinkLabel}</a>}
          </div>
        )}
      </div>
    </div>
  )
}
