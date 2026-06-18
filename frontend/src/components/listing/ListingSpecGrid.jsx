import CatalogIcon from '../../utils/CatalogIcon'
import { resolveListingSpecIconName } from '../../utils/listingSpecIcons'

export default function ListingSpecGrid({ detailSpecs = [] }) {
  if (!detailSpecs.length) return null

  return (
    <div className="basics-grid">
      {detailSpecs.map((spec) => (
        <div key={`${spec.icon}-${spec.label}`} className="basic-item">
          <span className="basic-ic" aria-hidden>
            <span className="spec-ic">
              <CatalogIcon
                name={resolveListingSpecIconName(spec.icon)}
                iconUrl={spec.iconUrl}
                size={24}
                strokeWidth={1.8}
                imgClassName="spec-ic-img"
                fallback={resolveListingSpecIconName(spec.icon)}
              />
            </span>
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
