function toSortOrder(post) {
  const value = post?.sort_order
  return Number.isFinite(value) ? value : Number.MAX_SAFE_INTEGER
}

function toPublishedTime(post) {
  const value = post?.published_at
  if (!value) return 0
  const time = new Date(value).getTime()
  return Number.isFinite(time) ? time : 0
}

/**
 * Match Laravel admin / API ordering: featured posts first, then sort_order, then newest publish date.
 */
export function sortBlogPosts(posts = []) {
  if (!Array.isArray(posts) || posts.length < 2) return Array.isArray(posts) ? [...posts] : []

  return [...posts].sort((a, b) => {
    const featuredDiff = Number(Boolean(b?.is_featured)) - Number(Boolean(a?.is_featured))
    if (featuredDiff !== 0) return featuredDiff

    const orderDiff = toSortOrder(a) - toSortOrder(b)
    if (orderDiff !== 0) return orderDiff

    return toPublishedTime(b) - toPublishedTime(a)
  })
}
