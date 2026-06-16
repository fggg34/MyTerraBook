import { SpecIcon } from '../../utils/listingSpecIcons'

export default function ListingSpecGrid({ detailSpecs = [] }) {
  if (!detailSpecs.length) return null

  return (
    <div className="basics-grid">
      {detailSpecs.map((spec) => (
        <div key={`${spec.icon}-${spec.label}`} className="basic-item">
          <span className="basic-ic" aria-hidden>
            <SpecIcon icon={spec.icon} className="spec-ic" size={24} strokeWidth={1.8} />
          </span>
          <div className="basic-copy">
            <div className="basic-title">{spec.label}</div>
            {spec.hint ? <div className="basic-hint">{spec.hint}</div> : null}
          </div>
        </div>
      ))}
    </div>
  )
}
