import CmsImage from '../components/cms/CmsImage'
import ContentPageHero from '../components/content/ContentPageHero'
import PageHead from '../components/seo/PageHead'
import { usePageContent } from '../context/SiteContentContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/content-pages.css'

const PAGE_FALLBACK = {
  header: {
    title: 'Campsite Map of Iceland',
    lead: 'Plan overnight stops along the Ring Road with registered campsites across Iceland. Use the map to explore locations before you travel.',
    image: '',
    imageAlt: 'Campervan parked at an Icelandic campsite',
  },
  map: {
    embedUrl: 'https://www.google.com/maps/d/embed?mid=1wk_t105qo4BYqaBL7xTcLUz5uXQ&ehbc=2E312F',
    image: '',
    imageAlt: 'Map of registered campsites across Iceland',
  },
  note: 'Overnight stays in campervans and motorhomes are permitted at registered campsites. Always check opening dates and facilities before you arrive.',
  photos: [],
}

export default function CampsiteMapPage() {
  const { page } = usePageContent('campsite-map', PAGE_FALLBACK)
  const header = { ...PAGE_FALLBACK.header, ...page.header }
  const map = { ...PAGE_FALLBACK.map, ...page.map }
  const seo = usePageSeo('campsite-map', { source: { title: header.title } })
  const note = page.note ?? PAGE_FALLBACK.note
  const photos = (page.photos ?? PAGE_FALLBACK.photos).filter((item) => item?.image)

  return (
    <div className="content-page campsite-map-page">
      <PageHead {...seo} />
      <ContentPageHero title={header.title} lead={header.lead} />
      <section className="content-body campsite-map-body">
        <div className="wrap">
          {header.image ? (
            <figure className="campsite-map-hero-photo">
              <CmsImage src={header.image} alt={header.imageAlt || header.title} />
            </figure>
          ) : null}
          <div className="campsite-map-embed">
            {map.embedUrl ? (
              <iframe
                src={map.embedUrl}
                title={header.title}
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
                allowFullScreen
              />
            ) : map.image ? (
              <CmsImage src={map.image} alt={map.imageAlt || header.title} />
            ) : null}
          </div>
          {photos.length > 0 ? (
            <div className="campsite-map-gallery">
              {photos.map((photo, index) => (
                <figure className="campsite-map-gallery-item" key={`${photo.alt || 'photo'}-${index}`}>
                  <CmsImage src={photo.image} alt={photo.alt || ''} />
                  {photo.caption ? <figcaption>{photo.caption}</figcaption> : null}
                </figure>
              ))}
            </div>
          ) : null}
          {note ? <p className="campsite-map-note">{note}</p> : null}
        </div>
      </section>
    </div>
  )
}
