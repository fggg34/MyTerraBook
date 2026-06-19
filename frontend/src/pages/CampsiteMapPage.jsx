import ContentPageHero from '../components/content/ContentPageHero'
import PageHead from '../components/seo/PageHead'
import { usePageContent } from '../context/SiteContentContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/content-pages.css'

const MAP_EMBED_URL = 'https://www.google.com/maps/d/embed?mid=1wk_t105qo4BYqaBL7xTcLUz5uXQ&ehbc=2E312F'

const PAGE_FALLBACK = {
  header: {
    title: 'Campsite Map of Iceland',
    lead: 'Plan overnight stops along the Ring Road with registered campsites across Iceland. Use the map to explore locations before you travel.',
  },
  note: 'Overnight stays in campervans and motorhomes are permitted at registered campsites. Always check opening dates and facilities before you arrive.',
}

export default function CampsiteMapPage() {
  const { page } = usePageContent('campsite-map', PAGE_FALLBACK)
  const header = { ...PAGE_FALLBACK.header, ...page.header }
  const seo = usePageSeo('campsite-map', { source: { title: header.title } })
  const note = page.note ?? PAGE_FALLBACK.note

  return (
    <div className="content-page campsite-map-page">
      <PageHead {...seo} />
      <ContentPageHero title={header.title} lead={header.lead} />
      <section className="content-body campsite-map-body">
        <div className="wrap">
          <div className="campsite-map-embed">
            <iframe
              src={MAP_EMBED_URL}
              title={header.title}
              loading="lazy"
              referrerPolicy="no-referrer-when-downgrade"
              allowFullScreen
            />
          </div>
          {note ? <p className="campsite-map-note">{note}</p> : null}
        </div>
      </section>
    </div>
  )
}
