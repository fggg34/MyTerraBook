import GoodToKnowSection from '../components/content/GoodToKnowSection'
import LoadingSpinner from '../components/ui/LoadingSpinner'
import useBlogPosts from '../hooks/useBlogPosts'
import '../styles/content-pages.css'

export default function GoodToKnowPage() {
  const { posts, loading, error } = useBlogPosts()

  return (
    <div className="content-page gtk-page">
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

      {!loading && !error && posts.length === 0 && (
        <div className="content-state">
          <p>No articles published yet. Check back soon.</p>
        </div>
      )}

      {!loading && !error && posts.length > 0 && (
        <GoodToKnowSection posts={posts} />
      )}
    </div>
  )
}
