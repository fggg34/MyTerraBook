import BookingModule from './BookingModule'

const HERO_FALLBACK = '/images/homepage/hero.jpg'

export default function HeroSection(props) {
  const {
    heading,
    subtitle,
    backgroundImage,
    mobileHeading,
    mobileSubtitle,
    mobileBackgroundImage,
    ...bookingProps
  } = props

  const desktopImage = backgroundImage || HERO_FALLBACK
  const mobileImage = mobileBackgroundImage || desktopImage

  return (
    <section className="hero">
      <picture className="hero-bg-wrap">
        {mobileBackgroundImage && mobileImage !== desktopImage && (
          <source media="(max-width: 768px)" srcSet={mobileImage} />
        )}
        <img
          className="hero-bg"
          src={desktopImage}
          alt="Campervan parked beneath Icelandic mountains"
        />
      </picture>
      <div className="hero-inner">
        <div className="hero-copy">
          {(heading || mobileHeading) && (
            <h1>
              {heading && <span className="hero-heading hero-heading--desktop">{heading}</span>}
              <span className="hero-heading hero-heading--mobile">{mobileHeading || heading}</span>
            </h1>
          )}
          {(subtitle || mobileSubtitle) && (
            <p>
              {subtitle && <span className="hero-subtitle hero-subtitle--desktop">{subtitle}</span>}
              <span className="hero-subtitle hero-subtitle--mobile">{mobileSubtitle || subtitle}</span>
            </p>
          )}
        </div>
        <BookingModule {...bookingProps} />
      </div>
    </section>
  )
}
