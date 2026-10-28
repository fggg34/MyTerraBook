import { Outlet, useLocation } from 'react-router-dom'
import Header from '../homepage/Header'
import Footer from '../homepage/Footer'
import CookieBanner from '../site/CookieBanner'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import useHomepageBodyClass from '../../hooks/useHomepageBodyClass'
import BackToTop from './BackToTop'

const BARE_PATHS = new Set([
  '/login',
  '/register',
  '/forgot-password',
  '/reset-password',
  '/host/login',
  '/host/register',
  '/host/forgot-password',
  '/coming-soon',
  '/newsletter/unsubscribe',
])

export default function ContentLayout() {
  useHomepageBodyClass()
  const { siteData } = useSiteLayout()
  const { pathname } = useLocation()
  const bare = BARE_PATHS.has(pathname)

  if (bare) {
    return <Outlet />
  }

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
