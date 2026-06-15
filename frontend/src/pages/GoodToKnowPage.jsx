import GoodToKnowSection from '../components/content/GoodToKnowSection'
import PageHead from '../components/seo/PageHead'
import useBlogPosts from '../hooks/useBlogPosts'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/content-pages.css'

export default function GoodToKnowPage() {
  const { posts, loading, error } = useBlogPosts()
  const seo = usePageSeo('good-to-know')

  return (
    <div className="content-page gtk-page">
      <PageHead {...seo} />
      {error && !loading && (
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
