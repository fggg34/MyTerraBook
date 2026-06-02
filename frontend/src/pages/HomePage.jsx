import TopBar from '../components/homepage/TopBar'
import Header from '../components/homepage/Header'
import HeroSection from '../components/homepage/HeroSection'
import TrustStrip from '../components/homepage/TrustStrip'
import WhatWeRentSection from '../components/homepage/WhatWeRentSection'
import WhyMyTerraSection from '../components/homepage/WhyMyTerraSection'
import PicksSection from '../components/homepage/PicksSection'
import HowItWorksSection from '../components/homepage/HowItWorksSection'
import StaySection from '../components/homepage/StaySection'
import BlogSection from '../components/homepage/BlogSection'
import HostCtaSection from '../components/homepage/HostCtaSection'
import ReviewsSection from '../components/homepage/ReviewsSection'
import FaqSection from '../components/homepage/FaqSection'
import NewsSection from '../components/homepage/NewsSection'
import Footer from '../components/homepage/Footer'

export default function HomePage({ pageData = {} }) {
  return (
    <>
      <TopBar {...(pageData.topbar || {})} />
      <Header {...(pageData.header || {})} />
      <main>
        <HeroSection {...(pageData.hero || {})} />
        <TrustStrip items={pageData.trustItems || []} />
        <WhatWeRentSection {...(pageData.rentSection || {})} />
        <WhyMyTerraSection {...(pageData.whySection || {})} />
        <PicksSection {...(pageData.picksSection || {})} />
        <HowItWorksSection {...(pageData.howSection || {})} />
        <StaySection {...(pageData.staySection || {})} />
        <BlogSection {...(pageData.blogSection || {})} />
        <HostCtaSection {...(pageData.hostCtaSection || {})} />
        <ReviewsSection {...(pageData.reviewsSection || {})} />
        <FaqSection {...(pageData.faqSection || {})} />
        <NewsSection {...(pageData.newsSection || {})} />
      </main>
      <Footer {...(pageData.footer || {})} />
    </>
  )
}
