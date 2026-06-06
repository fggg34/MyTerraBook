import { Link, useParams } from 'react-router-dom'
import ContentProse from '../components/content/ContentProse'
import LoadingSpinner from '../components/ui/LoadingSpinner'
import useBlogPosts from '../hooks/useBlogPosts'
import '../styles/content-pages.css'

const FALLBACK_IMAGE = '/images/homepage/hero.jpg'

export default function GoodToKnowPostPage() {
  const { slug } = useParams()
  const { post, loading, error } = useBlogPosts({ slug })

  if (loading) {
    return (
      <div className="content-page content-state">
        <LoadingSpinner />
      </div>
    )
  }

  if (error || !post) {
    return (
      <div className="content-page content-state">
        <h1>Article not found</h1>
        <p className="mt-4">
          <Link to="/good-to-know">Back to Good to Know</Link>
        </p>
      </div>
    )
  }

  const heroImage = post.featured_image || FALLBACK_IMAGE

  return (
    <div className="content-page">
      <section
        className="content-post-hero"
        style={{ backgroundImage: post.aurora ? undefined : `url(${heroImage})` }}
      >
        {post.aurora && <div className="bcard-aurora" style={{ position: 'absolute', inset: 0 }}><span className="stars" /></div>}
        <div className="wrap content-post-hero-inner">
          <Link to="/good-to-know" className="content-post-back">
            ← Good to Know
          </Link>
          <h1 className="content-post-title">{post.title}</h1>
          {post.read_time && <div className="content-post-meta">{post.read_time}</div>}
        </div>
      </section>

      <section className="content-body">
        <div className="wrap">
          {post.excerpt && <p className="content-lead" style={{ color: 'inherit', maxWidth: '52ch', marginBottom: '24px' }}>{post.excerpt}</p>}
          <ContentProse html={post.body} />
        </div>
      </section>
    </div>
  )
}
