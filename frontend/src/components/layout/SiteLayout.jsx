import { Outlet } from 'react-router-dom'
import TopBar from '../homepage/TopBar'
import Header from '../homepage/Header'
import FaqSection from '../homepage/FaqSection'
import NewsSection from '../homepage/NewsSection'
import Footer from '../homepage/Footer'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import BackToTop from './BackToTop'

export default function SiteLayout() {
  const { siteData } = useSiteLayout()

  return (
    <>
      <TopBar {...(siteData.topbar || {})} />
      <Header {...(siteData.header || {})} />
      <Outlet />
      <FaqSection {...(siteData.faqSection || {})} />
      <NewsSection {...(siteData.newsSection || {})} />
      <Footer
        {...(siteData.footer || {})}
        hostCtaLabel={siteData.header?.ctaLabel}
        hostCtaHref={siteData.header?.ctaHref}
      />
      <BackToTop />
    </>
  )
}
