import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api } from '../api'

export default function CarListingPage() {
  const [searchParams] = useSearchParams()
  const [cars, setCars] = useState([])

  useEffect(() => {
    api.get('/cars').then((res) => setCars(res.data.data || []))
  }, [])

  const query = Object.fromEntries(searchParams.entries())

  return (
    <section>
      <h1>Available cars</h1>
      <div className="grid">
        {cars.map((car) => (
          <article key={car.id} className="card">
            <h3>{car.name}</h3>
            <p>{car.transmission} - {car.fuel_type}</p>
            <p>From ${car.base_daily_price}/day</p>
            <div className="actions">
              <Link to={`/cars/${car.id}`}>Details</Link>
              <Link
                to={`/checkout?car_id=${car.id}&pickup_location_id=${query.pickup_location_id || ''}&dropoff_location_id=${query.dropoff_location_id || ''}&pickup_at=${query.pickup_at || ''}&dropoff_at=${query.dropoff_at || ''}`}
              >
                Book
              </Link>
            </div>
          </article>
        ))}
      </div>
    </section>
  )
}
