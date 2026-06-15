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

export default function GoodToKnowPostPage() {
  const { slug } = useParams()
  const { post, loading, error } = useBlogPosts({ slug })
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

  return (
    <>
      <PageHead {...seo} />
      <div className="content-page gtk-article-page">
      <article className={`gtk-article ${showCover ? '' : 'gtk-article--text'}`}>
        <div className="wrap gtk-article-wrap">
          <Link to="/good-to-know" className="gtk-article-back">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
            All articles
          </Link>

          <header className="gtk-article-header">
            {post.kicker && <span className="gtk-article-tag">{post.kicker}</span>}
            <h1>{post.title}</h1>
            {(post.read_time || publishedLabel) && (
              <div className="gtk-article-meta">
                {post.read_time && <span>{post.read_time}</span>}
                {post.read_time && publishedLabel && <span className="gtk-article-meta-dot" aria-hidden="true" />}
                {publishedLabel && <time dateTime={post.published_at}>{publishedLabel}</time>}
              </div>
            )}
          </header>

          {showCover && (
            <figure className="gtk-article-cover">
              {post.aurora ? (
                <div className="gtk-card-aurora gtk-article-aurora" aria-hidden="true">
                  <span className="stars" />
                </div>
              ) : (
                <img src={post.featured_image} alt={post.image_alt || post.title} />
              )}
            </figure>
          )}

          <div className="gtk-article-content">
            {post.excerpt && <p className="gtk-article-lead">{post.excerpt}</p>}
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
      </article>
    </div>
    </>
  )
}
