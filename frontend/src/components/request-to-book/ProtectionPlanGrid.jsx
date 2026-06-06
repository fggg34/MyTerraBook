import { Check } from 'lucide-react'
import { formatCurrencyFromCents } from '../../utils/format'
import { getProtectionPresentation } from '../../data/requestToBookConfig'

export default function ProtectionPlanGrid({ priceTypes, selectedId, onSelect }) {
  if (!priceTypes?.length) return null
  return (
    <div className="plan-grid" role="radiogroup" aria-label="Protection plan">
      {priceTypes.map((pt, idx) => {
        const sel = String(pt.id) === String(selectedId)
        const pres = getProtectionPresentation(pt, idx)
        const dailyCents = pt.from_price_per_day_cents || 0
        const daily = dailyCents
          ? formatCurrencyFromCents(dailyCents)
          : pt.from_price_per_day || '€0'
        const deposit = pt.attribute_value_per_day || pres.deposit
        const priceLine = pres.included || dailyCents === 0
          ? <><b>€0</b> / included</>
          : <><b>{daily}</b> / day</>

        return (
          <div
            key={pt.id}
            role="radio"
            aria-checked={sel}
            tabIndex={0}
            className={`plan${sel ? ' sel' : ''}`}
            onClick={(e) => {
              e.preventDefault()
              onSelect(String(pt.id))
            }}
            onKeyDown={(e) => {
              if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault()
                onSelect(String(pt.id))
              }
            }}
          >
            {pres.mostPopular && <span className="ptag">Most popular</span>}
            <span className="pcheck"><Check aria-hidden /></span>
            <span className="pname">{pt.name}</span>
            <span className="pprice">{priceLine}</span>
            <span className="pdep">{deposit}</span>
            <ul>
              {pres.features.map((feature) => (
                <li key={feature}>
                  <Check aria-hidden />
                  {feature}
                </li>
              ))}
            </ul>
          </div>
        )
      })}
    </div>
  )
}
