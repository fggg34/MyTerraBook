import { Link, useParams } from 'react-router-dom'
import AboutFigma from '../components/content/AboutFigma'
import ContactFigma from '../components/content/ContactFigma'
import ContentPageHero from '../components/content/ContentPageHero'
import ContentProse from '../components/content/ContentProse'
import FaqFigma from '../components/content/FaqFigma'
import LegalFigma from '../components/content/LegalFigma'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import useSitePage from '../hooks/useSitePage'
import '../styles/content-pages.css'
import '../styles/content-figma.css'

const SLUG_ALIASES = {
  about: 'about',
  faq: 'faq',
  contact: 'contact',
  terms: 'terms',
  privacy: 'privacy',
  'refund-policy': 'refund-policy',
  cookies: 'cookies',
  'cardholder-terms': 'cardholder-terms',
}

export default function SitePagePage({ forcedSlug }) {
  const { slug: routeSlug } = useParams()
  const slug = forcedSlug || routeSlug
  const pageKey = SLUG_ALIASES[slug] || slug
  const { page, loading, error } = useSitePage(pageKey)
  const seo = usePageSeo(pageKey, {
    source: {
      title: page?.title,
      lead: page?.lead,
      hero: { title: page?.title, lead: page?.lead },
    },
  })

  if (slug === 'faq') {
    return (<><PageHead {...seo} /><FaqFigma /></>)
  }

  if (slug === 'contact') {
    return (<><PageHead {...seo} /><ContactFigma /></>)
  }

  if (['terms', 'privacy', 'refund-policy', 'cookies', 'cardholder-terms'].includes(slug)) {
    return (<><PageHead {...seo} /><LegalFigma slug={slug} /></>)
  }

  if (loading) {
    return <PageHead {...seo} />
  }

  if (error || !page) {
    return (
      <>
        <PageHead {...seo} robots="noindex" />
        <div className="content-page content-state">
          <h1>Page not found</h1>
          <p className="mt-4">
            <Link to="/">Back to home</Link>
          </p>
        </div>
      </>
    )
  }

  if (slug === 'about') {
    return (
      <>
        <PageHead {...seo} />
        <AboutFigma />
      </>
    )
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="content-page">
      <ContentPageHero title={page.title} lead={page.lead} />
      <section className="content-body">
        <div className="wrap">
          <ContentProse html={page.body} />
        </div>
      </section>
      </div>
    </>
  )
}
