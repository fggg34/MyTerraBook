import { Check, ChevronLeft, Clock, CreditCard, Lock } from 'lucide-react'
import { Link } from 'react-router-dom'
import CountrySelect from '../forms/CountrySelect'
import { PREPAY_PERCENT } from '../../data/requestToBookConfig'
import { groupCardNumber, formatCardExpiry } from '../../utils/requestToBookUtils'
import LoadingSpinner from '../ui/LoadingSpinner'

const PAY_NOTES = {
  card: (
    <>
      <b>{PREPAY_PERCENT}% prepayment on approval.</b> Once your host approves, we charge {PREPAY_PERCENT}% to hold the booking. This prepayment is non-refundable. The remaining balance is paid on pick-up.
    </>
  ),
  instal: (
    <>
      <b>Pay in 3.</b> The {PREPAY_PERCENT}% prepayment is taken after approval; the rest can be split into interest-free instalments, with the final balance due on pick-up.
    </>
  ),
  paypal: (
    <>
      <b>PayPal.</b> You&apos;ll pay the {PREPAY_PERCENT}% prepayment via PayPal after approval. The balance is settled on pick-up.
    </>
  ),
}

function TermsLabel({ isGuesthouse }) {
  if (isGuesthouse) {
    return (
      <>
        I agree to the <Link to="/terms">Stay Terms</Link>, <Link to="/terms">Cancellation Policy</Link> and confirm the guest count is accurate.
      </>
    )
  }
  return (
    <>
      I agree to the <Link to="/terms">Rental Terms</Link>, <Link to="/terms">Cancellation Policy</Link> and confirm all drivers are 25+ with a licence held over 2 years.
    </>
  )
}

export default function Step4Payment({
  config,
  form,
  updateForm,
  errors,
  saving,
  onSubmit,
  onBack,
}) {
  const note = PAY_NOTES[form.paymentMethod] || PAY_NOTES.card

  return (
    <div data-step="4">
      <h2 className="panel-title">{config.step4.title}</h2>
      <p className="panel-sub">{config.step4.subtitle}</p>

      <div className="block">
        <div className="block-head">
          <span className="bnum">1</span>
          <h3>Payment method</h3>
        </div>
        <div className="pay-methods">
          {[
            { id: 'card', label: 'Card', Icon: CreditCard },
            { id: 'instal', label: 'Pay in 3', Icon: CreditCard },
            { id: 'paypal', label: 'PayPal', Icon: CreditCard },
          ].map(({ id, label, Icon }) => (
            <button
              key={id}
              type="button"
              className={`pm${form.paymentMethod === id ? ' sel' : ''}`}
              onClick={() => updateForm({ paymentMethod: id })}
            >
              <Icon aria-hidden />
              {label}
            </button>
          ))}
        </div>

        <div>
          <div className="card-visual">
            <div className="cv-brand">VISA</div>
            <div className="cv-chip" />
            <div className="cv-num">{form.cardNumber || '•••• •••• •••• ••••'}</div>
            <div className="cv-foot">
              <span>
                <span className="cl">Card holder</span>
                <br />
                <span className="cvv">{(form.cardName || 'YOUR NAME').toUpperCase()}</span>
              </span>
              <span>
                <span className="cl">Expires</span>
                <br />
                <span className="cvv">{form.cardExpiry || 'MM / YY'}</span>
              </span>
            </div>
          </div>
          <div className="frow" style={{ marginTop: 20 }}>
            <div className="field full">
              <label>Card number <span className="req">*</span></label>
              <div className="control ic">
                <CreditCard className="lead" aria-hidden />
                <input
                  className="inp"
                  inputMode="numeric"
                  placeholder="1234 5678 9012 3456"
                  maxLength={19}
                  value={form.cardNumber}
                  onChange={(e) => updateForm({ cardNumber: groupCardNumber(e.target.value) })}
                />
              </div>
            </div>
          </div>
          <div className="frow tri">
            <div className="field">
              <label>Name on card <span className="req">*</span></label>
              <input
                className="inp"
                placeholder="Full name"
                value={form.cardName}
                onChange={(e) => updateForm({ cardName: e.target.value })}
              />
            </div>
            <div className="field">
              <label>Expiry <span className="req">*</span></label>
              <input
                className="inp"
                placeholder="MM / YY"
                maxLength={7}
                value={form.cardExpiry}
                onChange={(e) => updateForm({ cardExpiry: formatCardExpiry(e.target.value) })}
              />
            </div>
            <div className="field">
              <label>CVC <span className="req">*</span></label>
              <div className="control ic">
                <Lock className="lead" aria-hidden />
                <input
                  className="inp"
                  inputMode="numeric"
                  placeholder="123"
                  maxLength={4}
                  value={form.cardCvc}
                  onChange={(e) => updateForm({ cardCvc: e.target.value.replace(/\D/g, '').slice(0, 4) })}
                />
              </div>
            </div>
          </div>
        </div>

        <div className="instal-note">
          <Clock aria-hidden />
          <p>{note}</p>
        </div>
      </div>

      <div className="block">
        <div className="block-head">
          <span className="bnum">2</span>
          <h3>Billing address</h3>
        </div>
        <div className="frow">
          <div className="field full">
            <label>Street address <span className="req">*</span></label>
            <input
              className="inp"
              placeholder="Street and number"
              value={form.billingStreet}
              onChange={(e) => updateForm({ billingStreet: e.target.value })}
            />
          </div>
        </div>
        <div className="frow tri">
          <div className="field">
            <label>City <span className="req">*</span></label>
            <input className="inp" placeholder="City" value={form.billingCity} onChange={(e) => updateForm({ billingCity: e.target.value })} />
          </div>
          <div className="field">
            <label>Postcode <span className="req">*</span></label>
            <input className="inp" placeholder="Postcode" value={form.billingZip} onChange={(e) => updateForm({ billingZip: e.target.value })} />
          </div>
          <div className="field">
            <label>Country <span className="req">*</span></label>
            <CountrySelect
              className="sel"
              value={form.billingCountry}
              onChange={(e) => updateForm({ billingCountry: e.target.value })}
              placeholder="Select"
            />
          </div>
        </div>
      </div>

      <label
        className={`agree${form.agreed ? ' on' : ''}`}
        onClick={() => updateForm({ agreed: !form.agreed })}
      >
        <span className="cbx"><Check aria-hidden /></span>
        <span><TermsLabel isGuesthouse={config.step4.isGuesthouse} /></span>
      </label>
      {errors.agreed && <p className="hint" style={{ color: 'var(--rtb-red)' }}>{errors.agreed}</p>}

      <div className="step-nav">
        <button type="button" className="btn-back" onClick={onBack}>
          <ChevronLeft aria-hidden />
          Back
        </button>
        <button type="button" className="btn-next" onClick={onSubmit} disabled={saving}>
          {saving ? <LoadingSpinner size="sm" className="text-white" /> : config.step4.submitLabel}
        </button>
        <span className="sn-note">
          <Lock aria-hidden />
          256-bit encrypted
        </span>
      </div>
    </div>
  )
}
