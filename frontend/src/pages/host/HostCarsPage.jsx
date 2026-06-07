import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { deleteHostCar, listHostCars } from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { useToast } from '../../context/ToastContext'

export default function HostCarsPage() {
  const { toast } = useToast()
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  const load = () => {
    setLoading(true)
    listHostCars()
      .then((res) => setItems(res.data.data || []))
      .catch(() => setItems([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const handleDelete = async (id) => {
    if (!window.confirm('Delete this vehicle?')) return
    try {
      await deleteHostCar(id)
      toast('Vehicle deleted', 'success')
      load()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not delete', 'error')
    }
  }

  if (loading) return <PageLoader message="Loading vehicles…" />

  return (
    <div>
      <div className="mb-4 flex items-center justify-between gap-4">
        <h2 className="text-xl font-bold text-brand-950">Your vehicles</h2>
        <Link to="/host/cars/new" className="host-btn primary">New vehicle</Link>
      </div>
      <div className="host-table-wrap">
        <table className="host-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Main category</th>
              <th>Sub category</th>
              <th>Status</th>
              <th>Units</th>
              <th />
            </tr>
          </thead>
          <tbody>
            {items.length === 0 ? (
              <tr>
                <td colSpan={6}>No vehicles yet.</td>
              </tr>
            ) : (
              items.map((item) => (
                <tr key={item.id}>
                  <td>{item.name}</td>
                  <td>{item.main_category?.name || '—'}</td>
                  <td>{item.sub_category?.name || item.category?.name || '—'}</td>
                  <td><ListingStatusBadge status={item.listing_status} /></td>
                  <td>{item.units_count ?? item.units_available}</td>
                  <td className="host-actions" style={{ margin: 0 }}>
                    <Link to={`/host/cars/${item.id}/edit`} className="host-btn secondary">Edit</Link>
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
