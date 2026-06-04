import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import GuestHouseCard from './GuestHouseCard'

export default function GuestHousesHighlight({
  title = 'Guest houses & stays',
  subtitle = 'Hand-picked homes across Iceland.',
  featured_slugs = [],
  ctaLabel = 'View all stays',
  ctaHref = '/guest-houses',
}) {
  const [houses, setHouses] = useState([])

  useEffect(() => {
    if (!featured_slugs?.length) return
    api
      .get('/guest-houses', { params: { per_page: 12 } })
      .then((res) => {
        const all = res.data?.data ?? []
        const featured = featured_slugs
          .map((slug) => all.find((h) => h.slug === slug))
          .filter(Boolean)
        setHouses(featured.length ? featured : all.slice(0, 3))
      })
      .catch(() => setHouses([]))
  }, [featured_slugs])

  if (!houses.length && !featured_slugs?.length) return null

  return (
    <section className="bg-slate-50 py-16">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <h2 className="section-title">{title}</h2>
            <p className="section-subtitle mt-2">{subtitle}</p>
          </div>
          <Link to={ctaHref} className="btn-secondary shrink-0">
            {ctaLabel}
          </Link>
        </div>
        <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {houses.map((house) => (
            <GuestHouseCard key={house.id} house={house} />
          ))}
        </div>
      </div>
    </section>
  )
}
