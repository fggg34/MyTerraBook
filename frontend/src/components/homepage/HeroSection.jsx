import useMediaQuery from '../../hooks/useMediaQuery'
import BookingModule from './BookingModule'
import CmsImage from '../cms/CmsImage'

export default function HeroSection(props) {
  const {
    heading,
    subtitle,
    backgroundImage,
    mobileHeading,
    mobileSubtitle,
    mobileBackgroundImage,
    backgroundAlt,
    ...bookingProps
  } = props

  const isMobile = useMediaQuery('(max-width: 768px)')
  const image = (isMobile && mobileBackgroundImage) || backgroundImage || null
  const title = (isMobile && mobileHeading) || heading
  const description = (isMobile && mobileSubtitle) || subtitle

  return (
    <section className="hero">
      <div className="hero-bg-wrap">
        {image ? (
          <CmsImage
            className="hero-bg"
            src={image}
            alt={backgroundAlt || 'Campervan parked beneath Icelandic mountains'}
            loading="eager"
          />
        ) : (
          <div className="hero-bg hero-bg--placeholder" aria-hidden="true" />
        )}
      </div>
      <div className="hero-inner">
        <div className="hero-copy">
          {title && <h1>{title}</h1>}
          {description && <p>{description}</p>}
        </div>
        <BookingModule {...bookingProps} />
      </div>
    </section>
  )
}
