import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { getPostLoginPath, useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'

const TRUST_POINTS = [
  'Free to list — no upfront costs',
  'You keep 85% of every booking',
  'Insurance and 24/7 support included',
]

export default function HostLandingHero({ hero = {} }) {
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [signup, setSignup] = useState({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
  const [signupLoading, setSignupLoading] = useState(false)

  const title = hero.title ?? 'Earn from your van or guesthouse'
  const lead = hero.lead ?? 'Join 1,800+ Iceland hosts. Free to list, you keep 85%.'
  const submitLabel = hero.submitLabel ?? 'Start hosting'
  const earnAmount = hero.earnAmount ?? '€1,900'
  const bgImage = hero.image ?? '/images/homepage/host-van.jpg'

  const handleSignup = async (e) => {
    e.preventDefault()
    setSignupLoading(true)
    try {
      const name = signup.name || signup.email.split('@')[0] || 'Host'
      const user = await registerAsHost({
        name,
        email: signup.email,
        phone: signup.phone,
        password: signup.password,
        password_confirmation: signup.password_confirmation,
      })
      toast('Host account created', 'success')
      navigate(getPostLoginPath(user))
    } catch (err) {
      toast(err.response?.data?.message || 'Could not create account', 'error')
    } finally {
      setSignupLoading(false)
    }
  }

  return (
    <section className="host-landing-hero" id="signup">
      <img className="host-landing-hero-bg" src={bgImage} alt="" aria-hidden="true" />
      <div className="host-landing-hero-scrim" aria-hidden="true">
        <div className="host-landing-hero-aurora" />
      </div>
      <div className="wrap host-landing-hero-grid">
        <div className="host-landing-hero-copy">
          <h1>{title}</h1>
          <p className="host-landing-lead">{lead}</p>
          <div className="host-landing-earn">
            <span className="host-landing-earn-amt">{earnAmount}</span>
            <span className="host-landing-earn-per">/ month on average</span>
          </div>
          <ul className="host-landing-points">
            {TRUST_POINTS.map((point) => (
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
          <h2>Create your host account</h2>
          <p className="host-landing-signup-sub">Free to list. No commitment. Earn on your own schedule.</p>
          <form onSubmit={handleSignup}>
            <div className="host-landing-field">
              <label htmlFor="host-su-name">Your name</label>
              <input
                id="host-su-name"
                type="text"
                placeholder="Your name"
                value={signup.name}
                onChange={(e) => setSignup({ ...signup, name: e.target.value })}
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-email">Email</label>
              <input
                id="host-su-email"
                type="email"
                placeholder="your@email.com"
                autoComplete="email"
                value={signup.email}
                onChange={(e) => setSignup({ ...signup, email: e.target.value })}
                required
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-phone">Phone</label>
              <input
                id="host-su-phone"
                type="tel"
                placeholder="+354 555 1234"
                autoComplete="tel"
                value={signup.phone}
                onChange={(e) => setSignup({ ...signup, phone: e.target.value })}
                required
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-pass">Password</label>
              <input
                id="host-su-pass"
                type="password"
                placeholder="Create a password"
                autoComplete="new-password"
                value={signup.password}
                onChange={(e) => setSignup({ ...signup, password: e.target.value })}
                required
                minLength={8}
              />
            </div>
            <div className="host-landing-field">
              <label htmlFor="host-su-pass-confirm">Confirm password</label>
              <input
                id="host-su-pass-confirm"
                type="password"
                placeholder="Confirm your password"
                autoComplete="new-password"
                value={signup.password_confirmation}
                onChange={(e) => setSignup({ ...signup, password_confirmation: e.target.value })}
                required
                minLength={8}
              />
            </div>
            <button className="host-landing-submit" type="submit" disabled={signupLoading}>
              {signupLoading ? 'Creating…' : submitLabel}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </button>
          </form>
          <p className="host-landing-signup-foot">
            Already a host? <Link to="/host/login">Log in</Link>
          </p>
        </div>
      </div>
    </section>
  )
}
