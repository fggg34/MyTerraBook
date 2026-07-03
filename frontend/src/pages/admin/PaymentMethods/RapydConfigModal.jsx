import { useEffect, useState } from 'react'
import { saveRapydPaymentMethod } from '../../../api/rapyd'

/**
 * Admin modal to configure the Rapyd card payment method.
 *
 * Props:
 *  - method: current payment method object (from admin/payment-methods)
 *  - onClose(): close the modal
 *  - onSaved(updatedMethod): called after a successful save
 */
export default function RapydConfigModal({ method, onClose, onSaved }) {
  const [accessKey, setAccessKey] = useState('')
  const [secretKey, setSecretKey] = useState('')
  const [environment, setEnvironment] = useState(method?.environment || 'sandbox')
  const [isActive, setIsActive] = useState(method?.is_active ?? true)
  const [saving, setSaving] = useState(false)
  const [error, setError] = useState('')
  const [copied, setCopied] = useState(false)

  useEffect(() => {
    setEnvironment(method?.environment || 'sandbox')
    setIsActive(method?.is_active ?? true)
  }, [method])

  const webhookUrl = method?.webhook_url || `${window.location.origin}/api/rapyd/webhook`

  async function handleSave() {
    setError('')
    setSaving(true)
    try {
      const payload = { environment, is_active: isActive }
      if (accessKey.trim()) payload.access_key = accessKey.trim()
      if (secretKey.trim()) payload.secret_key = secretKey.trim()
      const { data } = await saveRapydPaymentMethod(payload)
      onSaved?.(data?.data)
      onClose?.()
    } catch (err) {
      setError(err?.response?.data?.message || 'Could not save configuration.')
    } finally {
      setSaving(false)
    }
  }

  function copyWebhook() {
    navigator.clipboard?.writeText(webhookUrl).then(() => {
      setCopied(true)
      setTimeout(() => setCopied(false), 1500)
    })
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
        <div className="flex items-start justify-between">
          <div>
            <h2 className="text-lg font-bold text-gray-900">Configure Rapyd Card Payment</h2>
            <p className="text-sm text-gray-500">Platform collects 20% online • 80% cash on arrival</p>
          </div>
          <button type="button" onClick={onClose} className="text-gray-400 hover:text-gray-600" aria-label="Close">
            ✕
          </button>
        </div>

        <div className="mt-5 space-y-4">
          <Field label="Access Key">
            <input
              type="text"
              value={accessKey}
              onChange={(e) => setAccessKey(e.target.value)}
              placeholder={method?.access_key_last4 ? `•••• •••• ${method.access_key_last4}` : 'Enter access key'}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
            />
          </Field>

          <Field label="Secret Key">
            <input
              type="password"
              value={secretKey}
              onChange={(e) => setSecretKey(e.target.value)}
              placeholder={method?.has_secret_key ? '•••••••••••• (saved)' : 'Enter secret key'}
              className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500"
            />
          </Field>

          <Field label="Environment">
            <div className="flex gap-2">
              {['sandbox', 'production'].map((env) => (
                <button
                  key={env}
                  type="button"
                  onClick={() => setEnvironment(env)}
                  className={`flex-1 rounded-lg border px-3 py-2 text-sm font-medium capitalize ${
                    environment === env
                      ? 'border-teal-600 bg-teal-50 text-teal-700'
                      : 'border-gray-300 text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  {env}
                </button>
              ))}
            </div>
          </Field>

          <div className="grid grid-cols-2 gap-3">
            <ReadOnlyField label="Platform Fee" value="20% (online)" />
            <ReadOnlyField label="Cash on Arrival" value="80% (cash)" />
          </div>

          <Field label="Webhook URL">
            <div className="flex items-center gap-2">
              <input
                readOnly
                value={webhookUrl}
                className="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600"
              />
              <button
                type="button"
                onClick={copyWebhook}
                className="whitespace-nowrap rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              >
                {copied ? 'Copied!' : 'Copy'}
              </button>
            </div>
            <p className="mt-1 text-xs text-gray-400">Register this URL in Rapyd Dashboard → Developers → Webhooks.</p>
          </Field>

          <label className="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" checked={isActive} onChange={(e) => setIsActive(e.target.checked)} className="rounded" />
            Active
          </label>

          {error ? <p className="text-sm text-red-600">{error}</p> : null}
        </div>

        <div className="mt-6 flex justify-end gap-3">
          <button type="button" onClick={onClose} className="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
            Cancel
          </button>
          <button
            type="button"
            onClick={handleSave}
            disabled={saving}
            className="rounded-lg bg-teal-600 px-5 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-60"
          >
            {saving ? 'Saving…' : 'Save'}
          </button>
        </div>
      </div>
    </div>
  )
}

function Field({ label, children }) {
  return (
    <div>
      <label className="mb-1 block text-sm font-medium text-gray-700">{label}</label>
      {children}
    </div>
  )
}

function ReadOnlyField({ label, value }) {
  return (
    <div className="rounded-lg bg-gray-50 px-3 py-2">
      <p className="text-xs text-gray-400">{label}</p>
      <p className="text-sm font-semibold text-gray-700">{value}</p>
    </div>
  )
}
