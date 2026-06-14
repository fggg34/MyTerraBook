function TrustIcon({ type, image }) {
  if (image) {
    return <img src={image} alt="" className="trust-ic-img" aria-hidden="true" />
  }
  const icons = {
    star: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="m12 3 2.7 5.5 6 .9-4.3 4.2 1 6-5.4-2.8L6.6 19.6l1-6L3.3 9.4l6-.9L12 3Z" />
      </svg>
    ),
    check: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
        <path d="m8.5 12 2.5 2.5L16 9" />
      </svg>
    ),
    shield: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z" />
      </svg>
    ),
    phone: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M4 5.5C4 4.7 4.7 4 5.5 4h2.8c.6 0 1.2.4 1.4 1l1.2 3.2c.2.5 0 1.1-.4 1.5L8.8 11.2a13 13 0 0 0 5.6 5.6l1.5-1.7c.4-.4 1-.6 1.5-.4l3.2 1.2c.6.2 1 .8 1 1.4v2.8c0 .8-.7 1.5-1.5 1.5C10.3 21.6 4 15.3 4 5.5Z" />
      </svg>
    ),
  }
  return icons[type] || icons.check
}

export default function TrustStrip({ items = [] }) {
  if (!items.length) return null

  return (
    <section className="trust">
      <div className="wrap">
        {items.map((item, index) => (
          <div className="trust-item" key={`${item.title}-${index}`}>
            <span className="trust-ic">
              <TrustIcon type={item.icon} image={item.iconImage} />
            </span>
            <div className="trust-text">
              <div className="tt-top">
                {item.title}
                {item.icon === 'star' && item.stars ? (
                  <span className="trust-stars" aria-hidden="true">
                    {Array.from({ length: item.stars }).map((_, i) => (
                      <svg key={i} viewBox="0 0 24 24" fill="currentColor">
                        <path d="m12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z" />
                      </svg>
                    ))}
                  </span>
                ) : null}
              </div>
              <div className="tt-sub">{item.subtitle}</div>
            </div>
          </div>
        ))}
      </div>
    </section>
  )
}
