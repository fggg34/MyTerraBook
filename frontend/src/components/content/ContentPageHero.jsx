export default function ContentPageHero({ title, lead }) {
  return (
    <section className="content-hero">
      <div className="content-hero-aurora" aria-hidden="true" />
      <div className="wrap">
        {title && <h1>{title}</h1>}
        {lead && <p className="content-lead">{lead}</p>}
      </div>
    </section>
  )
}
