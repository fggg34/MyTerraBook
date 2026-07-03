import { useEffect, useState } from 'react'
import { getAdminRapydPayments, formatMoney } from '../../../api/rapyd'

/**
 * Admin payments list table (Rapyd 20% online / 80% cash split).
 */
export default function PaymentsList() {
  const [payments, setPayments] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    let active = true
    ;(async () => {
      try {
        const { data } = await getAdminRapydPayments({ per_page: 50 })
        if (active) setPayments(data?.data || [])
      } catch {
        if (active) setError('Could not load payments.')
      } finally {
        if (active) setLoading(false)
      }
    })()
    return () => {
      active = false
    }
  }, [])

  return (
    <div className="space-y-4">
      <h1 className="text-xl font-bold text-gray-900">Payments</h1>

      {loading ? (
        <p className="text-gray-500">Loading…</p>
      ) : error ? (
        <p className="text-red-600">{error}</p>
      ) : (
        <div className="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
          <table className="min-w-full divide-y divide-gray-100 text-sm">
            <thead className="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
              <tr>
                <Th>Order</Th>
                <Th>Guest</Th>
                <Th>Host</Th>
                <Th>Listing</Th>
                <Th className="text-right">Total</Th>
                <Th className="text-right">Online (15%)</Th>
                <Th className="text-right">Cash (85%)</Th>
                <Th>Online Status</Th>
                <Th>Cash Status</Th>
                <Th>Date</Th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-50">
              {payments.map((p) => (
                <tr key={p.id} className="hover:bg-gray-50">
                  <Td>#{p.order_id}</Td>
                  <Td>{p.user?.name || '—'}</Td>
                  <Td>{p.host?.name || '—'}</Td>
                  <Td>{p.booking?.booking_reference || '—'}</Td>
                  <Td className="text-right">{formatMoney(p.total_price, p.currency)}</Td>
                  <Td className="text-right text-emerald-700">{formatMoney(p.platform_fee, p.currency)}</Td>
                  <Td className="text-right text-amber-700">{formatMoney(p.cash_due_on_arrival, p.currency)}</Td>
                  <Td>
                    <OnlinePill status={p.status} />
                  </Td>
                  <Td>
                    <span className="text-xs text-gray-500">
                      {p.status === 'paid' ? 'Awaiting cash' : '—'}
                    </span>
                  </Td>
                  <Td className="whitespace-nowrap text-gray-500">
                    {p.created_at ? new Date(p.created_at).toLocaleDateString() : '—'}
                  </Td>
                </tr>
              ))}
              {payments.length === 0 ? (
                <tr>
                  <td colSpan={10} className="px-4 py-8 text-center text-gray-400">
                    No payments yet.
                  </td>
                </tr>
              ) : null}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}

function OnlinePill({ status }) {
  const map = {
    paid: 'bg-emerald-100 text-emerald-700',
    pending: 'bg-gray-100 text-gray-600',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-purple-100 text-purple-700',
  }
  return (
    <span className={`rounded px-2 py-0.5 text-xs font-medium ${map[status] || map.pending}`}>
      {String(status || 'pending').toUpperCase()}
    </span>
  )
}

function Th({ children, className = '' }) {
  return <th className={`px-4 py-3 font-semibold ${className}`}>{children}</th>
}

function Td({ children, className = '' }) {
  return <td className={`px-4 py-3 ${className}`}>{children}</td>
}
