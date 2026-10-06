import { Link } from 'react-router-dom'
import useDestinations from '../../hooks/useDestinations'
import CountryFlag from '../ui/CountryFlag'
import { destinationLandingPath, destinationLocationPath, partnerDestinationGroups } from '../../utils/destination'

export default function DestinationsSection({ standalone = false }) {
  const Heading = standalone ? 'h1' : 'h2'
  const { destinations, isPending, isError, refetch } = useDestinations('campervan')
  return (
    <section className="destinations-section" id="destinations" aria-labelledby="destinations-title">
      <div className="wrap">
        <Heading id="destinations-title">Choose your next destination</Heading>
        <p>Explore campervans by country, then choose your pick-up location and travel dates.</p>
        {isPending ? <p role="status">Loading destinations…</p> : isError ? (
          <button type="button" onClick={() => refetch()}>Retry destinations</button>
        ) : destinations.length ? (
          <div className="destination-links">
            {destinations.map(({ code, name, locations }) => {
            const groups = partnerDestinationGroups(locations)
            return (
              <div key={code} className="destination-card">
                <Link to={destinationLandingPath({ name })}>
                  <span className="destination-link-label"><CountryFlag code={code} decorative />{name}</span><span aria-hidden="true">→</span>
                </Link>
                {groups.map((group) => (
                  <div key={group.name} className="destination-card-partner">
                    <span>{group.name}</span>
                    {group.locations.map((location) => (
                      <Link key={location.id} to={destinationLocationPath(code, location)}>{location.name}</Link>
                    ))}
                  </div>
                ))}
              </div>
            )
          })}
          </div>
        ) : <p>New destinations will appear here when rental vehicles are available.</p>}
      </div>
    </section>
  )
}
