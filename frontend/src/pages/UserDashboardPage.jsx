import { useEffect, useState } from 'react'
import { api } from '../api'

export default function UserDashboardPage() {
  const [orders, setOrders] = useState([])

  useEffect(() => {
    api
      .get('/me/orders')
      .then((res) => setOrders(res.data.data || []))
      .catch(() => setOrders([]))
  }, [])

  return (
    <section>
      <h1>My orders</h1>
      <div className="grid">
        {orders.map((order) => (
          <article key={order.id} className="card">
            <h3>{order.reference}</h3>
            <p>Status: {order.order_status}</p>
            <p>
              Total: {order.currency} {order.total}
            </p>
          </article>
        ))}
      </div>
    </section>
  )
}
