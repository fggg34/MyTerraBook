import { Link } from 'react-router-dom'
import { usePageContent } from '../../context/SiteContentContext'

const FALLBACK_IMAGE = '/images/homepage/hero.jpg'

function ArticleCard({ post, featured = false }) {
  const image = post.featured_image || FALLBACK_IMAGE

  return (
    <Link
      to={`/good-to-know/${post.slug}`}
      className={`gtk-card ${featured ? 'gtk-card--featured' : ''}`}
    >
      <div className="gtk-card-media">
        {post.aurora ? (
          <div className="gtk-card-aurora" aria-hidden="true">
            <span className="stars" />
          </div>
        ) : (
          <img src={image} alt={post.image_alt || post.title} loading="lazy" />
        )}
        {post.kicker && <span className="gtk-card-tag">{post.kicker}</span>}
      </div>
      <div className="gtk-card-body">
        <h3>{post.title}</h3>
        {post.excerpt && <p className="gtk-card-excerpt">{post.excerpt}</p>}
        <div className="gtk-card-foot">
          {post.read_time && <span className="gtk-card-meta">{post.read_time}</span>}
          <span className="gtk-card-arrow" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </span>
        </div>
      </div>
    </Link>
  )
}

export default function GoodToKnowSection({ posts }) {
  const { page } = usePageContent('good-to-know')
  const header = page.header ?? {}
  const featured = posts.find((p) => p.is_featured)
  const rest = posts.filter((p) => p !== featured)

  return (
    <section className="gtk-section" id="gtk-articles">
      <div className="wrap">
        <header className="gtk-header">
          <h1>{header.title ?? 'Guides, routes & Iceland tips'}</h1>
          {header.lead && <p className="gtk-lead">{header.lead}</p>}
        </header>

        <div className="gtk-grid">
          {featured && <ArticleCard post={featured} featured />}
          {rest.map((post) => (
            <ArticleCard key={post.slug} post={post} />
          ))}
        </div>
      </div>
    </section>
  )
}
