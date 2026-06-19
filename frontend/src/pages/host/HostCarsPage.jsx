import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { deleteHostCar, listHostCars } from '../../api/host'
import ListingStatusBadge from '../../components/host/ListingStatusBadge'
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
            {loading ? (
              <tr>
                <td colSpan={6}>Loading…</td>
              </tr>
            ) : items.length === 0 ? (
              <tr>
                <td colSpan={6}>No vehicles yet, add your first one with “New vehicle” above.</td>
              </tr>
            ) : (
              items.map((item) => {
                const needsSetup = ['draft', 'rejected'].includes(item.listing_status)
                return (
                <tr key={item.id}>
                  <td data-label="Name">{item.name}</td>
                  <td data-label="Main category">{item.main_category?.name || '-'}</td>
                  <td data-label="Sub category">{item.sub_category?.name || item.category?.name || '-'}</td>
                  <td data-label="Status"><ListingStatusBadge status={item.listing_status} /></td>
                  <td data-label="Units">{item.units_count ?? item.units_available}</td>
                  <td className="host-actions">
                    <div className="host-table-actions">
                      <Link to={`/host/cars/${item.id}/edit`} className={`host-btn ${needsSetup ? 'primary' : 'secondary'}`}>{needsSetup ? 'Finish setup' : 'Edit'}</Link>
                      <button type="button" className="host-btn danger" onClick={() => handleDelete(item.id)}>Delete</button>
                    </div>
                  </td>
                </tr>
                )
              })
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
