export default function ListingPanelKicker({ children }) {
  if (!children) return null

  return (
    <div className="panel-kicker">
      {children}
      <span className="pk-line" />
    </div>
  )
}
