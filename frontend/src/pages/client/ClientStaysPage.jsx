import { Home, XCircle } from 'lucide-react'
import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import Modal from '../../components/ui/Modal'
import StatusBadge from '../../components/ui/StatusBadge'
import EmptyState from '../../components/ui/EmptyState'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import { formatDate } from '../../utils/format'

export default function ClientStaysPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const { toast } = useToast()
  const [stays, setStays] = useState([])
  const [loading, setLoading] = useState(true)
  const [cancelModal, setCancelModal] = useState(null)

  useEffect(() => {
    api
      .get('/me/guest-house-bookings')
      .then((res) => setStays(res.data.data || []))
      .catch(() => setStays([]))
      .finally(() => setLoading(false))
  }, [])

  const confirmCancel = () => {
    if (!cancelModal?.booking_reference) {
      setCancelModal(null)
      return
    }

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
  }

  if (loading) {
    return <PageLoader message="Loading your stays…" />
  }

  return (
    <>
      {stays.length === 0 ? (
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
        <StaysTable stays={stays} onCancel={setCancelModal} />
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
            Are you sure you want to cancel booking{' '}
            <strong>{cancelModal.booking_reference || cancelModal.reference}</strong>?
          </p>
        )}
      </Modal>
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
