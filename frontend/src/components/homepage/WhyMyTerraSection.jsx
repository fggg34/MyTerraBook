import { useMemo, useRef, useState } from 'react'
import { Car, Caravan, HandCoins, Home, Phone, ShieldCheck } from 'lucide-react'
import CmsImage from '../cms/CmsImage'
import useMediaQuery from '../../hooks/useMediaQuery'
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

function FeatureRow({ feature, learnMoreLabel = 'Learn more' }) {
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
              {learnMoreLabel}{' '}
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

function WhyMobileStep({ feature, index = 0, learnMoreLabel = 'Learn more' }) {
  const [open, setOpen] = useState(false)

  return (
    <article className={`why-mobile-step wf${open ? ' open' : ''}`} style={{ '--i': index }}>
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
              {learnMoreLabel}{' '}
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

function WhyMobileStory({ features, photo, photoAlt = '', badge = {}, learnMoreLabel, badgeFromLabel = 'from' }) {
  if (!features.length) return null

  return (
    <div className="why-mobile-story">
      <div className="why-mobile-story__photo">
        <CmsImage src={photo} alt={photoAlt || ''} />
        {(badge.rating || badge.reviewBold) && (
          <div className="badge">
            {badge.rating && <span className="num">{badge.rating}</span>}
            <span className="lbl">
              {badgeFromLabel} <b>{badge.reviewBold}</b>
              <br />
              {badge.reviewRest}
            </span>
          </div>
        )}
      </div>

      <div className="why-mobile-story__panel">
        <div className="why-mobile-story__panel-inner">
          {features.map((feature, index) => (
            <WhyMobileStep key={feature.title} feature={feature} index={index} learnMoreLabel={learnMoreLabel} />
          ))}
        </div>
      </div>
    </div>
  )
}

export default function WhyMyTerraSection({
  heading,
  subheading,
  photo,
  photoAlt,
  learnMoreLabel = 'Learn more',
  badgeFromLabel = 'from',
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
          <WhyMobileStory
            features={allFeatures}
            photo={photo}
            photoAlt={photoAlt}
            badge={badge}
            learnMoreLabel={learnMoreLabel}
            badgeFromLabel={badgeFromLabel}
          />
        ) : (
          <div className="why-split why-split--desktop" ref={splitRef}>
            <div className="why-col left">
              {featuresLeft.map((feature) => (
                <FeatureRow key={feature.title} feature={feature} learnMoreLabel={learnMoreLabel} />
              ))}
            </div>

            <div className="why-photo">
              <CmsImage src={photo} alt={photoAlt || 'A MyTerra campervan beneath Icelandic mountains'} />
              {(badge.rating || badge.reviewBold) && (
                <div className="badge">
                  {badge.rating && <span className="num">{badge.rating}</span>}
                  <span className="lbl">
                    {badgeFromLabel} <b>{badge.reviewBold}</b>
                    <br />
                    {badge.reviewRest}
                  </span>
                </div>
              )}
            </div>

            <div className="why-col right">
              {featuresRight.map((feature) => (
                <FeatureRow key={feature.title} feature={feature} learnMoreLabel={learnMoreLabel} />
              ))}
            </div>
          </div>
        )}
      </div>
    </section>
  )
}
