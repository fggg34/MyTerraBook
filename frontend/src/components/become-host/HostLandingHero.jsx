import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import CmsImage from '../cms/CmsImage'
import CountrySelect from '../forms/CountrySelect'
import PhoneField from '../forms/PhoneField'
import { getPostLoginPath, useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import { formatPhoneForApi } from '../../utils/phone'

const TRUST_POINTS = [
  'Free to list, no upfront costs',
  'You keep 85% of every booking',
  'Insurance and 24/7 support included',
]

function text(value, fallback) {
  const next = typeof value === 'string' ? value.trim() : ''
  return next || fallback
}

export default function HostLandingHero({ hero = {} }) {
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [signup, setSignup] = useState({
    name: '',
    email: '',
    phone: '',
    country_code: '',
    password: '',
    password_confirmation: '',
  })
  const [hostAccountType, setHostAccountType] = useState('individual')
  const [signupLoading, setSignupLoading] = useState(false)

  const title = text(hero.title, 'Earn from your van or guesthouse')
  const lead = text(hero.lead, 'Join 1,800+ Iceland hosts. Free to list, you keep 85%.')
  const submitLabel = text(hero.submitLabel, 'Start hosting')
  const submittingLabel = text(hero.submittingLabel, 'Creating…')
  const earnAmount = text(hero.earnAmount, '€1,900')
  const earnSuffix = text(hero.earnSuffix, '/ month on average')
  const points = Array.isArray(hero.points) && hero.points.length ? hero.points : TRUST_POINTS
  const bgImage = hero.image || null

  const handleSignup = async (e) => {
    e.preventDefault()
    if (!hostAccountType) {
      toast('Select individual or business', 'error')
      return
    }
    if (!signup.country_code) {
      toast('Select the country you operate in', 'error')
      return
    }
    if (signup.password !== signup.password_confirmation) {
      toast('Passwords do not match', 'error')
      return
    }
    setSignupLoading(true)
    try {
      const name = signup.name || signup.email.split('@')[0] || 'Host'
      const user = await registerAsHost({
        name,
        email: signup.email,
        phone: formatPhoneForApi(signup.phone),
        country_code: signup.country_code,
        password: signup.password,
        password_confirmation: signup.password_confirmation,
        host_account_type: hostAccountType,
      })
      toast('Host account created', 'success')
      navigate(getPostLoginPath(user, { hostIntent: true }), { replace: true })
    } catch (err) {
      toast(err.response?.data?.message || 'Could not create account', 'error')
    } finally {
      setSignupLoading(false)
    }
  }

  return (
    <section className="host-landing-hero" id="signup">
      <CmsImage className="host-landing-hero-bg" src={bgImage} alt={hero.imageAlt || ''} aria-hidden={!hero.imageAlt} loading="eager" />
      <div className="host-landing-hero-scrim" aria-hidden="true">
        <div className="host-landing-hero-aurora" />
      </div>
      <div className="wrap host-landing-hero-grid">
        <div className="host-landing-hero-copy">
          <h1>{title}</h1>
          <p className="host-landing-lead">{lead}</p>
          <div className="host-landing-earn">
            <span className="host-landing-earn-amt">{earnAmount}</span>
            <span className="host-landing-earn-per">{earnSuffix}</span>
          </div>
          <ul className="host-landing-points">
            {points.map((point) => (
              <li key={point}>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="m5 13 4 4L19 7" />
                </svg>
                {point}
              </li>
            ))}
          </ul>
        </div>
        <div className="host-landing-signup">
          <h2>{text(hero.signupTitle, 'Create your host account')}</h2>
          <div className="host-landing-type-tabs" role="tablist" aria-label="Host account type">
            <button
              type="button"
              role="tab"
              aria-selected={hostAccountType === 'individual'}
              className={`host-landing-type-tab${hostAccountType === 'individual' ? ' active' : ''}`}
              onClick={() => setHostAccountType('individual')}
            >
              {text(hero.individualLabel, 'Individual')}
            </button>
            <button
              type="button"
              role="tab"
              aria-selected={hostAccountType === 'business'}
              className={`host-landing-type-tab${hostAccountType === 'business' ? ' active' : ''}`}
              onClick={() => setHostAccountType('business')}
            >
              {text(hero.businessLabel, 'Business')}
            </button>
          </div>
          <p className="host-landing-signup-sub">{text(hero.signupLead, 'Free to list. No commitment. Earn on your own schedule.')}</p>
          <form onSubmit={handleSignup}>
            <div className="host-landing-field">
              <label htmlFor="host-su-name">{text(hero.nameLabel, 'Your name')} <span className="host-landing-req">*</span></label>
              <input
                id="host-su-name"
                type="text"
                placeholder={text(hero.namePlaceholder, 'Your name')}
                value={signup.name}
                onChange={(e) => setSignup({ ...signup, name: e.target.value })}
                required
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-email">{text(hero.emailLabel, 'Email')} <span className="host-landing-req">*</span></label>
              <input
                id="host-su-email"
                type="email"
                placeholder={text(hero.emailPlaceholder, 'your@email.com')}
                autoComplete="email"
                value={signup.email}
                onChange={(e) => setSignup({ ...signup, email: e.target.value })}
                required
              />
            </div>
            <div className="host-landing-field">
              <PhoneField
                id="host-su-phone"
                label={text(hero.phoneLabel, 'Phone')}
                variant="host"
                required
                requiredMarkClassName="host-landing-req"
                value={signup.phone}
                onChange={(phone) => setSignup({ ...signup, phone })}
                placeholder="555 1234"
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-country">{text(hero.countryLabel, 'Country you operate in')} <span className="host-landing-req">*</span></label>
              <CountrySelect
                id="host-su-country"
                includeOther={false}
                required
                value={signup.country_code}
                onChange={(e) => setSignup({ ...signup, country_code: e.target.value })}
                placeholder={text(hero.countryPlaceholder, 'Select country')}
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-pass">{text(hero.passwordLabel, 'Password')} <span className="host-landing-req">*</span></label>
              <input
                id="host-su-pass"
                type="password"
                placeholder={text(hero.passwordPlaceholder, 'Create a password')}
                autoComplete="new-password"
                value={signup.password}
                onChange={(e) => setSignup({ ...signup, password: e.target.value })}
                required
                minLength={8}
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-pass-confirm">{text(hero.confirmPasswordLabel, 'Confirm password')} <span className="host-landing-req">*</span></label>
              <input
                id="host-su-pass-confirm"
                type="password"
                placeholder={text(hero.confirmPasswordPlaceholder, 'Confirm your password')}
                autoComplete="new-password"
                value={signup.password_confirmation}
                onChange={(e) => setSignup({ ...signup, password_confirmation: e.target.value })}
                required
                minLength={8}
              />
            </div>
            <button className="host-landing-submit" type="submit" disabled={signupLoading}>
              {signupLoading ? submittingLabel : submitLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </button>
          </form>
          <p className="host-landing-signup-foot">
            {text(hero.loginPrompt, 'Already a host?')} <Link to="/host/login">{text(hero.loginLink, 'Log in')}</Link>
            {' · '}
            <Link to="/host/forgot-password">{text(hero.forgotPasswordLabel, 'Forgot your password?')}</Link>
          </p>
        </div>
      </div>
    </section>
  )
}
