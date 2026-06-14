import { Link } from 'react-router-dom'
import useBlogBentoEffects from '../../hooks/useBlogBentoEffects'

function hasCardMedia(post) {
  return post.aurora || post.image
}

function BlogCardLink({ post, children }) {
  if (post.slug) {
    return (
      <Link
        to={`/good-to-know/${post.slug}`}
        className={`bcard ${post.featured ? 'featured' : ''} ${hasCardMedia(post) ? '' : 'bcard--text'}`}
      >
        {children}
      </Link>
    )
  }

  return (
    <article className={`bcard ${post.featured ? 'featured' : ''} ${hasCardMedia(post) ? '' : 'bcard--text'}`}>
      {children}
    </article>
  )
}

export default function BlogSection({ heading, subtitle, allLabel, allHref, posts = [] }) {
  useBlogBentoEffects()

  const allLink = allHref?.startsWith('/') ? (
    <Link className="section-all" to={allHref}>
      {allLabel}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </Link>
  ) : (
    <a className="section-all" href={allHref || '#'}>
      {allLabel}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        <path d="M5 12h14M13 6l6 6-6 6" />
      </svg>
    </a>
  )

  return (
    <section className="blog" id="discover">
      <div className="wrap">
        <div className="blog-head">
          <div>
            {heading && <h2>{heading}</h2>}
            {subtitle && <p className="blog-sub">{subtitle}</p>}
          </div>
          {allLabel && allLink}
        </div>
        <div className="bento" id="bento">
          {posts.map((post) => {
            const showMedia = hasCardMedia(post)

            return (
            <BlogCardLink key={post.slug || post.title} post={post}>
              {showMedia && (
                post.aurora ? (
                  <div className="bcard-aurora">
                    <span className="stars" />
                  </div>
                ) : (
                  <div className="bcard-media">
                    <img src={post.image} alt={post.imageAlt || post.title} />
                  </div>
                )
              )}
              <span className="bcard-glow" />
              <span className="bcard-read" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M7 17 17 7M9 7h8v8" />
                </svg>
              </span>
              <div className="bcard-body">
                {!showMedia && post.kicker && (
                  <span className="bcard-kicker">
                    <span className="k-dot" aria-hidden="true" />
                    {post.kicker}
                  </span>
                )}
                <h3>{post.title}</h3>
                {post.description && <p className="bcard-desc">{post.description}</p>}
                {post.meta && (
                  <div className="bcard-meta">
                    {post.meta}
                    {post.metaExtra && (
                      <>
                        <span className="m-dot" />
                        {post.metaExtra}
                      </>
                    )}
                  </div>
                )}
              </div>
            </BlogCardLink>
            )
          })}
        </div>
      </div>
    </section>
  )
}
