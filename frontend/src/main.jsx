import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import ScrollToTop from './components/layout/ScrollToTop.jsx'
import { detectRouterBasename } from './utils/routerBasename'
import { applyBootstrapDocumentMeta } from './utils/siteBootstrap'
import { ensureClientSiteCache } from './utils/ensureClientSiteCache'
import './i18n'
import './index.css'
import './styles/product-card.css'
import './styles/homepage.css'
import './styles/content-pages.css'

applyBootstrapDocumentMeta()

function renderApp() {
  createRoot(document.getElementById('root')).render(
    <StrictMode>
      <BrowserRouter basename={detectRouterBasename()}>
        <ScrollToTop />
        <App />
      </BrowserRouter>
    </StrictMode>,
  )
}

ensureClientSiteCache().finally(renderApp)
