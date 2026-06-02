import { useEffect, useRef, useState } from 'react'
import ImageSlot from './ImageSlot'

function FeatureRow({ feature }) {
  const [open, setOpen] = useState(false)

  return (
    <div className="hp-wf">
      <div className="hp-wf-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="1.8" />
        </svg>
      </div>
      <div className="hp-wf-body">
        <h3>{feature.title}</h3>
        <p>{feature.description}</p>
        {feature.expandedText && (
          <>
            <div className={`hp-wf-extra ${open ? 'open' : ''}`}>{feature.expandedText}</div>
            <button type="button" className="hp-wf-toggle" onClick={() => setOpen((v) => !v)}>
              {open ? 'Show less' : 'Learn more'}
            </button>
          </>
        )}
      </div>
    </div>
  )
}

export default function WhyMyTerraSection({
  heading,
  subheading,
  photo,
  badge = {},
  featuresLeft = [],
  featuresRight = [],
}) {
  const splitRef = useRef(null)

  useEffect(() => {
    const node = splitRef.current
    if (!node) return undefined

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          node.classList.add('revealed')
          observer.disconnect()
        }
      },
      { threshold: 0.22 },
    )

    observer.observe(node)
    return () => observer.disconnect()
  }, [])

  return (
    <section className="hp-why">
      <div className="homepage-wrap">
        <div className="hp-why-header">
          {heading && <h2>{heading}</h2>}
          {subheading && <p>{subheading}</p>}
        </div>

        <div className="hp-why-split" ref={splitRef}>
          <div className="hp-why-left">
            {featuresLeft.map((feature) => (
              <FeatureRow key={feature.title} feature={feature} />
            ))}
          </div>

          <div className="hp-why-photo-wrap">
            {photo ? <img src={photo} alt="" /> : <ImageSlot label="Photo" />}
            {(badge.rating || badge.reviewCount) && (
              <div className="hp-why-badge">
                {badge.rating && <strong>{badge.rating}</strong>}
                {badge.reviewCount && <span>from {badge.reviewCount}</span>}
              </div>
            )}
          </div>

          <div className="hp-why-right">
            {featuresRight.map((feature) => (
              <FeatureRow key={feature.title} feature={feature} />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
