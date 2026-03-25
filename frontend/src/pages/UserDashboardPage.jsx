import { useEffect, useState } from 'react'
import { api } from '../api'

export default function UserDashboardPage() {
  const [bookings, setBookings] = useState([])

  useEffect(() => {
    api.get('/me/bookings')
      .then((res) => setBookings(res.data.data || []))
      .catch(() => setBookings([]))
  }, [])

  return (
    <section>
      <h1>My bookings</h1>
      <div className="grid">
        {bookings.map((booking) => (
          <article key={booking.id} className="card">
            <h3>{booking.reference}</h3>
            <p>Status: {String(booking.status).replaceAll('"', '')}</p>
            <p>Total: ${booking.total}</p>
          </article>
        ))}
      </div>
    </section>
  )
}
