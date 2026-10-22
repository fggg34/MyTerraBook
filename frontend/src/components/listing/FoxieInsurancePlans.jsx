import { formatCarenPrice } from '../../utils/carenPricing'

export function defaultFoxieInsuranceId(plans = []) {
  const included = plans.find((plan) => Number(plan.cost_cents) === 0)
  const plan = included || plans[0]
  return plan?.id != null ? String(plan.id) : ''
}

export function foxieInsurancePrice(plan, formatter) {
  if (!plan || Number(plan.cost_cents) === 0) return 'Included'
  const amount = formatCarenPrice(plan.cost, plan.currency || 'ISK', formatter)
  return plan.per_day ? `${amount} / day` : amount
}

export default function FoxieInsurancePlans({ plans = [], selectedId, onSelect, formatter, hideLegend = false }) {
  if (!plans.length) return null

  return (
    <fieldset className="foxie-insurance">
      <legend className={hideLegend ? 'foxie-insurance__legend--hidden' : undefined}>Insurance</legend>
      <div className="foxie-insurance__list">
        {plans.map((plan) => {
          const selected = String(plan.id) === String(selectedId)
          return (
            <label key={plan.id} className={`foxie-insurance__plan${selected ? ' is-selected' : ''}`}>
              <input
                type="radio"
                name="foxie-insurance"
                value={String(plan.id)}
                checked={selected}
                onChange={() => onSelect?.(String(plan.id))}
              />
              <span className="foxie-insurance__name">{plan.name}</span>
              <span className="foxie-insurance__price">{foxieInsurancePrice(plan, formatter)}</span>
              {plan.description ? <span className="foxie-insurance__desc">{plan.description}</span> : null}
            </label>
          )
        })}
      </div>
    </fieldset>
  )
}
