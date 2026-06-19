import { Outlet } from 'react-router-dom'
import TopBar from '../homepage/TopBar'
import Header from '../homepage/Header'
import Footer from '../homepage/Footer'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import useHomepageBodyClass from '../../hooks/useHomepageBodyClass'
import BackToTop from './BackToTop'

export default function ContentLayout() {
  useHomepageBodyClass()
  const { siteData } = useSiteLayout()

  return (
    <>
      <TopBar {...(siteData.topbar || {})} />
      <Header {...(siteData.header || {})} />
      <Outlet />
      <Footer
        {...(siteData.footer || {})}
        hostCtaLabel={siteData.header?.ctaLabel}
        hostCtaHref={siteData.header?.ctaHref}
      />
      <BackToTop />
    </>
  )
}
