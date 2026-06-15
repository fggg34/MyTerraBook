import { Link, useParams } from 'react-router-dom'
import AboutPageContent from '../components/content/AboutPageContent'
import ContentPageHero from '../components/content/ContentPageHero'
import ContentProse from '../components/content/ContentProse'
import FaqPageContent from '../components/content/FaqPageContent'
import ContactForm from '../components/content/ContactForm'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import useSitePage from '../hooks/useSitePage'
import '../styles/content-pages.css'

const SLUG_ALIASES = {
  about: 'about',
  faq: 'faq',
  contact: 'contact',
  terms: 'terms',
  privacy: 'privacy',
  cookies: 'cookies',
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

  const content = page.content || {}

  if (slug === 'about') {
    return (
      <>
        <PageHead {...seo} />
        <AboutPageContent page={page} />
      </>
    )
  }

  if (slug === 'faq') {
    return (
      <>
        <PageHead {...seo} />
        <FaqPageContent page={page} />
      </>
    )
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="content-page">
      <ContentPageHero title={page.title} lead={page.lead} />

      {slug === 'contact' && (
        <section className="content-body">
          <div className="wrap content-contact-grid">
            <div className="content-contact-cards">
              {content.phone && (
                <div className="content-contact-card">
                  <strong>Phone</strong>
                  <a href={`tel:${content.phone.replace(/\s/g, '')}`}>{content.phone}</a>
                </div>
              )}
              {content.email && (
                <div className="content-contact-card">
                  <strong>Email</strong>
                  <a href={`mailto:${content.email}`}>{content.email}</a>
                </div>
              )}
              {content.address && (
                <div className="content-contact-card">
                  <strong>Address</strong>
                  <span style={{ whiteSpace: 'pre-line' }}>{content.address}</span>
                </div>
              )}
              {content.hours && (
                <div className="content-contact-card">
                  <strong>Hours</strong>
                  <span>{content.hours}</span>
                </div>
              )}
            </div>
            {content.show_form !== false && <ContactForm />}
          </div>
        </section>
      )}

      {slug !== 'contact' && (
        <section className="content-body">
          <div className="wrap">
            <ContentProse html={page.body} />
          </div>
        </section>
      )}
      </div>
    </>
  )
}
