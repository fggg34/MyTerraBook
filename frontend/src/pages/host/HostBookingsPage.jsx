import { useEffect, useState } from 'react'
import { api } from '../../api'
import { getHostCarBookings, getHostGuestHouseBookings } from '../../api/host'
import StatusBadge from '../../components/ui/StatusBadge'
import { useToast } from '../../context/ToastContext'
import { formatCurrency, formatDate } from '../../utils/format'

export default function HostBookingsPage() {
  const { toast } = useToast()
  const [tab, setTab] = useState('cars')
  const [carOrders, setCarOrders] = useState([])
  const [guestBookings, setGuestBookings] = useState([])
  const [loading, setLoading] = useState(true)

  const load = () => {
    setLoading(true)
    Promise.all([getHostCarBookings(), getHostGuestHouseBookings()])
      .then(([cars, stays]) => {
        setCarOrders(cars.data.data || [])
        setGuestBookings(stays.data.data || [])
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const openPdf = async (url, filename) => {
    try {
      const res = await api.get(url, { responseType: 'blob' })
      const blobUrl = URL.createObjectURL(res.data)
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = filename
      link.click()
      URL.revokeObjectURL(blobUrl)
    } catch {
      toast('Could not download contract', 'error')
    }
  }


  return (
    <div>
      <div className="host-actions mb-4">
        <button type="button" className={`host-btn ${tab === 'cars' ? 'primary' : 'secondary'}`} onClick={() => setTab('cars')}>Car rentals</button>
        <button type="button" className={`host-btn ${tab === 'stays' ? 'primary' : 'secondary'}`} onClick={() => setTab('stays')}>Guesthouse stays</button>
      </div>
      {tab === 'cars' ? (
        <div className="host-table-wrap">
          <table className="host-table">
            <thead><tr><th>Reference</th><th>Vehicle</th><th>Dates</th><th>Status</th><th>Total</th><th /></tr></thead>
            <tbody>
              {loading || carOrders.length === 0 ? <tr><td colSpan={6}>No car orders yet.</td></tr> : carOrders.map((o) => (
                <tr key={o.id}>
                  <td data-label="Reference">{o.reference}</td>
                  <td data-label="Vehicle">{o.car?.name}</td>
                  <td data-label="Dates">{formatDate(o.pickup_at)} – {formatDate(o.dropoff_at)}</td>
                  <td data-label="Status"><StatusBadge status={o.order_status} /></td>
                  <td data-label="Total">{formatCurrency(o.total_cents / 100, o.currency)}</td>
                  <td className="host-actions">
                    <div className="host-table-actions">
                      {o.order_status === 'confirmed' && (
                        <button type="button" className="host-btn secondary" onClick={() => openPdf(`/host/bookings/cars/${o.id}/contract.pdf`, `contract-${o.reference}.pdf`)}>PDF</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        <div className="host-table-wrap">
          <table className="host-table">
            <thead><tr><th>Reference</th><th>Property</th><th>Stay</th><th>Status</th><th>Total</th><th /></tr></thead>
            <tbody>
              {loading || guestBookings.length === 0 ? <tr><td colSpan={6}>No guesthouse bookings yet.</td></tr> : guestBookings.map((b) => (
                <tr key={b.id}>
                  <td data-label="Reference">{b.booking_reference}</td>
                  <td data-label="Property">{b.guest_house?.name}</td>
                  <td data-label="Stay">{formatDate(b.check_in)} – {formatDate(b.check_out)}</td>
                  <td data-label="Status"><StatusBadge status={b.status} /></td>
                  <td data-label="Total">{formatCurrency(b.total_amount / 100)}</td>
                  <td className="host-actions">
                    <div className="host-table-actions">
                      {['confirmed', 'completed'].includes(b.status) && (
                        <button type="button" className="host-btn secondary" onClick={() => openPdf(`/host/bookings/guest-houses/${b.id}/contract.pdf`, `contract-${b.booking_reference}.pdf`)}>PDF</button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
