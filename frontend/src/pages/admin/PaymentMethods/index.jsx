import { useCallback, useEffect, useState } from 'react'
import { getAdminPaymentMethods, saveRapydPaymentMethod } from '../../../api/rapyd'
import RapydConfigModal from './RapydConfigModal'

/**
 * Admin — Payment Methods page.
 * Route target: /backend/admin/impact-rent/payment-methods
 */
export default function PaymentMethodsPage() {
  const [methods, setMethods] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [configuring, setConfiguring] = useState(null)

  const load = useCallback(async () => {
    setLoading(true)
    try {
      const { data } = await getAdminPaymentMethods()
      setMethods(data?.data || [])
    } catch {
      setError('Could not load payment methods.')
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    load()
  }, [load])

  const rapyd =
    methods.find((m) => m.code === 'rapyd_card') || {
      code: 'rapyd_card',
      name: 'Rapyd Card Payment',
      is_active: false,
      commission_label: 'Platform collects 20% online • 80% cash on arrival',
      environment: 'sandbox',
    }

  async function toggleActive(next) {
    // Optimistic update.
    setMethods((prev) =>
      prev.some((m) => m.code === 'rapyd_card')
        ? prev.map((m) => (m.code === 'rapyd_card' ? { ...m, is_active: next } : m))
        : [...prev, { ...rapyd, is_active: next }],
    )
    try {
      const { data } = await saveRapydPaymentMethod({ environment: rapyd.environment, is_active: next })
      if (data?.data) {
        setMethods((prev) => prev.map((m) => (m.code === 'rapyd_card' ? data.data : m)))
      }
    } catch {
      load()
    }
  }

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold text-gray-900">Payment Methods</h1>

      {loading ? (
        <p className="text-gray-500">Loading…</p>
      ) : error ? (
        <p className="text-red-600">{error}</p>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between">
              <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-lg font-bold text-indigo-600">
                  R
                </div>
                <div>
                  <h3 className="font-semibold text-gray-900">{rapyd.name}</h3>
                  <p className="text-xs text-gray-400 capitalize">{rapyd.environment} mode</p>
                </div>
              </div>

              <label className="relative inline-flex cursor-pointer items-center">
                <input
                  type="checkbox"
                  className="peer sr-only"
                  checked={!!rapyd.is_active}
                  onChange={(e) => toggleActive(e.target.checked)}
                />
                <div className="h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-teal-600 peer-checked:after:translate-x-full" />
              </label>
            </div>

            <p className="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-xs font-medium text-gray-600">
              {rapyd.commission_label}
            </p>

            <button
              type="button"
              onClick={() => setConfiguring(rapyd)}
              className="mt-4 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Configure
            </button>
          </div>
        </div>
      )}

      {configuring ? (
        <RapydConfigModal
          method={configuring}
          onClose={() => setConfiguring(null)}
          onSaved={(updated) => {
            if (updated) {
              setMethods((prev) =>
                prev.some((m) => m.code === 'rapyd_card')
                  ? prev.map((m) => (m.code === 'rapyd_card' ? updated : m))
                  : [...prev, updated],
              )
            } else {
              load()
            }
          }}
        />
      ) : null}
    </div>
  )
}
