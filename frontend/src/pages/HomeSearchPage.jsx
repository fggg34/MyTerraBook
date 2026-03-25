import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useNavigate } from 'react-router-dom'
import { api } from '../api'

export default function HomeSearchPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [locations, setLocations] = useState([])
  const [form, setForm] = useState({
    pickup_location_id: '',
    dropoff_location_id: '',
    pickup_at: '',
    dropoff_at: '',
  })

  useEffect(() => {
    api.get('/locations').then((res) => setLocations(res.data.data || []))
  }, [])

  const onSearch = (event) => {
    event.preventDefault()
    const params = new URLSearchParams(form).toString()
    navigate(`/cars?${params}`)
  }

  return (
    <section>
      <h1>{t('findRentalCar')}</h1>
      <form className="card" onSubmit={onSearch}>
        <label>
          Pickup location
          <select
            value={form.pickup_location_id}
            onChange={(e) => setForm({ ...form, pickup_location_id: e.target.value })}
            required
          >
            <option value="">Select</option>
            {locations.map((location) => (
              <option key={location.id} value={location.id}>
                {location.name}
              </option>
            ))}
          </select>
        </label>
        <label>
          Dropoff location
          <select
            value={form.dropoff_location_id}
            onChange={(e) => setForm({ ...form, dropoff_location_id: e.target.value })}
            required
          >
            <option value="">Select</option>
            {locations.map((location) => (
              <option key={location.id} value={location.id}>
                {location.name}
              </option>
            ))}
          </select>
        </label>
        <label>
          Pickup date/time
          <input
            type="datetime-local"
            value={form.pickup_at}
            onChange={(e) => setForm({ ...form, pickup_at: e.target.value })}
            required
          />
        </label>
        <label>
          Dropoff date/time
          <input
            type="datetime-local"
            value={form.dropoff_at}
            onChange={(e) => setForm({ ...form, dropoff_at: e.target.value })}
            required
          />
        </label>
        <button type="submit">Search cars</button>
      </form>
    </section>
  )
}
