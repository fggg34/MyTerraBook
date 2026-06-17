import { useMemo } from 'react'
import { Check, Shield } from 'lucide-react'
import { getProtectionPresentation } from '../../data/requestToBookConfig'
import {
  PROTECTION_TIER_HOST_LABELS,
  catalogProtectionTiers,
} from '../../utils/hostCarPricingUtils'

export default function HostCarProtectionPlansPanel({
  priceTypes = [],
  offers,
  onChangeOffers,
  baseDailyFare,
}) {
  const tiers = useMemo(() => catalogProtectionTiers(priceTypes), [priceTypes])

  if (!tiers.length) {
    return (
      <section className="host-fare-section">
        <p className="host-icon-select__empty">Protection plans are not configured yet. Contact support if this persists.</p>
      </section>
    )
  }

  const offeredCount = tiers.filter((tier) => (
    tier.isStandard ? !!baseDailyFare : (tier.slug === 'plus' ? offers.plus : offers.max)
  )).length

  return (
    <section className="host-fare-section">
      <div className="host-protection-head">
        <div className="host-fare-head">
          <span className="host-fare-head-icon"><Shield size={18} /></span>
          <div className="host-fare-head-text">
            <h3>Protection plans</h3>
            <p>Choose which coverage tiers guests can pick at checkout. Standard is always included once you set a daily rate.</p>
          </div>
        </div>
        {offeredCount > 0 && (
          <span className="host-icon-select__count">{offeredCount} offered</span>
        )}
      </div>

      <div className="host-protection-plans">
        {tiers.map((tier, index) => {
          const labels = PROTECTION_TIER_HOST_LABELS[tier.slug] || {
            title: tier.priceType.name,
            note: '',
          }
          const presentation = getProtectionPresentation(tier.priceType, index)
          const isStandard = tier.isStandard
          const isOffered = isStandard
            ? !!baseDailyFare
            : (tier.slug === 'plus' ? offers.plus : offers.max)

          return (
            <div
              key={tier.slug}
              className={`host-protection-plan${isOffered ? ' is-offered' : ''}${isStandard ? ' is-standard' : ''}`}
            >
              <div className="host-protection-plan__head">
                <span className="host-protection-plan__icon">
                  {isOffered ? <Check size={16} strokeWidth={2.5} /> : <Shield size={16} strokeWidth={2} />}
                </span>
                <div className="host-protection-plan__text">
                  <span className="host-protection-plan__name">{labels.title}</span>
                  <span className="host-protection-plan__meta">
                    {tier.priceType.name}
                    {presentation.deposit ? ` · ${presentation.deposit}` : ''}
                  </span>
                  {labels.note && (
                    <span className="host-protection-plan__note">{labels.note}</span>
                  )}
                </div>
              </div>

              {isStandard ? (
                <span className="host-protection-plan__status">
                  {baseDailyFare ? 'Included' : 'Set daily rate first'}
                </span>
              ) : (
                <label className="host-protection-plan__toggle">
                  <input
                    type="checkbox"
                    checked={isOffered}
                    onChange={(e) => onChangeOffers({
                      ...offers,
                      [tier.slug === 'plus' ? 'plus' : 'max']: e.target.checked,
                    })}
                  />
                  <span>Offer to guests</span>
                </label>
              )}
            </div>
          )
        })}
      </div>
    </section>
  )
}
