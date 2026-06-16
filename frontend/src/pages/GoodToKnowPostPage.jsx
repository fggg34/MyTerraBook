import { Link, useParams } from 'react-router-dom'
import ContentProse from '../components/content/ContentProse'
import PageHead from '../components/seo/PageHead'
import useBlogPosts from '../hooks/useBlogPosts'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/content-pages.css'

function formatDate(iso) {
  if (!iso) return null
  try {
    return new Intl.DateTimeFormat('en-GB', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }).format(new Date(iso))
  } catch {
    return null
  }
}

function ArticleMeta({ readTime, publishedAt, publishedLabel, variant = 'default' }) {
  if (!readTime && !publishedLabel) return null

  return (
    <div className={`gtk-article-meta ${variant === 'hero' ? 'gtk-article-meta--hero' : ''}`}>
      {readTime && <span>{readTime}</span>}
      {readTime && publishedLabel && <span className="gtk-article-meta-dot" aria-hidden="true" />}
      {publishedLabel && <time dateTime={publishedAt}>{publishedLabel}</time>}
    </div>
  )
}

function RelatedCard({ post }) {
  return (
    <Link to={`/good-to-know/${post.slug}`} className="gtk-article-related-card">
      {post.featured_image && (
        <div className="gtk-article-related-media">
          <img src={post.featured_image} alt={post.image_alt || post.title} loading="lazy" />
        </div>
      )}
      <div className="gtk-article-related-body">
        {post.kicker && <span className="gtk-article-related-kicker">{post.kicker}</span>}
        <h3>{post.title}</h3>
        {post.read_time && <span className="gtk-article-related-meta">{post.read_time}</span>}
      </div>
    </Link>
  )
}

export default function GoodToKnowPostPage() {
  const { slug } = useParams()
  const { post, loading, error } = useBlogPosts({ slug })
  const { posts: allPosts, loading: listLoading } = useBlogPosts()
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    source: post || {},
  })

  if (loading) {
    return <PageHead {...seo} />
  }

  if (error || !post) {
    return (
      <>
        <PageHead {...seo} robots="noindex" />
        <div className="content-page content-state">
          <h1>Article not found</h1>
          <p className="content-not-found-link">
            <Link to="/good-to-know">← Back to Good to Know</Link>
          </p>
        </div>
      </>
    )
  }

  const showCover = post.aurora || post.featured_image
  const publishedLabel = formatDate(post.published_at)
  const related = allPosts.filter((item) => item.slug !== slug).slice(0, 3)
  const showAside = !listLoading && related.length > 0

  return (
    <>
      <PageHead {...seo} />
      <div className="content-page gtk-article-page">
        <article className="gtk-article">
          <div className="wrap gtk-article-wrap">
            <Link to="/good-to-know" className="gtk-article-back">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              All articles
            </Link>

            {showCover ? (
              <figure className="gtk-article-hero">
                {post.aurora ? (
                  <div className="gtk-card-aurora gtk-article-aurora" aria-hidden="true">
                    <span className="stars" />
                  </div>
                ) : (
                  <img src={post.featured_image} alt={post.image_alt || post.title} />
                )}
                <figcaption className="gtk-article-hero-overlay">
                  {post.kicker && <span className="gtk-article-tag gtk-article-tag--hero">{post.kicker}</span>}
                  <div className="gtk-article-hero-bottom">
                    <h1>{post.title}</h1>
                    <ArticleMeta
                      readTime={post.read_time}
                      publishedAt={post.published_at}
                      publishedLabel={publishedLabel}
                      variant="hero"
                    />
                  </div>
                </figcaption>
              </figure>
            ) : null}

            <div className={`gtk-article-layout ${showAside ? 'gtk-article-layout--with-aside' : ''}`}>
              <div className="gtk-article-body">
                {!showCover && (
                  <header className="gtk-article-header">
                    {post.kicker && <span className="gtk-article-tag">{post.kicker}</span>}
                    <h1>{post.title}</h1>
                    <ArticleMeta
                      readTime={post.read_time}
                      publishedAt={post.published_at}
                      publishedLabel={publishedLabel}
                    />
                  </header>
                )}

                {post.excerpt && <h2 className="gtk-article-subtitle">{post.excerpt}</h2>}

                <div className="gtk-article-content">
                  <ContentProse html={post.body} />
                </div>

                <footer className="gtk-article-footer">
                  <Link to="/good-to-know" className="gtk-article-more">
                    <span>More Iceland guides</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                </footer>
              </div>

              {showAside && (
                <aside className="gtk-article-aside" aria-label="More guides">
                  <h2 className="gtk-article-aside-title">More guides</h2>
                  <div className="gtk-article-related">
                    {related.map((item) => (
                      <RelatedCard key={item.slug} post={item} />
                    ))}
                  </div>
                </aside>
              )}
            </div>
          </div>
        </article>
      </div>
    </>
  )
}
