import { Link } from 'react-router-dom'
import useBlogBentoEffects from '../../hooks/useBlogBentoEffects'
import useHorizontalCarousel from '../../hooks/useHorizontalCarousel'
import useMediaQuery from '../../hooks/useMediaQuery'

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

function CarouselNav({ direction, disabled, onClick, label }) {
  return (
    <button
      className={`carousel-nav ${direction}`}
      type="button"
      aria-label={label}
      disabled={disabled}
      onClick={onClick}
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
        {direction === 'prev' ? <path d="M15 6l-6 6 6 6" /> : <path d="M9 6l6 6-6 6" />}
      </svg>
    </button>
  )
}

export default function BlogSection({ heading, subtitle, allLabel, allHref, posts = [] }) {
  const isMobile = useMediaQuery('(max-width: 768px)')
  const showCarousel = isMobile && posts.length > 0
  const showControls = showCarousel && posts.length > 1
  useBlogBentoEffects({ enabled: posts.length > 0, carousel: showCarousel })
  const { trackRef, scroll, atStart, atEnd } = useHorizontalCarousel({
    itemCount: posts.length,
    cardSelector: '.bcard',
    gap: 12,
    enabled: showCarousel,
  })

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

  const bentoClassName = [
    'bento',
    showCarousel ? 'bento--carousel' : '',
    showCarousel && posts.length === 1 ? 'bento--carousel-single' : '',
  ]
    .filter(Boolean)
    .join(' ')

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
        <div className={bentoClassName} id="bento" ref={showCarousel ? trackRef : undefined}>
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
        {showControls && (
          <div className="blog-carousel-controls product-carousel-controls">
            <CarouselNav direction="prev" label="Previous article" disabled={atStart} onClick={() => scroll(-1)} />
            <CarouselNav direction="next" label="Next article" disabled={atEnd} onClick={() => scroll(1)} />
          </div>
        )}
      </div>
    </section>
  )
}
