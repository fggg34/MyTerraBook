import { useMemo, useRef, useState } from 'react'
import { Car, Caravan, ChevronDown, ChevronUp, HandCoins, Home, Phone, ShieldCheck } from 'lucide-react'
import useMediaQuery from '../../hooks/useMediaQuery'
import useScrollPinnedSteps from '../../hooks/useScrollPinnedSteps'
import useSectionReveal from '../../hooks/useSectionReveal'

const FEATURE_ICON_COMPONENTS = {
  campervan: Caravan,
  car: Car,
  house: Home,
  host: HandCoins,
  shield: ShieldCheck,
  phone: Phone,
}

function FeatureIcon({ name, image }) {
  if (image) {
    return <img src={image} alt="" className="wf-ic-img" aria-hidden />
  }
  const Icon = FEATURE_ICON_COMPONENTS[name] || ShieldCheck
  return <Icon size={25} strokeWidth={1.7} aria-hidden />
}

function FeatureRow({ feature }) {
  const [open, setOpen] = useState(false)

  return (
    <div className={`wf ${open ? 'open' : ''}`}>
      <span className="wf-ic">
        <FeatureIcon name={feature.icon} image={feature.iconImage} />
      </span>
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

function WhyMobileStep({ feature }) {
  const [open, setOpen] = useState(false)

  return (
    <article className={`why-mobile-step wf${open ? ' open' : ''}`}>
      <span className="wf-ic">
        <FeatureIcon name={feature.icon} image={feature.iconImage} />
      </span>
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
    </article>
  )
}

function WhyMobileScrollStory({ features, photo, badge = {} }) {
  const [trackEl, setTrackEl] = useState(null)
  const { activeIndex, barProgress, scrollToStep } = useScrollPinnedSteps(trackEl, {
    stepCount: features.length,
    enabled: features.length > 0,
  })
  const activeFeature = features[activeIndex]
  const lastIndex = features.length - 1
  const barFill = `${(features.length <= 1 ? 1 : barProgress) * 100}%`

  if (!features.length || !activeFeature) return null

  return (
    <div
      className="why-mobile-story"
      ref={setTrackEl}
      style={{ '--why-story-steps': features.length }}
    >
      <div className="why-mobile-story__sticky">
        <div className="why-mobile-story__photo">
          <img src={photo || '/images/homepage/why-photo.jpg'} alt="" />
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

        <div className="why-mobile-story__panel">
          <div className="why-mobile-story__panel-inner">
            <div className="why-mobile-story__nav">
              <button
                type="button"
                className="why-mobile-nav-btn"
                aria-label="Previous feature"
                disabled={activeIndex === 0}
                onClick={() => scrollToStep(activeIndex - 1)}
              >
                <ChevronUp size={16} strokeWidth={2} aria-hidden />
              </button>
              <div className="why-mobile-story__rail" aria-hidden>
                <span className="why-mobile-bar" style={{ height: barFill }} />
              </div>
              <button
                type="button"
                className="why-mobile-nav-btn"
                aria-label="Next feature"
                disabled={activeIndex === lastIndex}
                onClick={() => scrollToStep(activeIndex + 1)}
              >
                <ChevronDown size={16} strokeWidth={2} aria-hidden />
              </button>
            </div>

            <WhyMobileStep key={activeFeature.title} feature={activeFeature} />
          </div>
        </div>
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
  const isMobile = useMediaQuery('(max-width: 768px)')
  const allFeatures = useMemo(() => [...featuresLeft, ...featuresRight], [featuresLeft, featuresRight])

  useSectionReveal(splitRef, { revealDoneMs: 1200, threshold: 0.22, watch: !isMobile })

  return (
    <section className="why">
      <div className="wrap">
        <div className="why-head">
          {heading && <h2>{heading}</h2>}
          {subheading && <p className="sub">{subheading}</p>}
        </div>

        {isMobile ? (
          <WhyMobileScrollStory features={allFeatures} photo={photo} badge={badge} />
        ) : (
          <div className="why-split why-split--desktop" ref={splitRef}>
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
        )}
      </div>
    </section>
  )
}
