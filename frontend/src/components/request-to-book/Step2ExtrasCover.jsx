import { ChevronLeft } from 'lucide-react'
import ProtectionPlanGrid from './ProtectionPlanGrid'
import AddonRow from './AddonRow'

export default function Step2ExtrasCover({
  config,
  bookingType,
  item,
  form,
  updateForm,
  nights,
  toggleAddon,
  errors = {},
  onNext,
  onBack,
}) {
  const addons = item?.rental_options || []
  const amenities = (item?.amenities || []).flatMap((g) => g.items || (g.name ? [g] : []))

  return (
    <div data-step="2">
      <h2 className="panel-title">{config.step2.title}</h2>
      <p className="panel-sub">{config.step2.subtitle}</p>

      {config.step2.showProtection && item?.price_types?.length > 0 && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">1</span>
            <h3>Protection plan</h3>
            <span className="bh-sub">{config.step2.protectionSubtitle}</span>
          </div>
          <ProtectionPlanGrid
            priceTypes={item.price_types}
            selectedId={form.price_type_id}
            onSelect={(id) => updateForm({ price_type_id: id })}
          />
          {errors.price_type_id && (
            <p className="hint" style={{ color: 'var(--rtb-red)', marginTop: 10 }}>
              Choose a protection plan to continue
            </p>
          )}
        </div>
      )}

      {config.step2.showIncludedNote && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">1</span>
            <h3>What&apos;s included</h3>
          </div>
          <p style={{ margin: 0, fontSize: 14, color: 'var(--rtb-slate)', marginBottom: amenities.length ? 12 : 0 }}>
            Your stay includes linens, Wi-Fi, and standard amenities. Cleaning fee and taxes are shown in the summary.
          </p>
          {amenities.length > 0 && (
            <ul style={{ margin: 0, paddingLeft: 18, fontSize: 14, color: 'var(--rtb-slate)' }}>
              {amenities.slice(0, 8).map((a) => (
                <li key={a.id || a.name}>{a.name}</li>
              ))}
            </ul>
          )}
        </div>
      )}

      {config.step2.showAddons && addons.length > 0 && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">2</span>
            <h3>Host add-ons</h3>
            <span className="bh-sub">{config.step2.addonsSubtitle}</span>
          </div>
          <div className="addon-list">
            {addons.map((opt) => (
              <AddonRow
                key={opt.id}
                option={opt}
                selected={form.rental_option_ids.includes(Number(opt.id))}
                nights={nights}
                onToggle={toggleAddon}
              />
            ))}
          </div>
        </div>
      )}

      {config.step2.showAddons && !addons.length && bookingType !== 'guesthouse' && (
        <div className="block">
          <p style={{ margin: 0, fontSize: 14, color: 'var(--rtb-slate)' }}>No add-ons available for this listing.</p>
        </div>
      )}

      <div className="step-nav">
        <button type="button" className="btn-back" onClick={onBack}>
          <ChevronLeft aria-hidden />
          Back
        </button>
        <button type="button" className="btn-next" onClick={onNext}>
          Continue to your details
        </button>
      </div>
    </div>
  )
}
