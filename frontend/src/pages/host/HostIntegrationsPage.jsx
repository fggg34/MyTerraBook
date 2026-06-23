import { useEffect, useState } from 'react'
import { Copy, RefreshCw } from 'lucide-react'
import {
  fetchCarBlockedDays,
  getHostIntegrations,
  regenerateHostCarIntegrationToken,
} from '../../api/host'
import { api } from '../../api'
import { useToast } from '../../context/ToastContext'

function CopyButton({ value, label = 'Copy' }) {
  const { toast } = useToast()

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(value)
      toast('Copied to clipboard', 'success')
    } catch {
      toast('Could not copy', 'error')
    }
  }

  return (
    <button type="button" className="host-btn ghost host-integration-copy" onClick={handleCopy}>
      <Copy size={14} />
      <span>{label}</span>
    </button>
  )
}

function VehicleIntegrationCard({ vehicle, apiBase, onTokenRegenerated }) {
  const { toast } = useToast()
  const [preview, setPreview] = useState(null)
  const [previewLoading, setPreviewLoading] = useState(false)
  const [regenerating, setRegenerating] = useState(false)

  const endpoint = `${apiBase}/integrations/cars/${vehicle.id}/blocked-days`
  const curlExample = `curl -s -H "X-Integration-Token: ${vehicle.integration_token}" "${endpoint}"`

  const handleRegenerate = async () => {
    if (!window.confirm('Regenerate the API token? Existing integrations using the old token will stop working.')) {
      return
    }
    setRegenerating(true)
    try {
      const res = await regenerateHostCarIntegrationToken(vehicle.id)
      onTokenRegenerated(res.data.data)
      toast('Integration token regenerated', 'success')
      setPreview(null)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not regenerate token', 'error')
    } finally {
      setRegenerating(false)
    }
  }

  const handlePreview = async () => {
    setPreviewLoading(true)
    try {
      const res = await fetchCarBlockedDays(vehicle.id, vehicle.integration_token)
      setPreview(res.data)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not load blocked days', 'error')
    } finally {
      setPreviewLoading(false)
    }
  }

  return (
    <article className="host-integration-card">
      <div className="host-integration-card__head">
        <div>
          <h3>{vehicle.name}</h3>
          <p className="host-integration-card__meta">
            Vehicle ID {vehicle.id}
            {vehicle.units_available ? ` · ${vehicle.units_available} unit(s)` : ''}
          </p>
        </div>
        <button
          type="button"
          className="host-btn secondary"
          disabled={regenerating}
          onClick={handleRegenerate}
        >
          <RefreshCw size={14} />
          {regenerating ? 'Regenerating…' : 'Regenerate token'}
        </button>
      </div>

      <p className="host-integration-desc">
        Connect booking platforms and channel managers. This endpoint returns confirmed bookings and custom blocked days you set in the vehicle availability step.
      </p>

      <div className="host-integration-field">
        <label>Endpoint</label>
        <div className="host-integration-value">
          <code>{endpoint}</code>
          <CopyButton value={endpoint} />
        </div>
      </div>

      <div className="host-integration-field">
        <label>Integration token</label>
        <div className="host-integration-value">
          <code className="host-integration-token">{vehicle.integration_token}</code>
          <CopyButton value={vehicle.integration_token} label="Copy token" />
        </div>
        <p className="host-integration-hint">Send as header <code>X-Integration-Token</code> or query param <code>token</code>.</p>
      </div>

      <div className="host-integration-field">
        <label>Optional query params</label>
        <code className="host-integration-inline-code">from=2026-06-01&to=2026-12-31</code>
        <p className="host-integration-hint">Filter results to a date range (ISO 8601 dates).</p>
      </div>

      <div className="host-integration-field">
        <label>Example request</label>
        <pre className="host-integration-pre">{curlExample}</pre>
        <CopyButton value={curlExample} label="Copy curl" />
      </div>

      <div className="host-integration-field">
        <label>Example response</label>
        <pre className="host-integration-pre host-integration-pre--muted">{`{
  "vehicle": { "id": ${vehicle.id}, "name": "${vehicle.name}", "units_available": ${vehicle.units_available || 1} },
  "bookings": [
    { "id": 12, "start": "2026-07-01T10:00:00+00:00", "end": "2026-07-08T10:00:00+00:00", "type": "booking" }
  ],
  "custom_blocks": [
    { "id": 3, "start": "...", "end": "...", "units_blocked": 1, "notes": "Maintenance", "type": "custom_block" }
  ]
}`}</pre>
      </div>

      <div className="host-integration-actions">
        <button type="button" className="host-btn primary" disabled={previewLoading} onClick={handlePreview}>
          {previewLoading ? 'Loading live data…' : 'Load live blocked days'}
        </button>
      </div>

      {preview && (
        <div className="host-integration-preview">
          <p className="host-integration-preview__title">Live data</p>
          <pre className="host-integration-pre">{JSON.stringify(preview, null, 2)}</pre>
        </div>
      )}
    </article>
  )
}

export default function HostIntegrationsPage() {
  const { toast } = useToast()
  const [vehicles, setVehicles] = useState([])
  const [loading, setLoading] = useState(true)
  const apiBase = api.defaults.baseURL?.replace(/\/$/, '') || ''

  const load = () => {
    setLoading(true)
    getHostIntegrations()
      .then((res) => setVehicles(res.data.data || []))
      .catch(() => {
        setVehicles([])
        toast('Could not load integrations', 'error')
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const handleTokenRegenerated = (updated) => {
    setVehicles((prev) => prev.map((item) => (item.id === updated.id ? updated : item)))
  }

  return (
    <div className="host-integrations">
      <div className="host-integrations-intro">
        <h2>External connections</h2>
        <p>
          Use these APIs to sync availability with other platforms. Each vehicle has its own token and endpoint.
          Responses include confirmed bookings and custom blocked days from your host panel.
        </p>
      </div>

      {loading ? (
        <p className="host-integration-empty">Loading vehicles…</p>
      ) : vehicles.length === 0 ? (
        <div className="host-integration-empty">
          <p>No vehicles yet.</p>
          <p>Add a vehicle first, then return here to connect external platforms.</p>
        </div>
      ) : (
        <div className="host-integration-list">
          {vehicles.map((vehicle) => (
            <VehicleIntegrationCard
              key={vehicle.id}
              vehicle={vehicle}
              apiBase={apiBase}
              onTokenRegenerated={handleTokenRegenerated}
            />
          ))}
        </div>
      )}
    </div>
  )
}
