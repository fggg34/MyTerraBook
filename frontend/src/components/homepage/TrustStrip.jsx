function TrustIcon({ type }) {
  const icons = {
    star: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 2l2.9 6.9L22 10l-5.5 4.7L18.2 22 12 18.5 5.8 22l1.7-7.3L2 10l7.1-1.1L12 2z" />
      </svg>
    ),
    check: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M5 12l4 4L19 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    shield: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6l8-3z" stroke="currentColor" strokeWidth="1.8" />
      </svg>
    ),
    phone: (
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M6.5 4h3l1.5 5-2 1.5a11 11 0 0 0 5 5L17.5 13 22.5 14.5V18a2 2 0 0 1-2 2C10.5 20 4 13.5 4 4.5A2 2 0 0 1 6.5 4z" stroke="currentColor" strokeWidth="1.8" />
      </svg>
    ),
  }

  return icons[type] || icons.check
}

export default function TrustStrip({ items = [] }) {
  if (!items.length) return null

  return (
    <section className="hp-trust">
      <div className="homepage-wrap hp-trust-grid">
        {items.map((item, index) => (
          <div className="hp-trust-item" key={`${item.title}-${index}`}>
            <div className="hp-trust-icon">
              <TrustIcon type={item.icon} />
            </div>
            <div>
              <h4>{item.title}</h4>
              {item.icon === 'star' && item.stars ? (
                <div className="hp-stars" aria-hidden="true">
                  {Array.from({ length: item.stars }).map((_, i) => (
                    <svg key={i} viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 2l2.9 6.9L22 10l-5.5 4.7L18.2 22 12 18.5 5.8 22l1.7-7.3L2 10l7.1-1.1L12 2z" />
                    </svg>
                  ))}
                </div>
              ) : null}
              <p>{item.subtitle}</p>
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}
