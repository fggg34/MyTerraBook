export default function ListingShowMore({
  expanded,
  hiddenCount,
  onToggle,
  itemLabel = 'item',
  align = 'start',
}) {
  if (hiddenCount <= 0) return null

  const plural = `${itemLabel}s`
  const noun = hiddenCount === 1 ? itemLabel : plural

  return (
    <div className={`listing-more-wrap${align === 'center' ? ' listing-more-wrap--center' : ''}`}>
      <button
        type="button"
        className="listing-more"
        onClick={onToggle}
        aria-expanded={expanded}
      >
        {expanded ? `Show fewer ${plural}` : `Show ${hiddenCount} more ${noun}`}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
          <path d="m6 9 6 6 6-6" />
        </svg>
      </button>
    </div>
  )
}
