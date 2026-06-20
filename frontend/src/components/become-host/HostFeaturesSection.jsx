import { useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'
import HostPhoto from './HostPhoto'
import { becomeHostImages } from '../../data/becomeHostData'

const CARD_LAYOUT = [
  { accent: 'accent-green', layout: 'widget', image: null },
  { accent: 'accent-navy', layout: 'bleed', image: 'cardHouse' },
  { accent: 'accent-navy', layout: 'photo', image: 'whyPhoto' },
  { accent: 'accent-green', layout: 'stats', image: 'cardCar' },
]

export default function HostFeaturesSection({
  features = [],
  heading = 'Everything you need to earn more.',
  subheading = 'We bring the travellers, the tools and the protection. You bring the van, the car or the spare room.',
}) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1400, threshold: 0.1 })

  if (!features.length) return null

  return (
    <section className="host-feat" id="feat" ref={sectionRef}>
      <div className="wrap">
        <div className="host-feat-head">
          <h2>{heading}</h2>
          <p className="host-feat-sub">{subheading}</p>
        </div>
        <div className="host-feat-grid">
          {features.slice(0, 4).map((feature, index) => {
            const layout = CARD_LAYOUT[index] ?? CARD_LAYOUT[0]
            const imgSrc = feature.image || (layout.image ? becomeHostImages[layout.image] : null)

            if (layout.layout === 'widget') {
              return (
                <div key={feature.title} className={`host-feat-card ${layout.accent} host-feat-card--widget`}>
                  <div className="host-feat-widget">
                    <div className="host-feat-widget-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
                      </svg>
                    </div>
                    <div className="host-feat-widget-label">Iceland</div>
                    <div className="host-feat-chips">
                      <span className="host-feat-chip">Ring Road</span>
                      <span className="host-feat-chip">Search</span>
                      <span className="host-feat-chip">Email</span>
                      <span className="host-feat-chip">Social</span>
                    </div>
                  </div>
                  <div className="host-feat-card-body">
                    <h3>{feature.title}</h3>
                    <p>{feature.text}</p>
                  </div>
                </div>
              )
            }

            if (layout.layout === 'photo') {
              return (
                <div key={feature.title} className={`host-feat-card ${layout.accent} host-feat-card--photo`}>
                  <HostPhoto src={imgSrc} alt="" />
                  <div className="host-feat-card-body">
                    <h3>{feature.title}</h3>
                    <p>{feature.text}</p>
                  </div>
                </div>
              )
            }

            if (layout.layout === 'bleed') {
              return (
                <div key={feature.title} className={`host-feat-card ${layout.accent} host-feat-card--bleed`}>
                  <div className="host-feat-card-body">
                    <h3>{feature.title}</h3>
                    <p>{feature.text}</p>
                  </div>
                  <div className="host-feat-bleed-art">
                    <HostPhoto src={imgSrc} alt="Search results preview" />
                  </div>
                </div>
              )
            }

            return (
              <div key={feature.title} className={`host-feat-card ${layout.accent} host-feat-card--stats`}>
                <div className="host-feat-stats-art">
                  <HostPhoto src={imgSrc} alt="Earnings dashboard" />
                </div>
                <div className="host-feat-card-body">
                  <h3>{feature.title}</h3>
                  <p>{feature.text}</p>
                </div>
              </div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
