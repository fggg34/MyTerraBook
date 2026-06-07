import { Car, XCircle } from 'lucide-react'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import Modal from '../../components/ui/Modal'
import StatusBadge from '../../components/ui/StatusBadge'
import EmptyState from '../../components/ui/EmptyState'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import { formatCurrency, formatDate } from '../../utils/format'

export default function ClientBookingsPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const { toast } = useToast()
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [cancelModal, setCancelModal] = useState(null)

  useEffect(() => {
    api
      .get('/me/orders')
      .then((res) => setOrders(res.data.data || []))
      .catch(() => setOrders([]))
      .finally(() => setLoading(false))
  }, [])

  const confirmCancel = () => {
    toast('Cancellation requests must be handled by our team. Please contact support.', 'info')
    setCancelModal(null)
  }

  const activeOrders = orders.filter(
    (o) => o.rental_status === 'started' || o.rental_status === 'upcoming' || o.order_status === 'pending',
  )
  const pastOrders = orders.filter((o) => !activeOrders.includes(o))

  if (loading) {
    return <PageLoader message="Loading your bookings…" />
  }

  return (
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
              <BookingsTable orders={activeOrders} onCancel={setCancelModal} />
            </section>
          )}
          {pastOrders.length > 0 && (
            <section>
              <h2 className="text-lg font-bold text-brand-950">Past Bookings</h2>
              <BookingsTable orders={pastOrders} onCancel={setCancelModal} />
            </section>
          )}
        </div>
      )}

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
    </>
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
