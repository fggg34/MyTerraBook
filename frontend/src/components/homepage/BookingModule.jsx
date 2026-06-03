import { useState } from 'react'
import { useNavigate } from 'react-router-dom'

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
  const [activeTab, setActiveTab] = useState(tabList[0]?.id || 'campervan')

  const handleSearch = () => {
    const target = activeTab === 'cars' ? '/cars' : '/campervans'
    navigate(target)
  }

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
          <div className="search-row">
            <div className="field">
              <span className="flabel">{experienceLabel}</span>
              <span className="fval">{experiencePlaceholder}</span>
            </div>
            <div className="field dates">
              <span className="flabel">Select dates</span>
              <div className="dates-inner">
                <span className="seg">
                  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="3" y="4.5" width="18" height="16" rx="2.5" />
                    <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                  </svg>
                  {startDateLabel}
                </span>
                <span className="divider" />
                <span className="seg">
                  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <rect x="3" y="4.5" width="18" height="16" rx="2.5" />
                    <path d="M3 9h18M8 2.5v4M16 2.5v4" />
                  </svg>
                  {endDateLabel}
                </span>
              </div>
            </div>
            <div className="field travelers">
              <span className="flabel">{travelersLabel}</span>
              <span className="fval filled">
                <span style={{ display: 'flex', alignItems: 'center', gap: '9px' }}>
                  <svg className="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" />
                  </svg>
                  {travelersValue}
                </span>
                <svg className="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </span>
            </div>
            <button className="search-btn" type="button" onClick={handleSearch}>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.2-3.2" />
              </svg>
              {searchLabel}
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
