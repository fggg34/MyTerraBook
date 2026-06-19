import { useRef, useState } from 'react'
import useSectionReveal from '../../hooks/useSectionReveal'

export default function FaqSection({ heading, lead, phone, email, items = [] }) {
  const sectionRef = useRef(null)
  const defaultOpen = items.findIndex((item) => item.open)
  const [openIndex, setOpenIndex] = useState(defaultOpen >= 0 ? defaultOpen : 0)

  useSectionReveal(sectionRef, { revealDoneMs: 1800 })

  const handleToggle = (index) => {
    setOpenIndex((current) => (current === index ? -1 : index))
  }

  return (
    <section className="faq" id="faq" ref={sectionRef}>
      <div className="wrap">
        <div className="faq-box">
          <span className="faq-watermark" aria-hidden="true">
            FAQ
          </span>
          <div className="faq-grid">
            <div className="faq-l">
              {heading && (
                <h2 className="faq-rise" style={{ '--d': '.06s' }}>
                  {heading}
                </h2>
              )}
              {lead && (
                <p className="faq-lead faq-rise" style={{ '--d': '.12s' }}>
                  {lead}
                </p>
              )}
              <div className="faq-contacts">
                {phone && (
                  <a
                    className="faq-contact"
                    href={`tel:${phone.replace(/\s/g, '')}`}
                  >
                    <span className="faq-cic">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M4 5.5C4 4.7 4.7 4 5.5 4h2.8c.6 0 1.2.4 1.4 1l1.2 3.2c.2.5 0 1.1-.4 1.5L8.8 11.2a13 13 0 0 0 5.6 5.6l1.5-1.7c.4-.4 1-.6 1.5-.4l3.2 1.2c.6.2 1 .8 1 1.4v2.8c0 .8-.7 1.5-1.5 1.5C10.3 21.6 4 15.3 4 5.5Z" />
                      </svg>
                    </span>
                    <span className="faq-ctext">{phone}</span>
                  </a>
                )}
                {email && (
                  <a
                    className="faq-contact"
                    href={`mailto:${email}`}
                  >
                    <span className="faq-cic">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2.5" />
                        <path d="m4 7 8 6 8-6" />
                      </svg>
                    </span>
                    <span className="faq-ctext">{email}</span>
                  </a>
                )}
              </div>
            </div>

            <div className="faq-list">
              {items.map((item, index) => {
                const isOpen = openIndex === index
                return (
                  <div
                    key={item.num}
                    className={`faq-item ${isOpen ? 'open' : ''} faq-rise`}
                    style={{ '--d': `${0.16 + index * 0.08}s` }}
                  >
                    <button
                      className="faq-q"
                      type="button"
                      aria-expanded={isOpen}
                      onClick={() => handleToggle(index)}
                    >
                      <span className="faq-num">{item.num}</span>
                      <span className="faq-qt">{item.question}</span>
                      <svg className="faq-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="m6 9 6 6 6-6" />
                      </svg>
                    </button>
                    <div className="faq-a">
                      <div className="faq-a-inner">{item.answer}</div>
                    </div>
                  </div>
                )
              })}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
