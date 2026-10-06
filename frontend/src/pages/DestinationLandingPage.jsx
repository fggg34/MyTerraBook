import { useMemo } from 'react'
import { Navigate, Link, useParams } from 'react-router-dom'
import HeroSection from '../components/homepage/HeroSection'
import TrustStrip from '../components/homepage/TrustStrip'
import WhatWeRentSection from '../components/homepage/WhatWeRentSection'
import HowItWorksSection from '../components/homepage/HowItWorksSection'
import ReviewsSection from '../components/homepage/ReviewsSection'
import PageHead from '../components/seo/PageHead'
import CountryFlag from '../components/ui/CountryFlag'
import useDestinations from '../hooks/useDestinations'
import useHomepageData from '../hooks/useHomepageData'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import { destinationSearchPath, destinationSlug } from '../utils/destination'

export default function DestinationLandingPage() {
  const { countrySlug } = useParams()
  const destinationSlugParam = countrySlug?.replace(/-campervan-rental$/, '')
  const { destinations, isPending } = useDestinations('campervan')
  const { homepageData } = useHomepageData()
  const pageData = useMemo(
    () => mergeHomepageData(homepageData || {}, { useImageFallbacks: true }),
    [homepageData],
  )
  const destination = destinations.find(({ name }) => destinationSlug(name) === destinationSlugParam)

  if (!isPending && !destination) return <Navigate to="/destinations" replace />
  if (!destination) return null

  const { code, name } = destination
  const searchPath = destinationSearchPath(code)
  const campervanCard = pageData.rentSection?.cards?.find((card) => card.name === 'Campervans')
    || pageData.rentSection?.cards?.[0]
  const rentSection = {
    ...pageData.rentSection,
    heading: 'Campervan rental in ' + name,
    subtitle: 'Find the right campervan, select a pick-up location in ' + name + ', and set your travel dates.',
    cards: campervanCard ? [{ ...campervanCard, href: searchPath, tagline: 'Explore ' + name + ' at your own pace.' }] : [],
  }
  const hero = {
    ...pageData.hero,
    heading: 'Campervan rental in ' + name,
    subtitle: 'Choose your pick-up location and dates to compare available campervans in ' + name + '.',
    backgroundAlt: 'Campervan road trip in ' + name,
    tabs: [{ id: 'campervan', label: 'Campervan' }],
    footerHint: 'Planning a road trip in ' + name + '?',
    footerLinkLabel: 'Browse available campervans',
    footerLinkHref: searchPath,
    initialCountryCode: code,
  }
  const title = name + ' Campervan Rental | MyTerraBook'
  const description = 'Compare campervan rentals in ' + name + '. Choose your pick-up location and travel dates with MyTerraBook.'

  return (
    <>
      <PageHead title={title} description={description} canonical={typeof window === 'undefined' ? undefined : window.location.href} />
      <main className="destination-landing">
        <HeroSection {...hero} />
        <TrustStrip items={pageData.trustItems || []} />
        <section className="destination-landing-intro">
          <div className="wrap">
            <span className="destination-landing-eyebrow"><CountryFlag code={code} decorative /> Campervans in {name}</span>
            <h2>Plan your {name} road trip with confidence.</h2>
            <p>Use local pick-up points, transparent vehicle details and the same booking flow you already know from MyTerraBook.</p>
            <Link className="destination-landing-cta" to={searchPath}>See campervans in {name}</Link>
          </div>
        </section>
        <WhatWeRentSection {...rentSection} />
        <HowItWorksSection {...pageData.howSection} />
        <ReviewsSection {...pageData.reviewsSection} />
      </main>
    </>
  )
}