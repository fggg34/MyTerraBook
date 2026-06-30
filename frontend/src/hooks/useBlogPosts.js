import { useEffect, useState } from 'react'
import { api } from '../api'
import {
  getBootstrappedBlogPost,
  getBootstrappedBlogPosts,
} from '../utils/siteBootstrap'
import {
  readBlogPostCache,
  readBlogPostsCache,
  writeBlogPostCache,
  writeBlogPostsCache,
} from '../utils/siteContentCache'
import { sortBlogPosts } from '../utils/sortBlogPosts'

function getInstantBlogPosts() {
  return sortBlogPosts(getBootstrappedBlogPosts() ?? readBlogPostsCache() ?? [])
}

function normalizeBlogPostList(posts, { featured = false } = {}) {
  const list = Array.isArray(posts) ? posts : []
  const filtered = featured ? list.filter((post) => post?.is_featured) : list
  return sortBlogPosts(filtered)
}

function getInstantBlogPost(slug) {
  if (!slug) return null
  return getBootstrappedBlogPost(slug) ?? readBlogPostCache(slug) ?? getInstantBlogPosts().find((post) => post?.slug === slug) ?? null
}

export default function useBlogPosts({ slug, featured = false } = {}) {
  const instantPosts = getInstantBlogPosts()
  const instantPost = slug ? getInstantBlogPost(slug) : null
  const instantList = featured
    ? normalizeBlogPostList(instantPosts, { featured: true })
    : instantPosts

  const [posts, setPosts] = useState(instantList)
  const [post, setPost] = useState(instantPost)
  const [loading, setLoading] = useState(slug ? !instantPost : instantList.length === 0)
  const [error, setError] = useState(null)

  useEffect(() => {
    let cancelled = false
    const cachedPost = slug ? getInstantBlogPost(slug) : null
    const cachedPosts = getInstantBlogPosts()
    const cachedList = normalizeBlogPostList(cachedPosts, { featured })

    if (slug) {
      if (cachedPost) {
        setPost(cachedPost)
        setLoading(false)
      } else {
        setLoading(true)
      }
    } else if (cachedList.length > 0) {
      setPosts(cachedList)
      setLoading(false)
    } else {
      setLoading(true)
    }
    setError(null)

    const request = slug
      ? api.get(`/blog-posts/${slug}`)
      : api.get('/blog-posts', { params: featured ? { featured: 1 } : {} })

    request
      .then((res) => {
        if (cancelled) return
        if (slug) {
          const data = res.data?.data ?? res.data
          setPost(data)
          if (data?.slug) writeBlogPostCache(data.slug, data)
        } else {
          const payload = res.data?.data ?? res.data
          const list = normalizeBlogPostList(
            Array.isArray(payload) ? payload : payload?.data ?? [],
            { featured },
          )
          setPosts(list)
          if (list.length) writeBlogPostsCache(list)
        }
      })
      .catch((err) => {
        if (!cancelled) {
          if ((slug && cachedPost) || (!slug && cachedList.length > 0)) {
            setError(null)
          } else {
            setError(err.response?.status === 404 ? 'not_found' : 'error')
          }
        }
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
