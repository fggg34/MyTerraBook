import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import AdminEmbedCalendarPage from './pages/admin/AdminEmbedCalendarPage'
import { detectRouterBasename } from './utils/routerBasename'
import './styles/admin-panel.css'

// #region agent log
const __tbDbg = (location, message, data, hypothesisId) => {
  fetch('http://127.0.0.1:7876/ingest/51365707-604b-4c5c-b2ec-cfe2c3d9fec8', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '66744b' },
    body: JSON.stringify({ sessionId: '66744b', location, message, data, hypothesisId, timestamp: Date.now() }),
  }).catch(() => {})
  const beaconBase = window.location.pathname.startsWith('/backend') ? '/backend' : ''
  fetch(`${beaconBase}/admin/debug/calendar-embed-log`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    credentials: 'same-origin',
    body: JSON.stringify({ location, message, data, hypothesisId }),
  }).catch(() => {})
}
// #endregion

const rootEl = document.getElementById('terrabook-calendar-root')

// #region agent log
__tbDbg('admin-calendar-embed-main.jsx', 'Entry executed', {
  hasRoot: Boolean(rootEl),
  pathname: window.location.pathname,
  basename: detectRouterBasename(),
  hasHandoff: Boolean(window.__TERRABOOK_CALENDAR_HANDOFF__),
}, 'H3')
// #endregion

if (rootEl) {
  const reactStatus = document.getElementById('calendar-embed-react-status')
  if (reactStatus) reactStatus.textContent = 'mounting'

  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: 1,
        refetchOnWindowFocus: false,
      },
    },
  })

  createRoot(rootEl).render(
    <StrictMode>
      <QueryClientProvider client={queryClient}>
        <BrowserRouter basename={detectRouterBasename()}>
          <AdminEmbedCalendarPage />
        </BrowserRouter>
      </QueryClientProvider>
    </StrictMode>,
  )

  // #region agent log
  __tbDbg('admin-calendar-embed-main.jsx', 'React root rendered', {}, 'H3')
  if (reactStatus) reactStatus.textContent = 'mounted'
  // #endregion
}
