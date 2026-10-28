import { Outlet } from 'react-router-dom'
import Header from '../homepage/Header'
import Footer from '../homepage/Footer'
import CookieBanner from '../site/CookieBanner'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import useHomepageBodyClass from '../../hooks/useHomepageBodyClass'
import BackToTop from './BackToTop'

export default function SiteLayout() {
  useHomepageBodyClass()
  const { siteData } = useSiteLayout()

  return (
    <>
      <Header {...(siteData.header || {})} />
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
