export default function TopBar({ linkLabel, linkHref }) {
  return (
    <div className="topbar">
      <div className="wrap">
        <span>
          <strong>Become a Host</strong> and start earning money!
        </span>
        {linkLabel && (
          <a className="bannerlink" href={linkHref || '#'}>
            {linkLabel}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </a>
        )}
      </div>
    </div>
  )
}
