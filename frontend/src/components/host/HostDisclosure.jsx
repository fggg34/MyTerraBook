/**
 * Collapsible "advanced / optional" section for the host editors. Uses a native
 * <details> element so it stays accessible and needs no state. Keeps the common
 * path uncluttered while leaving power-user options one click away.
 */
export default function HostDisclosure({ title, hint, count = 0, defaultOpen = false, children }) {
  return (
    <details className="host-disclosure" open={defaultOpen}>
      <summary className="host-disclosure__summary">
        <span className="host-disclosure__title">
          {title}
          {count > 0 && <span className="host-disclosure__count">{count}</span>}
        </span>
        <svg
          className="host-disclosure__chevron"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2.2"
          strokeLinecap="round"
          strokeLinejoin="round"
          aria-hidden
        >
          <path d="m6 9 6 6 6-6" />
        </svg>
      </summary>
      <div className="host-disclosure__body">
        {hint && <p className="host-disclosure__hint">{hint}</p>}
        {children}
      </div>
    </details>
  )
}
