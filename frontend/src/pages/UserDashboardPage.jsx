import { Calendar, Car, Home, Settings, User, XCircle } from 'lucide-react'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { usePageContent } from '../context/SiteContentContext'
import { api } from '../api'
import Modal from '../components/ui/Modal'
import PageHead from '../components/seo/PageHead'
import StatusBadge from '../components/ui/StatusBadge'
import EmptyState from '../components/ui/EmptyState'
import { PageLoader } from '../components/ui/LoadingSpinner'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import { formatCurrency, formatDate } from '../utils/format'

const SIDEBAR_ICONS = {
  bookings: Calendar,
  stays: Home,
  profile: User,
  settings: Settings,
}

export default function UserDashboardPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: copy.title ?? 'My account' },
  })
  const sidebarLinks = (copy.sidebarLinks?.length ? copy.sidebarLinks : [
    { id: 'bookings', label: 'My Bookings' },
    { id: 'stays', label: 'My Stays' },
    { id: 'profile', label: 'Profile' },
    { id: 'settings', label: 'Settings' },
  ]).map((link) => ({
    ...link,
    icon: SIDEBAR_ICONS[link.id] ?? User,
  }))
  const { toast } = useToast()
  const [orders, setOrders] = useState([])
  const [stays, setStays] = useState([])
  const [loading, setLoading] = useState(true)
  const [staysLoading, setStaysLoading] = useState(false)
  const [activeTab, setActiveTab] = useState('bookings')
  const [cancelModal, setCancelModal] = useState(null)

  useEffect(() => {
    api
      .get('/me/orders')
      .then((res) => setOrders(res.data.data || []))
      .catch(() => setOrders([]))
      .finally(() => setLoading(false))
  }, [])

  useEffect(() => {
    if (activeTab !== 'stays') return
    setStaysLoading(true)
    api
      .get('/me/guest-house-bookings')
      .then((res) => setStays(res.data.data || []))
      .catch(() => setStays([]))
      .finally(() => setStaysLoading(false))
  }, [activeTab])

  const handleCancelRequest = (order) => {
    setCancelModal(order)
  }

  const confirmCancel = () => {
    if (cancelModal?.booking_reference) {
      api
        .post(`/me/guest-house-bookings/${cancelModal.booking_reference}/cancel`)
        .then(() => {
          toast('Booking cancelled', 'success')
          setStays((list) =>
            list.map((b) =>
              b.booking_reference === cancelModal.booking_reference
                ? { ...b, status: 'cancelled' }
                : b,
            ),
          )
        })
        .catch((err) => toast(err.response?.data?.message || 'Could not cancel', 'error'))
        .finally(() => setCancelModal(null))
      return
    }
    toast('Cancellation requests must be handled by our team. Please contact support.', 'info')
    setCancelModal(null)
  }

  const activeOrders = orders.filter(
    (o) => o.rental_status === 'started' || o.rental_status === 'upcoming' || o.order_status === 'pending',
  )
  const pastOrders = orders.filter((o) => !activeOrders.includes(o))

  if (loading) {
    return (
      <>
        <PageHead {...seo} />
        <PageLoader message="Loading your bookings…" />
      </>
    )
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <h1 className="section-title">My Dashboard</h1>

      <div className="mt-8 flex flex-col gap-8 lg:flex-row">
        <aside className="lg:w-56 lg:shrink-0">
          <nav className="rounded-xl border border-slate-200 bg-white p-2 shadow-card">
            {sidebarLinks.map(({ id, label, icon: Icon }) => (
              <button
                key={id}
                type="button"
                onClick={() => setActiveTab(id)}
                className={`flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors ${
                  activeTab === id
                    ? 'bg-accent/10 text-accent'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-brand-950'
                }`}
              >
                <Icon className="h-4 w-4" aria-hidden />
                {label}
              </button>
            ))}
          </nav>
        </aside>

        <div className="flex-1">
          {activeTab === 'bookings' && (
            <>
              {orders.length === 0 ? (
                <EmptyState
                  icon={Car}
                  title={copy.emptyBookings ?? 'No bookings yet'}
                  description="When you rent a car, your reservations will appear here."
                  action={
                    <Link to="/cars" className="btn-primary">
                      Browse cars
                    </Link>
                  }
                />
              ) : (
                <div className="space-y-8">
                  {activeOrders.length > 0 && (
                    <section>
                      <h2 className="text-lg font-bold text-brand-950">Active &amp; Upcoming</h2>
                      <BookingsTable orders={activeOrders} onCancel={handleCancelRequest} />
                    </section>
                  )}
                  {pastOrders.length > 0 && (
                    <section>
                      <h2 className="text-lg font-bold text-brand-950">Past Bookings</h2>
                      <BookingsTable orders={pastOrders} onCancel={handleCancelRequest} />
                    </section>
                  )}
                </div>
              )}
            </>
          )}

          {activeTab === 'stays' && (
            <>
              {staysLoading ? (
                <PageLoader message="Loading your stays…" />
              ) : stays.length === 0 ? (
                <EmptyState
                  icon={Home}
                  title={copy.emptyStays ?? 'No stays yet'}
                  description="When you book a guest house, it will appear here."
                  action={
                    <Link to="/guest-houses" className="btn-primary">
                      Browse guest houses
                    </Link>
                  }
                />
              ) : (
                <StaysTable stays={stays} onCancel={handleCancelRequest} />
              )}
            </>
          )}

          {activeTab === 'profile' && (
            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
              <h2 className="text-lg font-bold text-brand-950">Profile</h2>
              <p className="mt-2 text-sm text-slate-600">
                Profile editing will be available in a future update. Contact support to update your details.
              </p>
            </div>
          )}

          {activeTab === 'settings' && (
            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
              <h2 className="text-lg font-bold text-brand-950">Settings</h2>
              <p className="mt-2 text-sm text-slate-600">Notification preferences coming soon.</p>
            </div>
          )}
        </div>
      </div>

      <Modal
        open={!!cancelModal}
        onClose={() => setCancelModal(null)}
        title="Cancel booking?"
        footer={
          <>
            <button type="button" className="btn-secondary" onClick={() => setCancelModal(null)}>
              Keep booking
            </button>
            <button type="button" className="btn-primary bg-red-600 hover:bg-red-700" onClick={confirmCancel}>
              Request cancellation
            </button>
          </>
        }
      >
        {cancelModal && (
          <p>
            Are you sure you want to cancel booking <strong>{cancelModal.reference}</strong>?
          </p>
        )}
      </Modal>
    </div>
    </>
  )
}

function StaysTable({ stays, onCancel }) {
  return (
    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card">
      <div className="overflow-x-auto">
        <table className="w-full min-w-[640px] text-left text-sm">
          <thead className="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th className="px-4 py-3">Reference</th>
              <th className="px-4 py-3">Property</th>
              <th className="px-4 py-3">Dates</th>
              <th className="px-4 py-3">Total</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {stays.map((stay) => (
              <tr key={stay.id} className="hover:bg-slate-50/50">
                <td className="px-4 py-3 font-mono text-xs font-medium">{stay.booking_reference}</td>
                <td className="px-4 py-3">{stay.guest_house?.name || '—'}</td>
                <td className="px-4 py-3 text-slate-600">
                  {formatDate(stay.check_in)} → {formatDate(stay.check_out)}
                </td>
                <td className="px-4 py-3 font-semibold">{stay.total_formatted}</td>
                <td className="px-4 py-3">
                  <StatusBadge status={stay.status} />
                </td>
                <td className="px-4 py-3">
                  {['pending', 'confirmed'].includes(stay.status) && (
                    <button
                      type="button"
                      onClick={() => onCancel(stay)}
                      className="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700"
                    >
                      <XCircle className="h-3.5 w-3.5" aria-hidden />
                      Cancel
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

function BookingsTable({ orders, onCancel }) {
  return (
    <div className="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card">
      <div className="overflow-x-auto">
        <table className="w-full min-w-[640px] text-left text-sm">
          <thead className="border-b border-slate-100 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th className="px-4 py-3">Reference</th>
              <th className="px-4 py-3">Vehicle</th>
              <th className="px-4 py-3">Dates</th>
              <th className="px-4 py-3">Total</th>
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100">
            {orders.map((order) => (
              <tr key={order.id} className="hover:bg-slate-50/50">
                <td className="px-4 py-3 font-mono text-xs font-medium">{order.reference}</td>
                <td className="px-4 py-3">{order.car?.name || '—'}</td>
                <td className="px-4 py-3 text-slate-600">
                  {formatDate(order.pickup_at)} → {formatDate(order.dropoff_at)}
                </td>
                <td className="px-4 py-3 font-semibold">
                  {formatCurrency(order.total, order.currency)}
                </td>
                <td className="px-4 py-3">
                  <StatusBadge status={order.order_status} rentalStatus={order.rental_status} />
                </td>
                <td className="px-4 py-3">
                  {['pending', 'stand_by'].includes(order.order_status) && (
                    <button
                      type="button"
                      onClick={() => onCancel(order)}
                      className="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700"
                    >
                      <XCircle className="h-3.5 w-3.5" aria-hidden />
                      Cancel
                    </button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
