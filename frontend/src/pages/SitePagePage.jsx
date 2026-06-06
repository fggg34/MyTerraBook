import { Link, useParams } from 'react-router-dom'
import ContentPageHero from '../components/content/ContentPageHero'
import ContentProse from '../components/content/ContentProse'
import FaqAccordion from '../components/content/FaqAccordion'
import ContactForm from '../components/content/ContactForm'
import LoadingSpinner from '../components/ui/LoadingSpinner'
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
  const { page, loading, error } = useSitePage(SLUG_ALIASES[slug] || slug)

  if (loading) {
    return (
      <div className="content-page content-state">
        <LoadingSpinner />
      </div>
    )
  }

  if (error || !page) {
    return (
      <div className="content-page content-state">
        <h1>Page not found</h1>
        <p className="mt-4">
          <Link to="/">Back to home</Link>
        </p>
      </div>
    )
  }

  const content = page.content || {}

  return (
    <div className="content-page">
      <ContentPageHero title={page.title} lead={page.lead} />

      {slug === 'faq' && (
        <section className="content-body content-faq-page">
          <div className="wrap">
            <section className="faq">
              <FaqAccordion phone={content.phone} email={content.email} items={content.items || []} />
            </section>
          </div>
        </section>
      )}

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

      {slug !== 'faq' && slug !== 'contact' && (
        <section className="content-body">
          <div className="wrap">
            <ContentProse html={page.body} />
          </div>
        </section>
      )}
    </div>
  )
}
