import { useEffect, useState } from 'react'
import { api } from '../api'

export default function useBlogPosts({ slug, featured = false } = {}) {
  const [posts, setPosts] = useState([])
  const [post, setPost] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    let cancelled = false
    setLoading(true)
    setError(null)

    const request = slug
      ? api.get(`/blog-posts/${slug}`)
      : api.get('/blog-posts', { params: featured ? { featured: 1 } : {} })

    request
      .then((res) => {
        if (cancelled) return
        if (slug) {
          setPost(res.data?.data ?? res.data)
        } else {
          const payload = res.data?.data ?? res.data
          setPosts(Array.isArray(payload) ? payload : payload?.data ?? [])
        }
      })
      .catch((err) => {
        if (!cancelled) setError(err.response?.status === 404 ? 'not_found' : 'error')
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [slug, featured])

  return { posts, post, loading, error }
}
