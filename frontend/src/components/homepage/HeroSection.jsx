import BookingModule from './BookingModule'
import CmsImage from '../cms/CmsImage'

export default function HeroSection(props) {
  const {
    heading,
    subtitle,
    backgroundImage,
    ...bookingProps
  } = props

  const image = backgroundImage || null

  return (
    <section className="hero">
      <div className="hero-bg-wrap">
        {image ? (
          <CmsImage
            className="hero-bg"
            src={image}
            alt="Campervan parked beneath Icelandic mountains"
            loading="eager"
          />
        ) : (
          <div className="hero-bg hero-bg--placeholder" aria-hidden="true" />
        )}
      </div>
      <div className="hero-inner">
        <div className="hero-copy">
          {heading && <h1>{heading}</h1>}
          {subtitle && <p>{subtitle}</p>}
        </div>
        <BookingModule {...bookingProps} />
      </div>
    </section>
  )
}
