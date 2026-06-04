export default function ListingGallery({ images, photoCount }) {
  const slots = [0, 1, 2, 3, 4]
  const main = images[0]
  const rest = images.slice(1)

  return (
    <div className="gallery-wrap">
      <div className="wrap">
        <div className="gallery">
          <div className="gslot main">
            {main ? (
              <img src={main.url} alt={main.alt} />
            ) : (
              <div className="listing-ph" />
            )}
          </div>
          {slots.slice(1, 4).map((i) => (
            <div key={i} className="gslot">
              {rest[i - 1] ? (
                <img src={rest[i - 1].url} alt={rest[i - 1].alt} />
              ) : (
                <div className="listing-ph" />
              )}
            </div>
          ))}
          <div className="gslot">
            {rest[3] ? <img src={rest[3].url} alt={rest[3].alt} /> : <div className="listing-ph" />}
            <button className="allphotos" type="button">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                <rect x="14" y="14" width="7" height="7" rx="1.5" />
              </svg>
              View all {photoCount} photos
            </button>
          </div>
        </div>
      </div>
    </div>
  )
}
