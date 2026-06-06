import { Link } from 'react-router-dom'
import ContentPageHero from '../components/content/ContentPageHero'
import LoadingSpinner from '../components/ui/LoadingSpinner'
import useBlogPosts from '../hooks/useBlogPosts'
import '../styles/content-pages.css'

const FALLBACK_IMAGE = '/images/homepage/hero.jpg'

export default function GoodToKnowPage() {
  const { posts, loading, error } = useBlogPosts()

  return (
    <div className="content-page content-blog-index">
      <ContentPageHero
        title="Guides, routes & Iceland tips"
        lead="Practical advice from our Reykjavík team — driving, weather, itineraries and how to plan your trip."
      />

      <section className="blog content-body">
        <div className="wrap">
          {loading && (
            <div className="content-state">
              <LoadingSpinner />
            </div>
          )}

          {!loading && error && (
            <div className="content-state">
              <p>Unable to load articles right now.</p>
            </div>
          )}

          {!loading && !error && (
            <div className="bento" id="bento">
              {posts.map((post) => (
                <Link
                  key={post.slug}
                  to={`/good-to-know/${post.slug}`}
                  className={`bcard ${post.is_featured ? 'featured' : ''}`}
                >
                  {post.aurora ? (
                    <div className="bcard-aurora">
                      <span className="stars" />
                    </div>
                  ) : (
                    <div className="bcard-media">
                      <img src={post.featured_image || FALLBACK_IMAGE} alt={post.image_alt || post.title} />
                    </div>
                  )}
                  <span className="bcard-glow" />
                  <span className="bcard-read" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                      <path d="M7 17 17 7M9 7h8v8" />
                    </svg>
                  </span>
                  <div className="bcard-body">
                    <h3>{post.title}</h3>
                    {post.excerpt && <p className="bcard-desc">{post.excerpt}</p>}
                    {post.read_time && (
                      <div className="bcard-meta">{post.read_time}</div>
                    )}
                  </div>
                </Link>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  )
}
