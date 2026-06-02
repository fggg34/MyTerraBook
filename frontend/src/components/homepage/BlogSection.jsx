import useBlogBentoEffects from '../../hooks/useBlogBentoEffects'

export default function BlogSection({ heading, subtitle, allLabel, allHref, posts = [] }) {
  useBlogBentoEffects()

  return (
    <section className="blog" id="discover">
      <div className="wrap">
        <div className="blog-head">
          <div>
            {heading && <h2>{heading}</h2>}
            {subtitle && <p className="blog-sub">{subtitle}</p>}
          </div>
          {allLabel && (
            <a className="blog-all" href={allHref || '#'}>
              {allLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </a>
          )}
        </div>
        <div className="bento" id="bento">
          {posts.map((post) => (
            <article key={post.title} className={`bcard ${post.featured ? 'featured' : ''}`}>
              {post.aurora ? (
                <div className="bcard-aurora">
                  <span className="stars" />
                </div>
              ) : (
                <div className="bcard-media">
                  <img src={post.image} alt={post.imageAlt || post.title} />
                </div>
              )}
              <span className="bcard-glow" />
              <button className="bcard-read" type="button" aria-label="Read article">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M7 17 17 7M9 7h8v8" />
                </svg>
              </button>
              <div className="bcard-body">
                {post.kicker && (
                  <span className="bcard-kicker">
                    <span className="k-dot" />
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
            </article>
          ))}
        </div>
      </div>
    </section>
  )
}
