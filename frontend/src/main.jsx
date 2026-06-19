import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import ScrollToTop from './components/layout/ScrollToTop.jsx'
import {
  applyBootstrapDocumentMeta,
  getBootstrappedHomepage,
  getBootstrappedSiteContent,
} from './utils/siteBootstrap'
import { preloadSiteAssets, readHomepageCache, readSiteContentCache } from './utils/siteContentCache'
import './i18n'
import './index.css'
import './styles/product-card.css'
import './styles/homepage.css'
import './styles/content-pages.css'

applyBootstrapDocumentMeta()
preloadSiteAssets(
  getBootstrappedSiteContent() ?? readSiteContentCache(),
  getBootstrappedHomepage() ?? readHomepageCache(),
)

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <BrowserRouter>
      <ScrollToTop />
      <App />
    </BrowserRouter>
  </StrictMode>,
)
