import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { deleteHostGuestHouse, listHostGuestHouses } from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { useToast } from '../../context/ToastContext'

export default function HostGuestHousesPage() {
  const { toast } = useToast()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  const load = () => {
    setLoading(true)
    listHostGuestHouses()
      .then((res) => setItems(res.data.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this guesthouse?')) return
    try {
      await deleteHostGuestHouse(id)
      toast('Guesthouse deleted', 'success')
      load()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not delete', 'error')
    }
  }

  if (loading) return <PageLoader message="Loading guesthouses…" />

  return (
    <div>
      <div className="mb-4 flex items-center justify-between gap-4">
        <h2 className="text-xl font-bold text-brand-950">Your guesthouses</h2>
        <Link to="/host/guesthouses/new" className="host-btn primary">New guesthouse</Link>
      </div>
      <div className="host-table-wrap">
        <table className="host-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>City</th>
              <th>Status</th>
              <th>Price/night</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {items.length === 0 ? (
              <tr>
                <td colSpan={5}>No guesthouses yet.</td>
              </tr>
            ) : (
              items.map((item) => (
                <tr key={item.id}>
                  <td>
                    <div>{item.name}</div>
                    {item.address && <div className="text-xs text-slate-500">{item.address}</div>}
                  </td>
                  <td>{item.city}</td>
                  <td><ListingStatusBadge status={item.status} /></td>
                  <td>€{item.base_price_per_night_euros}</td>
                  <td className="host-actions" style={{ margin: 0 }}>
                    <Link to={`/host/guesthouses/${item.id}/edit`} className="host-btn secondary">Edit</Link>
                    <button type="button" className="host-btn danger" onClick={() => handleDelete(item.id)}>Delete</button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
