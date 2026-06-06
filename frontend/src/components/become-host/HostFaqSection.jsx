import { useRef, useState } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

export default function HostFaqSection({
  faqItems = [],
  heading = 'Questions, answered.',
  subheading = 'Thinking about hosting but not sure where to start? Our team in Reykjavík is one message away.',
  contactEmail = 'hosts@myterrabook.com',
}) {
  const sectionRef = useRef(null)
  const [openIndex, setOpenIndex] = useState(0)

  useSectionReveal(sectionRef, { revealDoneMs: 1200, threshold: 0.1 })

  if (!faqItems.length) return null

  return (
    <section className="host-faq" id="faq" ref={sectionRef}>
      <div className="wrap host-faq-grid">
        <div className="host-faq-aside">
          <h2>{heading}</h2>
          <p className="host-faq-lead">{subheading}</p>
          <div className="host-faq-contact">
            <span className="host-faq-contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2.5" />
                <path d="m4 7 8 6 8-6" />
              </svg>
            </span>
            <span className="host-faq-contact-tx">
              Talk to the host team
              <b>
                <a href={`mailto:${contactEmail}`}>{contactEmail}</a>
              </b>
            </span>
          </div>
        </div>
        <div className="host-faq-list">
          {faqItems.map((item, index) => {
            const isOpen = openIndex === index
            return (
              <div key={item.num} className={`host-faq-item ${isOpen ? 'open' : ''}`}>
                <button
                  type="button"
                  className="host-faq-q"
                  aria-expanded={isOpen}
                  onClick={() => setOpenIndex(isOpen ? -1 : index)}
                >
                  <span className="host-faq-num">{item.num}</span>
                  <span className="host-faq-qt">{item.question}</span>
                  <svg className="host-faq-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                </button>
                <div className="host-faq-a">
                  <div className="host-faq-a-inner">{item.answer}</div>
                </div>
              </div>
            )
          })}
        </div>
      </div>
    </section>
  )
}
