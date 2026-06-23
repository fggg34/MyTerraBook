import { useEffect, useState } from 'react'
import { Copy, RefreshCw } from 'lucide-react'
import {
  fetchHostBlockedDays,
  getHostIntegrations,
  regenerateHostIntegrationToken,
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

export default function HostIntegrationsPage() {
  const { toast } = useToast()
  const [integration, setIntegration] = useState(null)
  const [loading, setLoading] = useState(true)
  const [preview, setPreview] = useState(null)
  const [previewLoading, setPreviewLoading] = useState(false)
  const [regenerating, setRegenerating] = useState(false)
  const apiBase = api.defaults.baseURL?.replace(/\/$/, '') || ''

  const load = () => {
    setLoading(true)
    getHostIntegrations()
      .then((res) => setIntegration(res.data.data || null))
      .catch(() => {
        setIntegration(null)
        toast('Could not load integrations', 'error')
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [])

  const endpoint = integration?.blocked_days_endpoint || `${apiBase}/integrations/blocked-days`
  const token = integration?.integration_token || ''
  const vehicles = integration?.vehicles || []
  const curlExample = token
    ? `curl -s -H "X-Integration-Token: ${token}" "${endpoint}"`
    : ''

  const handleRegenerate = async () => {
    if (!window.confirm('Regenerate the API token? Existing integrations using the old token will stop working.')) {
      return
    }
    setRegenerating(true)
    try {
      const res = await regenerateHostIntegrationToken()
      setIntegration(res.data.data)
      setPreview(null)
      toast('Integration token regenerated', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not regenerate token', 'error')
    } finally {
      setRegenerating(false)
    }
  }

  const handlePreview = async () => {
    if (!token) return
    setPreviewLoading(true)
    try {
      const res = await fetchHostBlockedDays(token)
      setPreview(res.data)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not load blocked days', 'error')
    } finally {
      setPreviewLoading(false)
    }
  }

  return (
    <div className="host-integrations">
      <div className="host-integrations-intro">
        <h2>External connections</h2>
        <p>
          Use a single API endpoint to sync availability for all your vehicles with other platforms.
          Responses include confirmed bookings and custom blocked days from your host panel.
        </p>
      </div>

      {loading ? (
        <p className="host-integration-empty">Loading…</p>
      ) : !integration ? (
        <div className="host-integration-empty">
          <p>Could not load integration settings.</p>
        </div>
      ) : (
        <article className="host-integration-card">
          <div className="host-integration-card__head">
            <div>
              <h3>Blocked days API</h3>
              <p className="host-integration-card__meta">
                {vehicles.length} vehicle{vehicles.length === 1 ? '' : 's'} included
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
              <code className="host-integration-token">{token}</code>
              <CopyButton value={token} label="Copy token" />
            </div>
            <p className="host-integration-hint">Send as header <code>X-Integration-Token</code> or query param <code>token</code>.</p>
          </div>

          <div className="host-integration-field">
            <label>Optional query params</label>
            <code className="host-integration-inline-code">from=2026-06-01&to=2026-12-31</code>
            <p className="host-integration-hint">Filter results to a date range (ISO 8601 dates).</p>
          </div>

          {vehicles.length > 0 && (
            <div className="host-integration-field">
              <label>Your vehicles</label>
              <ul className="host-integration-vehicles">
                {vehicles.map((vehicle) => (
                  <li key={vehicle.id}>
                    <strong>{vehicle.name}</strong>
                    <span>ID {vehicle.id} · {vehicle.units_available} unit(s)</span>
                  </li>
                ))}
              </ul>
              <p className="host-integration-hint">Each vehicle is returned in the <code>vehicles</code> array of the API response.</p>
            </div>
          )}

          <div className="host-integration-field">
            <label>Example request</label>
            <pre className="host-integration-pre">{curlExample}</pre>
            <CopyButton value={curlExample} label="Copy curl" />
          </div>

          <div className="host-integration-field">
            <label>Example response</label>
            <pre className="host-integration-pre host-integration-pre--muted">{`{
  "vehicles": [
    {
      "id": 5,
      "name": "VW Camper",
      "units_available": 2,
      "bookings": [
        { "id": 12, "start": "2026-07-01T10:00:00+00:00", "end": "2026-07-08T10:00:00+00:00", "type": "booking" }
      ],
      "custom_blocks": [
        { "id": 3, "start": "...", "end": "...", "units_blocked": 1, "notes": "Maintenance", "type": "custom_block" }
      ]
    }
  ]
}`}</pre>
          </div>

          <div className="host-integration-actions">
            <button type="button" className="host-btn primary" disabled={previewLoading || !token} onClick={handlePreview}>
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
      )}
    </div>
  )
}
