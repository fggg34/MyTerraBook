import { useId, useRef } from 'react'
import { resolveCmsImage } from '../../api'
import useSectionReveal from '../../hooks/useSectionReveal'

const DEFAULT_PATTERN_SRC = '/images/patterns/myterrabook-mark.png'

export default function HostBottomCta({ cta = {} }) {
  const sectionRef = useRef(null)
  const patternId = useId().replace(/:/g, '')
  useSectionReveal(sectionRef, { revealDoneMs: 1000, threshold: 0.15 })

  const title = cta.title ?? 'Ready to earn from your vehicle?'
  const lead = cta.lead ?? "Join 1,800+ Iceland hosts already earning with MyTerraBook. It's free to list, and you could be booked within the week."
  const submitLabel = cta.submitLabel ?? 'List for free'
  const patternSrc = resolveCmsImage(cta.patternImage, DEFAULT_PATTERN_SRC)

  return (
    <section className="host-bottom-cta" ref={sectionRef}>
      <div className="wrap">
        <div className="host-bottom-cta-box">
          <div className="host-bottom-cta-topo" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" preserveAspectRatio="xMidYMid slice">
              <defs>
                <pattern
                  id={patternId}
                  width="168"
                  height="210"
                  patternUnits="userSpaceOnUse"
                  patternTransform="rotate(-18 84 105)"
                >
                  <image href={patternSrc} width="84" height="105" x="42" y="52" />
                </pattern>
              </defs>
              <rect width="100%" height="100%" fill={`url(#${patternId})`} />
            </svg>
          </div>
          <div className="host-bottom-cta-inner">
            <h2>{title}</h2>
            <p>{lead}</p>
            <div className="host-bottom-cta-actions">
              <a className="host-bottom-cta-btn host-bottom-cta-btn--solid" href="#signup">
                {submitLabel}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </a>
              <a className="host-bottom-cta-btn host-bottom-cta-btn--ghost" href="#how">
                See how it works
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
