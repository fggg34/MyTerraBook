import { useCallback, useState } from 'react'
import { Outlet } from 'react-router-dom'
import Header from '../homepage/Header'
import CookieBanner from '../site/CookieBanner'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import BackToTop from './BackToTop'
import BookingCheckoutFooter from './BookingCheckoutFooter'

/** Checkout chrome: header + slim footer only (no FAQ/News). */
export default function BookingLayout() {
  const { siteData } = useSiteLayout()
  const [showFooter, setShowFooter] = useState(false)
  const setCheckoutFooterVisible = useCallback((visible) => {
    setShowFooter(!!visible)
  }, [])

  return (
    <>
      <Header {...(siteData.header || {})} />
      <Outlet context={{ setCheckoutFooterVisible }} />
      {showFooter ? <BookingCheckoutFooter /> : null}
      <CookieBanner />
      <BackToTop />
    </>
  )
}
