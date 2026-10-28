import { Outlet } from 'react-router-dom'
import Header from '../homepage/Header'
import Footer from '../homepage/Footer'
import CookieBanner from '../site/CookieBanner'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import useHomepageBodyClass from '../../hooks/useHomepageBodyClass'
import BackToTop from './BackToTop'

/**
 * Search listing layout, matches offline HTML: topbar scrolls away, then one sticky
 * `.chrome` stack (nav + search bar + filters). Results grid lives below the chrome.
 */
export default function SearchResultsLayout() {
  useHomepageBodyClass()
  const { siteData } = useSiteLayout()

  return (
    <>
      <div className="chrome" id="searchChrome">
        <Header {...(siteData.header || {})} />
        <div id="searchChromeBar" className="search-chrome-bar" />
        <div className="scroll-progress" id="scrollProgress" aria-hidden="true" />
      </div>
      <Outlet />
      <Footer
        {...(siteData.footer || {})}
        hostCtaLabel={siteData.header?.ctaLabel}
        hostCtaHref={siteData.header?.ctaHref}
      />
      <CookieBanner />
      <BackToTop />
    </>
  )
}
