import { usePageContent } from '../context/SiteContentContext'
import HostLandingHero from '../components/become-host/HostLandingHero'
import HostProofMarquee from '../components/become-host/HostProofMarquee'
import HostHowItWorks from '../components/become-host/HostHowItWorks'
import HostFeaturesSection from '../components/become-host/HostFeaturesSection'
import HostReviewsSection from '../components/become-host/HostReviewsSection'
import HostFaqSection from '../components/become-host/HostFaqSection'
import HostBottomCta from '../components/become-host/HostBottomCta'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'

export default function BecomeHostPage() {
  const { page, loading } = usePageContent('become-a-host')
  const seo = usePageSeo('become-a-host', {
    source: {
      title: page.hero?.title,
      lead: page.hero?.lead,
      hero: page.hero,
    },
  })

  if (loading) {
    return (
      <main className="content-page become-host-page become-host-page--loading">
        <PageHead {...seo} />
      </main>
    )
  }

  return (
    <main className="content-page become-host-page">
      <PageHead {...seo} />
      <HostLandingHero hero={page.hero} />
      <HostProofMarquee stats={page.proof?.stats} />
      <HostHowItWorks howTabs={page.howTabs} />
      <HostFeaturesSection features={page.features} />
      <HostReviewsSection reviews={page.reviews} />
      <HostFaqSection faqItems={page.faqItems} />
      <HostBottomCta cta={page.cta} />
    </main>
  )
}
