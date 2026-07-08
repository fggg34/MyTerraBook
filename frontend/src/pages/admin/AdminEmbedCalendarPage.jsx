import { useEffect, useState } from 'react'
import { api, setAuthToken } from '../../api'
import { clearAuth, getStoredToken, storeAuth } from '../../auth'
import AdminCalendarPage from './AdminCalendarPage'
import '../../styles/admin-panel.css'

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

      if (handoff) {
        try {
          setAuthToken(handoff)
          const res = await api.get('/user')
          const user = res.data?.data ?? res.data
          storeAuth(handoff, user)
          stripHandoffFromUrl()
        } catch {
          if (!cancelled) {
            clearAuth()
            setError('Could not sign in to the calendar. Open Filament again or log in at the React admin.')
          }
          return
        }
      }

      if (!getStoredToken()) {
        if (!cancelled) {
          setError('You are not signed in. Log in through Filament or the React admin first.')
        }
        return
      }

      if (!cancelled) {
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
