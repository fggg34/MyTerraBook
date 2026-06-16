export default function ListingSection({ title, description, children, className = '', bordered = true }) {
  if (!children) return null

  return (
    <section className={`listing-section${bordered ? '' : ' listing-section--plain'}${className ? ` ${className}` : ''}`}>
      <div className="listing-section__inner">
        {title ? (
          <header className="listing-section__head">
            <h2 className="listing-section__label">{title}</h2>
            {description ? <p className="listing-section__desc">{description}</p> : null}
          </header>
        ) : null}
        <div className="listing-section__body">{children}</div>
      </div>
    </section>
  )
}
