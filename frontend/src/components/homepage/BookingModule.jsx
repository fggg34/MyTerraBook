import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

const TAB_ICONS = {
  campervan: (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M4 14h16M6 14V8h12v6M8 18h2M14 18h2M5 14l1-4h12l1 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  ),
  cars: (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M5 16h14l-1.5-5.5a2 2 0 0 0-1.9-1.5H8.4a2 2 0 0 0-1.9 1.5L5 16zm2 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm10 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
    </svg>
  ),
  guesthouses: (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M4 20V9l8-5 8 5v11M9 20v-6h6v6" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  ),
}

export default function BookingModule({
  tabs = [],
  experienceLabel,
  experiencePlaceholder,
  startDateLabel,
  endDateLabel,
  travelersLabel,
  travelersValue,
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
  const [activeTab, setActiveTab] = useState(tabList[0]?.id || 'cars')

  const handleSearch = () => {
    if (activeTab === 'cars') {
      navigate('/cars')
      return
    }
    navigate('/cars')
  }

  return (
    <div className="hp-booking">
      <div className="hp-booking-tabs" role="tablist">
        {tabList.map((tab) => (
          <button
            key={tab.id}
            type="button"
            role="tab"
            aria-selected={activeTab === tab.id}
            className={`hp-booking-tab ${activeTab === tab.id ? 'active' : ''}`}
            onClick={() => setActiveTab(tab.id)}
          >
            {TAB_ICONS[tab.id] || TAB_ICONS.cars}
            {tab.label}
          </button>
        ))}
      </div>

      <div className="hp-booking-body">
        <div className="hp-field">
          <span className="hp-field-label">{experienceLabel}</span>
          <div className="hp-field-value">{experiencePlaceholder}</div>
        </div>

        <div className="hp-field">
          <span className="hp-field-label">{startDateLabel || 'Select dates'}</span>
          <div className="hp-dates-field">
            <div className="hp-field-value">{startDateLabel}</div>
            <div className="hp-dates-divider" />
            <div className="hp-field-value">{endDateLabel}</div>
          </div>
        </div>

        <div className="hp-field hp-travelers-field">
          <div>
            <span className="hp-field-label">{travelersLabel}</span>
            <div className="hp-field-value">{travelersValue}</div>
          </div>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
        </div>

        <button type="button" className="hp-search-btn" onClick={handleSearch}>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="currentColor" strokeWidth="2" />
            <path d="M20 20l-3.5-3.5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
          {searchLabel}
        </button>
      </div>

      {(footerHint || footerLinkLabel) && (
        <div className="hp-booking-footer">
          {footerHint}{' '}
          {footerLinkLabel && (
            <a href={footerLinkHref || '#'}>{footerLinkLabel}</a>
          )}
        </div>
      )}
    </div>
  )
}
