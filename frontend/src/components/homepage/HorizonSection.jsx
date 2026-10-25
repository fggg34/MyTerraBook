import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { destinationLandingPath } from '../../utils/destination'

const REGION_ORDER = ['Europe', 'Oceania', 'America']

export default function HorizonSection({ heading, subtitle, cards = [] }) {
  const price = useFormatPrice()
  const regions = useMemo(() => {
    const extra = cards
      .map((card) => card.region)
      .filter((region) => region && !REGION_ORDER.includes(region))
    return [...REGION_ORDER, ...new Set(extra)]
  }, [cards])
  const [region, setRegion] = useState(regions[0])

  useEffect(() => {
    if (!regions.includes(region)) setRegion(regions[0])
  }, [region, regions])

  const visible = cards.filter((card) => card.region === region)
  if (!cards.length) return null

  return (
    <section className="horizon" aria-labelledby="horizon-title">
      <div className="wrap">
        <div className="horizon-head">
          <div>
            <h2 id="horizon-title">{heading || 'Choose your next destination'}</h2>
            {subtitle ? <p className="sub">{subtitle}</p> : null}
          </div>
          <div className="horizon-tabs" role="tablist" aria-label="Destination regions">
            {regions.map((item) => (
              <button
                key={item}
                type="button"
                role="tab"
                className={`horizon-tab${item === region ? ' active' : ''}`}
                aria-selected={item === region}
                onClick={() => setRegion(item)}
              >
                {item}
              </button>
            ))}
          </div>
        </div>
        {visible.length ? (
          <div className="horizon-grid">
            {visible.map((card, index) => {
              const priceLabel = card.available && Number(card.fromPriceCents) > 0
                ? `from ${price.formatCents(card.fromPriceCents)}/day`
                : ''
              const body = (
                <>
                  {card.image ? (
                    <img src={card.image} alt="" />
                  ) : (
                    <span className="horizon-placeholder" aria-hidden="true" />
                  )}
                  {priceLabel ? <span className="horizon-price">{priceLabel}</span> : null}
                  <span className="horizon-name">{card.name || card.countryCode}</span>
                </>
              )
              if (!card.available) {
                return (
                  <div key={`${card.countryCode}-${index}`} className="horizon-card is-placeholder">
                    {body}
                  </div>
                )
              }
              return (
                <Link key={`${card.countryCode}-${index}`} className="horizon-card" to={destinationLandingPath(card)}>
                  {body}
                </Link>
              )
            })}
          </div>
        ) : (
          <p className="horizon-empty">More destinations in {region} are on the way.</p>
        )}
      </div>
    </section>
  )
}
