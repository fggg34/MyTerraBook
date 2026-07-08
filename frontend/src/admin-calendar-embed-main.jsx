import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import AdminEmbedCalendarPage from './pages/admin/AdminEmbedCalendarPage'
import { detectRouterBasename } from './utils/routerBasename'
import './styles/admin-panel.css'

const rootEl = document.getElementById('terrabook-calendar-root')

if (rootEl) {
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
}
