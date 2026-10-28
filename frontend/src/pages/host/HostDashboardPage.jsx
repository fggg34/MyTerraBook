import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { AlertCircle, Car, Home, Plus } from 'lucide-react'
import { getHostCarBookings, getHostDashboard, getHostGuestHouseBookings } from '../../api/host'
import { usePageContent } from '../../context/SiteContentContext'
import { getStoredToken } from '../../auth'
import HostReservationsCalendar from '../../components/host/HostReservationsCalendar'
import { formatCurrency } from '../../utils/format'
import { normalizeHostCarBookings, normalizeHostStayBookings } from '../../utils/hostBookings'

function OverviewCard({ tone, icon: Icon, title, href, metrics, footer }) {
  const Tag = href ? Link : 'div'
  const tagProps = href ? { to: href } : {}

  return (
    <Tag className={`host-overview-card host-overview-card--${tone}`} {...tagProps}>
      <div className="host-overview-card-head">
        <span className="host-overview-card-icon">
          <Icon size={16} />
        </span>
        <span className="host-overview-card-title">{title}</span>
        {href && <span className="host-overview-card-link">Manage</span>}
      </div>
      <div className={`host-overview-metrics ${metrics.length === 2 ? 'host-overview-metrics--pair' : ''}`}>
        {metrics.map((metric) => (
          <div
            key={metric.label}
            className={`host-overview-metric ${metric.highlight ? 'is-highlight' : ''}`}
          >
            <strong>{metric.value}</strong>
            <span>{metric.label}</span>
          </div>
        ))}
      </div>
      {footer}
    </Tag>
  )
}

export default function HostDashboardPage() {
  const { page: copy } = usePageContent('host-panel')
  const onboarding = copy.onboarding || {}
  const overview = copy.overview || {}
  const steps = onboarding.steps?.length ? onboarding.steps : [
    { title: 'Complete your host profile', text: 'Company details, kennitala, partner logo and contract rental terms.' },
    { title: 'Add your first listing', text: 'Create a vehicle or guesthouse with photos, pricing and availability.' },
    { title: 'Submit for review', text: 'The MyTerraBook team checks your listing, then it goes live for travellers.' },
  ]
  const [stats, setStats] = useState(null)
  const [carBookings, setCarBookings] = useState([])
  const [stayBookings, setStayBookings] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  const loadDashboard = useCallback(() => {
    if (!getStoredToken()) {
      setError('Your session is not ready yet. Please sign in again.')
      setStats(null)
      setCarBookings([])
      setStayBookings([])
      setLoading(false)
      return
    }

    setLoading(true)
    setError(null)

    Promise.all([
      getHostDashboard(),
      getHostCarBookings({ per_page: 200 }),
      getHostGuestHouseBookings({ per_page: 200 }),
    ])
      .then(([dashboard, cars, stays]) => {
        setStats(dashboard.data.data)
        setCarBookings(normalizeHostCarBookings(cars.data.data))
        setStayBookings(normalizeHostStayBookings(stays.data.data))
      })
      .catch((err) => {
        setStats(null)
        setCarBookings([])
        setStayBookings([])
        if (err.response?.status === 401) {
          setError('Your session expired. Please sign out and sign in again.')
          return
        }
        if (!err.response) {
          setError('Could not reach the server. Make sure the backend is running on port 8080.')
          return
        }
        setError(err.response?.data?.message || 'Could not load dashboard.')
      })
      .finally(() => setLoading(false))
  }, [])

  useEffect(() => {
    loadDashboard()
  }, [loadDashboard])

  const pendingReview = stats
    ? stats.guest_houses.pending_review + stats.cars.pending_review
    : 0
  const pendingBookings = stats
    ? stats.bookings.pending_car_orders + stats.bookings.pending_guesthouse_bookings
    : 0
  const draftCount = stats ? stats.cars.draft + stats.guest_houses.draft : 0
  const rejectedCount = stats ? stats.cars.rejected + stats.guest_houses.rejected : 0
  const totalListings = stats
    ? stats.cars.draft + stats.cars.pending_review + stats.cars.live + stats.cars.rejected
      + stats.guest_houses.draft + stats.guest_houses.pending_review + stats.guest_houses.live + stats.guest_houses.rejected
    : 0
  const showOnboarding = !loading && !!stats && totalListings === 0

  return (
    <div className="host-dashboard">
      {showOnboarding && (
        <section className="host-onboarding">
          <p className="host-panel-kicker">{onboarding.eyebrow || 'GETTING STARTED'}</p>
          <h2>{onboarding.title || "Welcome, let's get your first listing live"}</h2>
          <p>{onboarding.lead || 'Three short steps and travellers can book your vehicles and guesthouses on MyTerraBook.'}</p>
          <ol className="host-onboarding-steps">
            {steps.map((step, index) => (
              <li key={step.title}>
                <span className="host-onboarding-num">{index + 1}</span>
                <span>
                  <strong>{step.title}</strong>
                  {step.text ? <span> {step.text}</span> : null}
                </span>
              </li>
            ))}
          </ol>
          <div className="host-actions">
            <Link to="/host/cars/new" className="host-btn vehicle"><Plus size={15} /> Add vehicle</Link>
            <Link to="/host/guesthouses/new" className="host-btn stay"><Plus size={15} /> Add guesthouse</Link>
          </div>
        </section>
      )}

      <HostReservationsCalendar
        carBookings={carBookings}
        stayBookings={stayBookings}
        loading={loading}
        labels={copy.calendar}
        upcomingLabel={copy.upcoming?.title}
      />

      <section className="host-overview">
        <div className="host-overview-head">
          <h2>{overview.title || 'Overview'}</h2>
          {overview.lead ? <p>{overview.lead}</p> : null}
          {error && <p className="host-overview-error">{error}</p>}
        </div>

        <div className="host-overview-grid">
          <OverviewCard
            tone="vehicle"
            icon={Car}
            title="Vehicles"
            href="/host/cars"
            metrics={[
              {
                label: 'Live',
                value: stats ? stats.cars.live : '—',
              },
              {
                label: 'Revenue',
                value: stats ? formatCurrency(stats.revenue_cents.car_orders / 100) : '—',
              },
              {
                label: 'Pending bookings',
                value: stats ? stats.bookings.pending_car_orders : '—',
                highlight: stats?.bookings.pending_car_orders > 0,
              },
            ]}
          />

          <OverviewCard
            tone="alert"
            icon={AlertCircle}
            title="Needs attention"
            metrics={[
              {
                label: 'Pending review',
                value: stats ? pendingReview : '—',
                highlight: pendingReview > 0,
              },
              {
                label: 'Drafts',
                value: stats ? draftCount : '—',
                highlight: draftCount > 0,
              },
              {
                label: 'Needs changes',
                value: stats ? rejectedCount : '—',
                highlight: rejectedCount > 0,
              },
              {
                label: 'Pending bookings',
                value: stats ? pendingBookings : '—',
                highlight: pendingBookings > 0,
              },
            ]}
            footer={
              (draftCount > 0 || rejectedCount > 0) ? (
                <div className="host-overview-card-foot">
                  <Link to="/host/cars" className="host-overview-foot-link">Finish vehicles</Link>
                  <Link to="/host/guesthouses" className="host-overview-foot-link">Finish guesthouses</Link>
                </div>
              ) : null
            }
          />

          <OverviewCard
            tone="stay"
            icon={Home}
            title="Guesthouses"
            href="/host/guesthouses"
            metrics={[
              {
                label: 'Live',
                value: stats ? stats.guest_houses.live : '—',
              },
              {
                label: 'Revenue',
                value: stats ? formatCurrency(stats.revenue_cents.guesthouse_bookings / 100) : '—',
              },
              {
                label: 'Pending bookings',
                value: stats ? stats.bookings.pending_guesthouse_bookings : '—',
                highlight: stats?.bookings.pending_guesthouse_bookings > 0,
              },
            ]}
          />
        </div>

        <div className="host-actions host-dashboard-actions">
          <Link to="/host/cars/new" className="host-btn vehicle">
            <Plus size={15} />
            Add vehicle
          </Link>
          <Link to="/host/guesthouses/new" className="host-btn stay">
            <Plus size={15} />
            Add guesthouse
          </Link>
          <Link to="/host/bookings" className="host-btn secondary">
            View bookings
          </Link>
          {error && (
            <button type="button" className="host-btn secondary" onClick={loadDashboard}>
              Retry
            </button>
          )}
        </div>
      </section>
    </div>
  )
}
