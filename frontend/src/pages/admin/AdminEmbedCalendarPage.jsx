import { useEffect, useState } from 'react'
import { api, setAuthToken } from '../../api'
import { clearAuth, getStoredToken, storeAuth } from '../../auth'
import AdminCalendarPage from './AdminCalendarPage'
import '../../styles/admin-panel.css'

// #region agent log
const __tbDbgEmbed = (location, message, data, hypothesisId) => {
  fetch('http://127.0.0.1:7876/ingest/51365707-604b-4c5c-b2ec-cfe2c3d9fec8', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '66744b' },
    body: JSON.stringify({ sessionId: '66744b', location, message, data, hypothesisId, timestamp: Date.now() }),
  }).catch(() => {})
  const beaconBase = window.location.pathname.startsWith('/backend') ? '/backend' : ''
  fetch(`${beaconBase}/admin/debug/calendar-embed-log`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
    body: JSON.stringify({ location, message, data, hypothesisId }),
  }).catch(() => {})
}
// #endregion

function stripHandoffFromUrl() {
  const params = new URLSearchParams(window.location.search)
  if (!params.has('handoff')) return
  params.delete('handoff')
  const query = params.toString()
  const next = `${window.location.pathname}${query ? `?${query}` : ''}${window.location.hash}`
  window.history.replaceState({}, '', next)
}

export default function AdminEmbedCalendarPage() {
  const [ready, setReady] = useState(false)
  const [error, setError] = useState(null)

  useEffect(() => {
    let cancelled = false

    async function bootstrap() {
      const params = new URLSearchParams(window.location.search)
      const handoff = params.get('handoff')
        || (typeof window !== 'undefined' ? window.__TERRABOOK_CALENDAR_HANDOFF__ : null)

      // #region agent log
      __tbDbgEmbed('AdminEmbedCalendarPage.jsx:bootstrap', 'Bootstrap start', {
        hasHandoff: Boolean(handoff),
        hasStoredToken: Boolean(getStoredToken()),
        pathname: window.location.pathname,
      }, 'H4')
      // #endregion

      if (handoff) {
        try {
          setAuthToken(handoff)
          const res = await api.get('/user')
          const user = res.data?.data ?? res.data
          storeAuth(handoff, user)
          // #region agent log
          __tbDbgEmbed('AdminEmbedCalendarPage.jsx:bootstrap', 'Handoff auth ok', {
            userId: user?.id,
            role: user?.role,
          }, 'H4')
          // #endregion
          if (params.has('handoff')) {
            stripHandoffFromUrl()
          }
          if (typeof window !== 'undefined') {
            delete window.__TERRABOOK_CALENDAR_HANDOFF__
          }
        } catch (err) {
          // #region agent log
          __tbDbgEmbed('AdminEmbedCalendarPage.jsx:bootstrap', 'Handoff auth failed', {
            message: err?.message,
            status: err?.response?.status,
          }, 'H4')
          // #endregion
          if (!cancelled) {
            clearAuth()
            setError('Could not sign in to the calendar. Open Filament again or log in at the React admin.')
          }
          return
        }
      }

      if (!getStoredToken()) {
        // #region agent log
        __tbDbgEmbed('AdminEmbedCalendarPage.jsx:bootstrap', 'No stored token', {}, 'H4')
        // #endregion
        if (!cancelled) {
          setError('You are not signed in. Log in through Filament or the React admin first.')
        }
        return
      }

      if (!cancelled) {
        // #region agent log
        __tbDbgEmbed('AdminEmbedCalendarPage.jsx:bootstrap', 'Bootstrap ready', {}, 'H4')
        const reactStatus = document.getElementById('calendar-embed-react-status')
        if (reactStatus) reactStatus.textContent = 'ready'
        // #endregion
        setReady(true)
      }
    }

    bootstrap()

    return () => {
      cancelled = true
    }
  }, [])

  if (error) {
    return (
      <div style={{ padding: '1.5rem', fontFamily: 'system-ui, sans-serif', color: '#b91c1c' }}>
        {error}
      </div>
    )
  }

  if (!ready) {
    return (
      <div className="admin-calendar-embed-loading">
        Loading calendar...
      </div>
    )
  }

  return <AdminCalendarPage embed />
}
