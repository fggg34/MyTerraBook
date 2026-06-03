import { useRef, useState } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

const FEATURE_ICONS = {
  campervan: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 17V9a1 1 0 0 1 1-1h10v9M3 17h11M14 11h3.5L21 14v3h-2M14 17h-2" />
      <circle cx="7" cy="17" r="2" />
      <circle cx="17" cy="17" r="2" />
    </svg>
  ),
  car: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 17a2 2 0 1 0 4 0 2 2 0 0 0-4 0Zm10 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z" />
      <path d="M5 17H3v-4l2-5h11l3 5v4h-2M9 17h6M3 11h17" />
    </svg>
  ),
  house: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M3 11 12 4l9 7M5 10v10h14V10M9 20v-6h6v6" />
    </svg>
  ),
  host: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 3v18M7 8h7a2.5 2.5 0 0 1 0 5H7m0 0h8" />
    </svg>
  ),
  shield: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z" />
      <path d="m8.5 11.5 2.5 2.5L16 8.5" />
    </svg>
  ),
  phone: (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
      <path d="M4 5.5C4 4.7 4.7 4 5.5 4h2.8c.6 0 1.2.4 1.4 1l1.2 3.2c.2.5 0 1.1-.4 1.5L8.8 11.2a13 13 0 0 0 5.6 5.6l1.5-1.7c.4-.4 1-.6 1.5-.4l3.2 1.2c.6.2 1 .8 1 1.4v2.8c0 .8-.7 1.5-1.5 1.5C10.3 21.6 4 15.3 4 5.5Z" />
    </svg>
  ),
}

function FeatureRow({ feature }) {
  const [open, setOpen] = useState(false)

  return (
    <div className={`wf ${open ? 'open' : ''}`}>
      <span className="wf-ic">{FEATURE_ICONS[feature.icon] || FEATURE_ICONS.shield}</span>
      <div className="wf-tx">
        <h3>{feature.title}</h3>
        <p>{feature.description}</p>
        {feature.expandedText && (
          <>
            <div className="wf-extra">
              <p>{feature.expandedText}</p>
            </div>
            <button className="wf-more" type="button" onClick={() => setOpen((v) => !v)}>
              Learn more{' '}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M9 6l6 6-6 6" />
              </svg>
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
  useSectionReveal(splitRef, { revealDoneMs: 1200, threshold: 0.22 })

  return (
    <section className="why">
      <div className="wrap">
        <div className="why-head">
          {heading && <h2>{heading}</h2>}
          {subheading && <p>{subheading}</p>}
        </div>
        <div className="why-split" ref={splitRef}>
          <div className="why-col left">
            {featuresLeft.map((feature) => (
              <FeatureRow key={feature.title} feature={feature} />
            ))}
          </div>

          <div className="why-photo">
            <img src={photo || '/images/homepage/why-photo.jpg'} alt="A MyTerra campervan beneath Icelandic mountains" />
            {(badge.rating || badge.reviewBold) && (
              <div className="badge">
                {badge.rating && <span className="num">{badge.rating}</span>}
                <span className="lbl">
                  from <b>{badge.reviewBold || '12,400+ travellers'}</b>
                  <br />
                  {badge.reviewRest || 'who booked with us'}
                </span>
              </div>
            )}
          </div>

          <div className="why-col right">
            {featuresRight.map((feature) => (
              <FeatureRow key={feature.title} feature={feature} />
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
