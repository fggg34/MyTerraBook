import { Outlet } from 'react-router-dom'
import TopBar from '../homepage/TopBar'
import Header from '../homepage/Header'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import BackToTop from './BackToTop'
import BookingCheckoutFooter from './BookingCheckoutFooter'

/** Checkout chrome: header + slim footer only (no FAQ/News). */
export default function BookingLayout() {
  const { siteData } = useSiteLayout()

  return (
    <>
      <TopBar {...(siteData.topbar || {})} />
      <Header {...(siteData.header || {})} />
      <Outlet />
      <BookingCheckoutFooter />
      <BackToTop />
    </>
  )
}
