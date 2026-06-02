import BookingModule from './BookingModule'

export default function HeroSection(props) {
  const { heading, subtitle, backgroundImage, ...bookingProps } = props

  return (
    <section className="hero">
      <img className="hero-bg" src={backgroundImage || '/images/homepage/hero.jpg'} alt="Campervan parked beneath Icelandic mountains" />
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
