export default function ContentPageHero({ eyebrow, title, lead }) {
  return (
    <section className="content-hero">
      <div className="content-hero-aurora" aria-hidden="true" />
      <div className="wrap">
        {eyebrow && <span className="content-hero-eyebrow">{eyebrow}</span>}
        {title && <h1>{title}</h1>}
        {lead && <p className="content-lead">{lead}</p>}
      </div>
    </section>
  )
}
