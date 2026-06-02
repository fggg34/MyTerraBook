import TopBar from '../components/homepage/TopBar'
import Header from '../components/homepage/Header'
import HeroSection from '../components/homepage/HeroSection'
import TrustStrip from '../components/homepage/TrustStrip'
import WhatWeRentSection from '../components/homepage/WhatWeRentSection'
import WhyMyTerraSection from '../components/homepage/WhyMyTerraSection'
import Footer from '../components/homepage/Footer'
import '../styles/homepage.css'

export default function HomePage({ pageData = {} }) {
  return (
    <div className="homepage">
      <TopBar {...(pageData.topbar || {})} />
      <Header {...(pageData.header || {})} />
      <HeroSection {...(pageData.hero || {})} />
      <TrustStrip items={pageData.trustItems || []} />
      <WhatWeRentSection {...(pageData.rentSection || {})} />
      <WhyMyTerraSection {...(pageData.whySection || {})} />
      <Footer {...(pageData.footer || {})} />
    </div>
  )
}
