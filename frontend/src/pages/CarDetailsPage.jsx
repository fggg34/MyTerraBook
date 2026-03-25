import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { api } from '../api'

export default function CarDetailsPage() {
  const { id } = useParams()
  const [car, setCar] = useState(null)

  useEffect(() => {
    api.get(`/cars/${id}`).then((res) => setCar(res.data.data))
  }, [id])

  if (!car) return <p>Loading car details...</p>

  return (
    <section className="card">
      <h1>{car.name}</h1>
      <p>{car.description || 'No description available.'}</p>
      <p>Seats: {car.seats} | Bags: {car.bags}</p>
      <p>Transmission: {car.transmission}</p>
      <p>Fuel: {car.fuel_type}</p>
      <p>Daily price: ${car.base_daily_price}</p>
      <Link to={`/checkout?car_id=${car.id}`}>Book this car</Link>
    </section>
  )
}
