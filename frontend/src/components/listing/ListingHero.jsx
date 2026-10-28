import { Link } from 'react-router-dom'
import { Bed, Check, ChevronRight, KeyRound, MapPin, Share2, Tent } from 'lucide-react'

const ARCHIVE = {
  campervan: { label: 'Campervans', to: '/campervans' },
  car: { label: 'Cars', to: '/cars' },
  guesthouse: { label: 'Guesthouses', to: '/guesthouses' },
}

function typeChip(listing) {
  const raw = listing.categoryName || listing.listingType
  if (raw === 'campervan') return 'Campervan'
  if (raw === 'car') return 'Car'
  if (raw === 'guesthouse') return 'Guesthouse'
  return raw
}

function placeCrumb(listing) {
  if (listing.location?.country) return listing.location.country
  const loc = (listing.pickupLocations || []).find((item) => item.country || item.country_name || item.city)
  return loc?.country || loc?.country_name || loc?.city || ''
}

export default function ListingHero({ listing, onShare }) {
  const archive = ARCHIVE[listing.listingType] || ARCHIVE.campervan
  const specs = listing.detailSpecs || []
  const sleeps = specs.find((spec) => spec.icon === 'sleeps')
  const units = specs.find((spec) => spec.icon === 'units')
  const place = placeCrumb(listing)
  const pickupNames = (listing.pickupLocations || []).map((item) => item.name).filter(Boolean)
  const where = pickupNames.length
    ? `Pick-up: ${pickupNames.slice(0, 3).join(' · ')}`
    : listing.location?.formattedLine || ''
  const hostName = listing.partner?.name || listing.owner?.name || ''

  return (
    <header className="listing-hero">
      <div className="wrap">
        <nav className="listing-hero__crumb" aria-label="Breadcrumb">
          <Link to="/">Home</Link>
          <ChevronRight size={12} aria-hidden />
          <Link to={archive.to}>{archive.label}</Link>
          {place ? (
            <>
              <ChevronRight size={12} aria-hidden />
              <span>{place}</span>
            </>
          ) : null}
          <ChevronRight size={12} aria-hidden />
          <span className="is-current">{listing.title}</span>
        </nav>

        <div className="listing-hero__head">
          <div>
            <div className="listing-hero__chips">
              <span className="listing-chip">
                <Tent size={13} aria-hidden />
                {typeChip(listing)}
              </span>
              {sleeps ? (
                <span className="listing-chip">
                  <Bed size={13} aria-hidden />
                  {sleeps.label}
                </span>
              ) : null}
              {units ? (
                <span className="listing-chip listing-chip--gold">
                  <Check size={13} aria-hidden />
                  {units.label}
                </span>
              ) : null}
            </div>
            <h1>{listing.title}</h1>
            <div className="listing-hero__meta">
              {where ? (
                <span>
                  <MapPin size={15} aria-hidden />
                  {listing.location?.mapsUrl ? (
                    <a href={listing.location.mapsUrl} target="_blank" rel="noopener noreferrer">{where}</a>
                  ) : (
                    where
                  )}
                </span>
              ) : null}
              {hostName ? (
                <span>
                  <KeyRound size={15} aria-hidden />
                  {hostName}
                </span>
              ) : null}
            </div>
          </div>
          <div className="listing-hero__actions">
            <button type="button" className="listing-hero__action" onClick={onShare}>
              <Share2 size={15} aria-hidden />
              Share
            </button>
          </div>
        </div>
      </div>
    </header>
  )
}
