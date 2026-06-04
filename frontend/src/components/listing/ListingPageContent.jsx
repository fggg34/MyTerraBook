import { Link } from 'react-router-dom'
import ProductCard from '../homepage/ProductCard'
import { mapCarToResultCard } from '../../utils/mapCarToResultCard'
import ListingGallery from './ListingGallery'
import ListingTabPanels from './ListingTabPanels'

export default function ListingPageContent({ listing, related, searchQuery, typeConfig }) {
  const detailBase =
    listing.listingType === 'car' ? '/cars' : listing.listingType === 'guesthouse' ? '/guesthouses' : '/campervans'
  const relatedCards = related.map((car) => {
    const card = mapCarToResultCard(
      { ...car, categoryName: car.category_name },
      {
        searchQuery,
        config: {
          defaultSeats: 5,
          defaultSleeps: listing.listingType === 'guesthouse' ? 0 : 2,
          defaultBags: 2,
        },
        categoryName: car.category_name,
      },
    )
    return { ...card, href: `${detailBase}/${car.id}${searchQuery ? `?${searchQuery}` : ''}` }
  })

  return (
    <>
      <div className="subbar">
        <div className="wrap">
          <Link className="crumb" to={typeConfig.archiveRoute}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="m15 18-6-6 6-6" />
            </svg>
            {typeConfig.archiveLabel}
          </Link>
          <div className="subactions">
            <button className="sa-btn" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
                <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7" />
                <path d="M12 3v13M8 7l4-4 4 4" />
              </svg>
              Share
            </button>
          </div>
        </div>
      </div>

      <ListingGallery images={listing.images} photoCount={listing.photoCount} />

      <div className="layout">
        <div className="wrap">
          <ListingTabPanels listing={listing} />
        </div>
      </div>

      <section className="reviews-sec" id="reviews">
        <div className="wrap">
          <h2>{typeConfig.reviewsTitle}</h2>
          <div className="rev-summary">
            <div className="rev-score">
              <svg className="rs-star" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
              </svg>
              <div className="rs-meta">
                <span className="rs-num">{listing.rating.score}</span>
                <span className="rs-excellent">{listing.rating.label}</span>
                <a href="#reviews">{listing.rating.reviewLinkLabel}</a>
              </div>
            </div>
            <div className="rev-overall">
              <div className="ro-label">Overall rating</div>
              <div className="ro-bars">
                {[5, 4, 3, 2, 1].map((n) => (
                  <div key={n} className="ro-bar">
                    <span className="ro-n">{n}</span>
                    <span className="ro-track">
                      <span className="ro-fill" data-w={n === 5 ? 100 : 0} />
                    </span>
                  </div>
                ))}
              </div>
            </div>
            <div className="rev-cats">
              {listing.reviewCategories.map((rc) => (
                <div key={rc.label} className="rev-cat">
                  <span className="rc-label">{rc.label}</span>
                  <span className="rc-val">
                    {rc.value}{' '}
                    <span className="rc-bar">
                      <i style={{ width: rc.width }} />
                    </span>
                  </span>
                </div>
              ))}
            </div>
          </div>
          <div className="rev-grid">
            <div className="rev-feature">
              <img src={listing.images[0]?.url || '/images/homepage/cardcamper.jpg'} alt="" />
            </div>
            {listing.reviews.map((rev) => (
              <div key={rev.name + rev.date} className="rcard-rev">
                <div className="rcard-head">
                  <div className="rcard-av">{rev.initial}</div>
                  <div className="rcard-id">
                    <span className="rcard-name">{rev.name}</span>
                    <span className="rcard-sub">
                      {rev.date}{' '}
                      <span className="rc-star">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 18.9 6.1 21.2l1.3-6.6L2.5 9.9l6.6-.8L12 2.5z" />
                        </svg>
                        {rev.score}
                      </span>
                    </span>
                  </div>
                </div>
                <p className={`rcard-text ${rev.clamp ? 'clamp' : ''}`}>{rev.text}</p>
                {rev.clamp ? (
                  <button className="rcard-more" type="button">
                    Show more
                  </button>
                ) : null}
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="gphotos">
        <div className="wrap">
          <h2>{typeConfig.guestPhotosTitle}</h2>
        </div>
        <div className="gp-marquee" id="gpMarquee">
          <div className="gp-track">
            {[...listing.guestPhotoUrls, ...listing.guestPhotoUrls].map((url, i) => (
              <div key={`${url}-${i}`} className="gp-tile" aria-hidden={i >= listing.guestPhotoUrls.length ? true : undefined}>
                <img src={url} alt="" />
              </div>
            ))}
          </div>
        </div>
      </section>

      {relatedCards.length > 0 && (
        <section className="similar">
          <div className="wrap">
            <div className="similar-head">
              <h2>{typeConfig.similarTitle}</h2>
              <div className="sim-nav">
                <button className="sim-arrow" id="simPrev" type="button" aria-label="Previous">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m15 18-6-6 6-6" />
                  </svg>
                </button>
                <button className="sim-arrow" id="simNext" type="button" aria-label="Next">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                    <path d="m9 18 6-6-6-6" />
                  </svg>
                </button>
              </div>
            </div>
            <div className="sim-track" id="simTrack">
              {relatedCards.map((card) => (
                <ProductCard key={card.id} {...card} />
              ))}
            </div>
          </div>
        </section>
      )}

      <section className="faqs">
        <div className="wrap">
          <div className="faq-grid">
            <div className="faq-head">
              <h2>Frequently asked questions</h2>
              <p>{typeConfig.faqLead}</p>
              <button className="faq-contact" type="button">
                Message the host
              </button>
            </div>
            <div className="faq-list">
              {listing.faqs.map((faq) => (
                <div key={faq.q} className="faq-item">
                  <button className="faq-q" type="button">
                    {faq.q}
                  </button>
                  <div className="faq-a">
                    <p>{faq.a}</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <div className="bp-overlay" id="bpOverlay" aria-hidden="true">
        <div className="bp-modal" role="dialog" aria-modal="true" aria-labelledby="bpTitle">
          <div className="bp-head">
            <div className="bp-eyebrow">How it works</div>
            <h3 id="bpTitle">{typeConfig.bookingModalTitle}</h3>
            <p>{typeConfig.bookingModalLead}</p>
            <button className="bp-close" id="bpClose" type="button" aria-label="Close">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M6 6l12 12M18 6 6 18" />
              </svg>
            </button>
          </div>
          <div className="bp-steps">
            {typeConfig.bookingSteps.map((step, i) => (
              <div key={step.title} className="bp-step">
                <span className="bp-num">{i + 1}</span>
                <div className="bp-tx">
                  <h4>{step.title}</h4>
                  <p>{step.text}</p>
                  {step.tag ? <span className="bp-tag">{step.tag}</span> : null}
                </div>
              </div>
            ))}
          </div>
          <div className="bp-foot">
            <button className="bp-cta" id="bpCta" type="button">
              {typeConfig.bookCta}
            </button>
            <span className="bp-note">No charge until the booking is confirmed.</span>
          </div>
        </div>
      </div>
    </>
  )
}
