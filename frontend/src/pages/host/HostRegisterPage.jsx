import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import AuthPageLayout from '../../components/auth/AuthPageLayout'
import PasswordInput from '../../components/auth/PasswordInput'
import PhoneField from '../../components/forms/PhoneField'
import RequiredMark from '../../components/forms/RequiredMark'
import PageHead from '../../components/seo/PageHead'
import { getPostLoginPath, useAuth, useRedirectIfAuthenticated } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import usePageSeo from '../../hooks/usePageSeo'
import { formatPhoneForApi, validatePhone } from '../../utils/phone'
import '../../styles/auth-pages.css'

const HOST_REGISTER_FEATURES = [
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Free to list',
    text: 'No upfront costs or subscriptions. Publish when you are ready and earn on your schedule.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" strokeLinecap="round" strokeLinejoin="round" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'You keep 85%',
    text: 'Industry-leading commission so more of every booking stays in your pocket.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="m9 12 2 2 4-4" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Insurance & 24/7 support',
    text: 'Guest coverage and a dedicated Iceland team whenever you need help.',
  },
]

export default function HostRegisterPage() {
  useRedirectIfAuthenticated(true)
  const { page: copy } = usePageContent('auth-host-register')
  const seo = usePageSeo('auth-host-register', { source: copy, robots: 'noindex' })
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
  })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    const e2 = {}
    if (!form.name.trim()) e2.name = 'Name is required'
    if (!form.email.trim()) e2.email = 'Email is required'
    const phoneError = validatePhone(form.phone)
    if (phoneError) e2.phone = phoneError
    if (!form.password) e2.password = 'Password is required'
    else if (form.password.length < 8) e2.password = 'At least 8 characters'
    if (form.password !== form.password_confirmation) {
      e2.password_confirmation = 'Passwords do not match'
    }
    setErrors(e2)
    if (Object.keys(e2).length) return

    setLoading(true)
    try {
      const user = await registerAsHost({
        ...form,
        phone: formatPhoneForApi(form.phone),
      })
      toast('Host account created', 'success')
      navigate(getPostLoginPath(user, { hostIntent: true }), { replace: true })
    } catch (err) {
      const apiErrors = err.response?.data?.errors
      if (apiErrors) {
        const mapped = {}
        Object.entries(apiErrors).forEach(([k, v]) => {
          mapped[k] = Array.isArray(v) ? v[0] : v
        })
        setErrors(mapped)
      } else {
        setErrors({ form: err.response?.data?.message || 'Registration failed' })
      }
      toast(err.response?.data?.message || 'Registration failed', 'error')
    } finally {
      setLoading(false)
    }
  }

  const update = (key) => (e) => setForm({ ...form, [key]: e.target.value })

  return (
    <>
      <PageHead {...seo} />
      <AuthPageLayout
        variant="host-register"
        features={HOST_REGISTER_FEATURES}
        heroTitle={copy.heroTitle ?? 'Turn your van or guesthouse into income'}
        heroText={copy.heroText ?? 'Join 1,800+ Iceland hosts listing guesthouses and campervans on MyTerraBook.'}
        heroStat={{
          amount: copy.earnAmount ?? '€1,900',
          suffix: copy.earnSuffix ?? '/ month on average',
        }}
      >
        <div className="auth-form-head">
          <p className="auth-form-eyebrow">Host program</p>
          <h1>{copy.title ?? 'Create your host account'}</h1>
          <p>{copy.subtitle ?? 'List your van or guesthouse. Free to start, no commitment.'}</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form auth-form--register">
          {errors.form && <div className="auth-form-error" role="alert">{errors.form}</div>}

          <div className="auth-field">
            <label htmlFor="host-reg-name">Full name <RequiredMark /></label>
            <div className={`auth-input-wrap${errors.name ? ' auth-input-wrap--error' : ''}`}>
              <input
                id="host-reg-name"
                type="text"
                className="auth-input"
                placeholder="Jane Smith"
                autoComplete="name"
                value={form.name}
                onChange={update('name')}
              />
            </div>
            {errors.name && <p className="auth-field-error">{errors.name}</p>}
          </div>

          <div className="auth-field">
            <label htmlFor="host-reg-email">Email address <RequiredMark /></label>
            <div className={`auth-input-wrap${errors.email ? ' auth-input-wrap--error' : ''}`}>
              <input
                id="host-reg-email"
                type="email"
                className="auth-input"
                placeholder="you@example.com"
                autoComplete="email"
                value={form.email}
                onChange={update('email')}
              />
            </div>
            {errors.email && <p className="auth-field-error">{errors.email}</p>}
          </div>

          <div className="auth-field">
            <PhoneField
              id="host-reg-phone"
              label="Phone"
              variant="auth"
              required
              value={form.phone}
              onChange={(phone) => setForm({ ...form, phone })}
              hasError={!!errors.phone}
              placeholder="555 1234"
            />
            {errors.phone && <p className="auth-field-error">{errors.phone}</p>}
          </div>

          <div className="auth-field">
            <label htmlFor="host-reg-password">Password <RequiredMark /></label>
            <PasswordInput
              id="host-reg-password"
              value={form.password}
              onChange={update('password')}
              autoComplete="new-password"
              hasError={!!errors.password}
            />
            {errors.password
              ? <p className="auth-field-error">{errors.password}</p>
              : <p className="auth-password-hint">At least 8 characters</p>}
          </div>

          <div className="auth-field">
            <label htmlFor="host-reg-password-confirm">Confirm password <RequiredMark /></label>
            <PasswordInput
              id="host-reg-password-confirm"
              value={form.password_confirmation}
              onChange={update('password_confirmation')}
              autoComplete="new-password"
              hasError={!!errors.password_confirmation}
            />
            {errors.password_confirmation && (
              <p className="auth-field-error">{errors.password_confirmation}</p>
            )}
          </div>

          <button type="submit" className="auth-submit auth-submit--host" disabled={loading}>
            {loading ? 'Creating account…' : (copy.submitLabel ?? 'Start hosting')}
          </button>
        </form>

        <footer className="auth-layout__footer">
          <p className="auth-switch">
            {copy.loginPrompt ?? 'Already a host?'}{' '}
            <Link to="/host/login">{copy.loginLink ?? 'Sign in'}</Link>
            {' · '}
            <Link to="/host/forgot-password">Forgot your password?</Link>
          </p>
          <div className="auth-divider" aria-hidden="true">or</div>
          <Link to="/become-a-host" className="auth-host-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
              <path d="M9 18h6M10 22h4M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6L15 18H9l-.7-3C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7Z" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            Learn more about hosting
          </Link>
        </footer>
      </AuthPageLayout>
    </>
  )
}
