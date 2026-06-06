import { useRef } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

export default function HostBottomCta({
  cta = {},
  lead = "Join 1,800+ Iceland hosts already earning with MyTerraBook. It's free to list, and you could be booked within the week.",
}) {
  const sectionRef = useRef(null)
  useSectionReveal(sectionRef, { revealDoneMs: 1000, threshold: 0.15 })

  const title = cta.title ?? 'Ready to earn from your vehicle?'
  const submitLabel = cta.submitLabel ?? 'List for free'

  return (
    <section className="host-bottom-cta" ref={sectionRef}>
      <div className="wrap">
        <div className="host-bottom-cta-box">
          <div className="host-bottom-cta-topo" aria-hidden="true" />
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
