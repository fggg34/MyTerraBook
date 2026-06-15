import { useCallback, useEffect, useState } from 'react'

export default function ListingGallery({ images, photoCount }) {
  const slots = [0, 1, 2, 3, 4]
  const main = images[0]
  const rest = images.slice(1)
  const hasImages = images.length > 0

  const [open, setOpen] = useState(false)
  const [active, setActive] = useState(0)

  const openAt = (index) => {
    if (!hasImages) return
    setActive(Math.min(Math.max(index, 0), images.length - 1))
    setOpen(true)
  }

  const close = useCallback(() => setOpen(false), [])
  const goPrev = useCallback(
    () => setActive((i) => (i - 1 + images.length) % images.length),
    [images.length],
  )
  const goNext = useCallback(
    () => setActive((i) => (i + 1) % images.length),
    [images.length],
  )

  useEffect(() => {
    if (!open) return undefined

    const prevOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'

    const onKeyDown = (event) => {
      if (event.key === 'Escape') close()
      else if (event.key === 'ArrowLeft') goPrev()
      else if (event.key === 'ArrowRight') goNext()
    }
    document.addEventListener('keydown', onKeyDown)

    return () => {
      document.body.style.overflow = prevOverflow
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [open, close, goPrev, goNext])

  const activeImage = images[active]

  return (
    <div className="gallery-wrap">
      <div className="wrap">
        <div className="gallery">
          <div className="gslot main">
            {main ? (
              <img src={main.url} alt={main.alt} onClick={() => openAt(0)} role="button" />
            ) : (
              <div className="listing-ph" />
            )}
          </div>
          {slots.slice(1, 4).map((i) => (
            <div key={i} className="gslot">
              {rest[i - 1] ? (
                <img src={rest[i - 1].url} alt={rest[i - 1].alt} onClick={() => openAt(i)} role="button" />
              ) : (
                <div className="listing-ph" />
              )}
            </div>
          ))}
          <div className="gslot">
            {rest[3] ? (
              <img src={rest[3].url} alt={rest[3].alt} onClick={() => openAt(4)} role="button" />
            ) : (
              <div className="listing-ph" />
            )}
            {hasImages ? (
              <button className="allphotos" type="button" onClick={() => openAt(0)}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="3" y="3" width="7" height="7" rx="1.5" />
                  <rect x="14" y="3" width="7" height="7" rx="1.5" />
                  <rect x="3" y="14" width="7" height="7" rx="1.5" />
                  <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
                View all {photoCount} photos
              </button>
            ) : null}
          </div>
        </div>
      </div>

      {open && activeImage ? (
        <div
          className="glx-overlay open"
          role="dialog"
          aria-modal="true"
          aria-label="Photo gallery"
          onClick={close}
        >
          <div className="glx-modal" onClick={(e) => e.stopPropagation()}>
            <div className="glx-head">
              <span className="glx-count">
                {active + 1} / {images.length}
              </span>
              <button className="glx-close" type="button" aria-label="Close gallery" onClick={close}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M6 6l12 12M18 6 6 18" />
                </svg>
              </button>
            </div>

            <div className="glx-stage">
              {images.length > 1 ? (
                <button className="glx-nav glx-prev" type="button" aria-label="Previous photo" onClick={goPrev}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m15 18-6-6 6-6" />
                  </svg>
                </button>
              ) : null}
              <img className="glx-image" src={activeImage.url} alt={activeImage.alt} />
              {images.length > 1 ? (
                <button className="glx-nav glx-next" type="button" aria-label="Next photo" onClick={goNext}>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m9 18 6-6-6-6" />
                  </svg>
                </button>
              ) : null}
            </div>

            {images.length > 1 ? (
              <div className="glx-thumbs">
                {images.map((image, i) => (
                  <button
                    key={`${image.url}-${i}`}
                    type="button"
                    className={`glx-thumb${i === active ? ' on' : ''}`}
                    aria-label={`View photo ${i + 1}`}
                    aria-current={i === active}
                    onClick={() => setActive(i)}
                  >
                    <img src={image.url} alt={image.alt} />
                  </button>
                ))}
              </div>
            ) : null}
          </div>
        </div>
      ) : null}
    </div>
  )
}
