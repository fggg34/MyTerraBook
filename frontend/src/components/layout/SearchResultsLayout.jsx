import { Outlet } from 'react-router-dom'
import TopBar from '../homepage/TopBar'
import Header from '../homepage/Header'
import FaqSection from '../homepage/FaqSection'
import NewsSection from '../homepage/NewsSection'
import Footer from '../homepage/Footer'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import BackToTop from './BackToTop'

/**
 * Search listing layout — matches offline HTML: topbar scrolls away, then one sticky
 * `.chrome` stack (nav + search bar + filters). Results grid lives below the chrome.
 */
export default function SearchResultsLayout() {
  const { siteData } = useSiteLayout()

  return (
    <>
      <TopBar {...(siteData.topbar || {})} />
      <div className="chrome" id="searchChrome">
        <Header {...(siteData.header || {})} />
        <div id="searchChromeBar" className="search-chrome-bar" />
        <div className="scroll-progress" id="scrollProgress" aria-hidden="true" />
      </div>
      <Outlet />
      <FaqSection {...(siteData.faqSection || {})} />
      <NewsSection {...(siteData.newsSection || {})} />
      <Footer {...(siteData.footer || {})} />
      <BackToTop />
    </>
  )
}
