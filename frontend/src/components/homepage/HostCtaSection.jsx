import { useRef } from 'react'
import { Link } from 'react-router-dom'
import CmsImage from '../cms/CmsImage'
import useHostCtaEffects from '../../hooks/useHostCtaEffects'

export default function HostCtaSection({
  heading,
  lead,
  earnAmount = '€1,900',
  points = [],
  primaryLabel,
  primaryHref = '/become-a-host',
  secondaryLabel,
  secondaryHref,
  houseImage,
  vanImage,
  chipAmount,
}) {
  const sectionRef = useRef(null)
  useHostCtaEffects(sectionRef)

  return (
    <section className="hostcta" id="host" ref={sectionRef}>
      <div className="wrap">
        <div className="host-grid">
          <div className="host-stage">
            <div className="host-photo">
              <div className="ph-zoom">
                <CmsImage src={houseImage} alt="A guesthouse you could list on MyTerra" />
              </div>
            </div>
            <div className="host-van">
              <CmsImage src={vanImage} alt="A campervan parked outside the guesthouse" />
              <span className="van-tag">
                <span className="vt-dot" />
                Parked &amp; earning
              </span>
            </div>
            <div className="host-chip">
              <div>
                <div className="hc-head">
                  <span className="hc-live">
                    <span className="lv-dot" />
                    Live payouts
                  </span>
                </div>
                <div className="hc-amt">{chipAmount}</div>
                <div className="hc-lab">
                  paid to <b>1,800+ hosts</b> last year
                </div>
              </div>
              <div className="hc-spark" id="hcSpark" />
            </div>
            <div className="host-pings" id="hostPings" />
          </div>

          <div className="host-copy">
            {heading && <h2>{heading}</h2>}
            {lead && <p className="host-lead">{lead}</p>}
            <div className="host-earn">
              <span className="he-amt" id="heAmt">
                {earnAmount}
              </span>
              <span className="he-per">/ month on average</span>
            </div>
            <p className="host-earn-note">
              Top campervan hosts clear <b>€3,200+</b>. You keep 85%, we handle bookings, payments and insurance.
            </p>
            <div className="host-points">
              {points.map((point) => (
                <span className="host-point" key={point}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m5 13 4 4L19 7" />
                  </svg>
                  {point}
                </span>
              ))}
            </div>
            <div className="host-actions">
              {primaryLabel &&
                (primaryHref?.startsWith('/') ? (
                  <Link className="host-btn primary" to={primaryHref}>
                    {primaryLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                ) : (
                  <a className="host-btn primary" href={primaryHref || '#host'}>
                    {primaryLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </a>
                ))}
              {secondaryLabel &&
                (secondaryHref?.startsWith('/') ? (
                  <Link className="host-link" to={secondaryHref}>
                    {secondaryLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                ) : (
                  <a className="host-link" href={secondaryHref || '#'}>
                    {secondaryLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </a>
                ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  )
}
