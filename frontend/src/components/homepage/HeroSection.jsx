import BookingModule from './BookingModule'

export default function HeroSection(props) {
  const { heading, subtitle, backgroundImage, ...bookingProps } = props

  return (
    <section className="hp-hero">
      <div
        className="hp-hero-bg"
        style={backgroundImage ? { backgroundImage: `url(${backgroundImage})` } : undefined}
      />
      <div className="homepage-wrap hp-hero-content">
        {heading && <h1>{heading}</h1>}
        {subtitle && <p>{subtitle}</p>}
        <BookingModule {...bookingProps} />
      </div>
    </section>
  )
}
