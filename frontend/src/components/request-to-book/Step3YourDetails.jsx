import { ChevronLeft, Mail } from 'lucide-react'
import CountrySelect from '../forms/CountrySelect'
import PhoneField from '../forms/PhoneField'
import { useShopConfig } from '../../context/ShopConfigContext'

export default function Step3YourDetails({
  config,
  form,
  updateForm,
  item,
  errors,
  onNext,
  onBack,
  customFields = [],
}) {
  const { prepayPercent } = useShopConfig()
  const hostInitial = item?.name?.charAt(0)?.toUpperCase() || 'H'
  const showSuperhost = item?.rating && Number(item.rating) >= 4.8

  return (
    <div data-step="3">
      <h2 className="panel-title">{config.step3.title}</h2>
      <p className="panel-sub">{config.step3.subtitle}</p>

      <div className="approve-banner">
        <span className="ab-av">{hostInitial}</span>
        <span className="ab-tx">
          <h4>
            Your host reviews this request
            {showSuperhost && <span className="ab-badge">★ Superhost</span>}
          </h4>
          <p>
            Nothing is charged today. Once your host approves, we take a <b>{prepayPercent}% prepayment</b> to hold the booking, this is non-refundable. The balance is due on pick-up.
          </p>
        </span>
      </div>

      <div className="block">
        <div className="block-head">
          <span className="bnum">1</span>
          <h3>{config.step3.showLicence ? 'Main driver' : 'Guest details'}</h3>
        </div>
        <div className="frow">
          <div className="field">
            <label>Full name <span className="req">*</span></label>
            <input
              className="inp"
              placeholder={config.step3.showLicence ? 'As shown on your licence' : 'As shown on your ID'}
              value={form.customer_name}
              onChange={(e) => updateForm({ customer_name: e.target.value })}
            />
            {errors.customer_name && <span className="hint" style={{ color: 'var(--rtb-red)' }}>{errors.customer_name}</span>}
          </div>
          {config.step3.showCountry && (
            <div className="field">
              <label>Country of residence <span className="req">*</span></label>
              <CountrySelect
                className="sel"
                includeOther={false}
                value={form.customer_country}
                onChange={(e) => updateForm({ customer_country: e.target.value })}
                placeholder="Select country"
              />
            </div>
          )}
        </div>
        <div className="frow">
          <div className="field full">
            <label>Email <span className="req">*</span></label>
            <div className="control ic">
              <Mail className="lead" aria-hidden />
              <input
                className="inp"
                type="email"
                placeholder="you@email.com"
                value={form.customer_email}
                onChange={(e) => updateForm({ customer_email: e.target.value })}
              />
            </div>
          </div>
          <div className="field full">
            <PhoneField
              id="rtb-customer-phone"
              label="Phone number"
              variant="rtb"
              required
              requiredMarkClassName="req"
              value={form.customer_phone}
              onChange={(customer_phone) => updateForm({ customer_phone })}
              placeholder="123 4567"
            />
            {errors.customer_phone && <span className="hint" style={{ color: 'var(--rtb-red)' }}>{errors.customer_phone}</span>}
          </div>
        </div>
        {config.step3.showDob && (
          <div className="frow tri">
            <div className="field">
              <label>Date of birth <span className="req">*</span></label>
              <select className="sel" value={form.dobYear} onChange={(e) => updateForm({ dobYear: e.target.value })}>
                <option value="">Year</option>
                {Array.from({ length: 50 }, (_, i) => 2005 - i).map((y) => (
                  <option key={y} value={y}>{y}</option>
                ))}
              </select>
            </div>
            <div className="field">
              <label>&nbsp;</label>
              <select className="sel" value={form.dobMonth} onChange={(e) => updateForm({ dobMonth: e.target.value })}>
                <option value="">Month</option>
                {Array.from({ length: 12 }, (_, i) => String(i + 1).padStart(2, '0')).map((m) => (
                  <option key={m} value={m}>{m}</option>
                ))}
              </select>
            </div>
            <div className="field">
              <label>&nbsp;</label>
              <select className="sel" value={form.dobDay} onChange={(e) => updateForm({ dobDay: e.target.value })}>
                <option value="">Day</option>
                {Array.from({ length: 31 }, (_, i) => String(i + 1).padStart(2, '0')).map((d) => (
                  <option key={d} value={d}>{d}</option>
                ))}
              </select>
            </div>
          </div>
        )}
      </div>

      {config.step3.showLicence && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">2</span>
            <h3>Driving licence</h3>
            <span className="bh-sub">{config.step3.licenceSubtitle}</span>
          </div>
          <div className="frow">
            <div className="field">
              <label>Licence number <span className="req">*</span></label>
              <input
                className="inp"
                placeholder="Number on your licence"
                value={form.licenceNumber}
                onChange={(e) => updateForm({ licenceNumber: e.target.value })}
              />
            </div>
            <div className="field">
              <label>Issuing country <span className="req">*</span></label>
              <CountrySelect
                className="sel"
                value={form.licenceCountry}
                onChange={(e) => updateForm({ licenceCountry: e.target.value })}
                placeholder="Select country"
              />
            </div>
          </div>
          <div className="frow">
            <div className="field full">
              <label>
                Anything your host should know? <span className="hint">(optional)</span>
              </label>
              <input
                className="inp"
                placeholder="Travelling with a dog, late flight, additional driver…"
                value={form.notes}
                onChange={(e) => updateForm({ notes: e.target.value })}
              />
            </div>
          </div>
        </div>
      )}

      {config.step3.showSpecialRequests && (
        <div className="block">
          <div className="field full">
            <label>Special requests <span className="hint">(optional)</span></label>
            <textarea
              className="inp"
              rows={3}
              maxLength={1000}
              placeholder="Late arrival, dietary needs, accessibility requirements…"
              value={form.special_requests}
              onChange={(e) => updateForm({ special_requests: e.target.value })}
            />
          </div>
        </div>
      )}

      {customFields.length > 0 && (
        <div className="block">
          <div className="block-head">
            <span className="bnum">{config.step3.showLicence ? '3' : '2'}</span>
            <h3>Additional information</h3>
          </div>
          {customFields.map((field) => (
            <div key={field.field_key} className="field full" style={{ marginBottom: 12 }}>
              <label>
                {field.label}
                {field.is_required ? <span className="req"> *</span> : null}
              </label>
              {field.type === 'select' ? (
                <select
                  className="sel"
                  value={form.custom_field_values?.[field.field_key] || ''}
                  onChange={(e) =>
                    updateForm({
                      custom_field_values: {
                        ...form.custom_field_values,
                        [field.field_key]: e.target.value,
                      },
                    })
                  }
                >
                  <option value="">Select…</option>
                  {(field.select_options || []).map((opt) => (
                    <option key={opt} value={opt}>{opt}</option>
                  ))}
                </select>
              ) : (
                <input
                  className="inp"
                  type={field.is_email ? 'email' : 'text'}
                  value={form.custom_field_values?.[field.field_key] || ''}
                  onChange={(e) =>
                    updateForm({
                      custom_field_values: {
                        ...form.custom_field_values,
                        [field.field_key]: e.target.value,
                      },
                    })
                  }
                />
              )}
              {errors[`custom_${field.field_key}`] && (
                <span className="hint" style={{ color: 'var(--rtb-red)' }}>{errors[`custom_${field.field_key}`]}</span>
              )}
            </div>
          ))}
        </div>
      )}

      <div className="step-nav">
        <button type="button" className="btn-back" onClick={onBack}>
          <ChevronLeft aria-hidden />
          Back
        </button>
        <button type="button" className="btn-next" onClick={onNext}>
          Continue to payment
        </button>
      </div>
    </div>
  )
}
