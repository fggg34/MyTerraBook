export default function TopBar({ text, linkLabel, linkHref }) {
  if (!text) return null

  return (
    <div className="hp-topbar">
      <span>{text}</span>
      {linkLabel && linkHref && (
        <a href={linkHref}>
          {linkLabel}
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </a>
      )}
    </div>
  )
}
