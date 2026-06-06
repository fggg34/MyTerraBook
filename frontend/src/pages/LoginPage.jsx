import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PasswordInput from '../components/auth/PasswordInput'
import { getPostLoginPath, useAuth } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import '../styles/auth-pages.css'

export default function LoginPage({ hostIntent = false }) {
  const { page: copy } = usePageContent(hostIntent ? 'auth-host-register' : 'auth-login')
  const loginCopy = hostIntent
    ? {
        title: 'Host sign in',
        subtitle: 'Manage your listings, bookings and payouts.',
        submitLabel: 'Sign in to host panel',
        registerPrompt: 'New host?',
        registerLink: 'Create a host account',
        heroTitle: 'Welcome back, host',
        heroText: 'Manage your guesthouses, campervans and bookings from one dashboard.',
      }
    : {}
  const mergedCopy = { ...copy, ...loginCopy }
  const { loginWithCredentials } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const redirect = searchParams.get('redirect') || (hostIntent ? '/host' : null)
  const [form, setForm] = useState({ email: '', password: '', remember: false })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    const e2 = {}
    if (!form.email) e2.email = 'Email is required'
    if (!form.password) e2.password = 'Password is required'
    setErrors(e2)
    if (Object.keys(e2).length) return

    setLoading(true)
    try {
      const loggedInUser = await loginWithCredentials(form.email, form.password)
      toast('Welcome back!', 'success')
      navigate(getPostLoginPath(loggedInUser, redirect))
    } catch (err) {
      const msg = err.response?.data?.message || 'Invalid email or password'
      setErrors({ form: msg })
      toast(msg, 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <AuthPageLayout
      variant="login"
      heroTitle={mergedCopy.heroTitle ?? 'Sign in to MyTerraBook'}
      heroText={mergedCopy.heroText ?? 'Pick up where you left off. Your bookings, saved listings and trip details are waiting.'}
    >
      <div className="auth-form-head">
        <h1>{mergedCopy.title ?? 'Sign in'}</h1>
        <p>{mergedCopy.subtitle ?? 'Enter your details to access your account.'}</p>
      </div>

      <form onSubmit={handleSubmit} className="auth-form">
        {errors.form && <div className="auth-form-error" role="alert">{errors.form}</div>}

        <div className="auth-field">
          <label htmlFor="email">{mergedCopy.emailLabel ?? 'Email address'}</label>
          <div className={`auth-input-wrap${errors.email ? ' auth-input-wrap--error' : ''}`}>
            <input
              id="email"
              type="email"
              className="auth-input"
              placeholder="you@example.com"
              autoComplete="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </div>
          {errors.email && <p className="auth-field-error">{errors.email}</p>}
        </div>

        <div className="auth-field">
          <label htmlFor="password">{mergedCopy.passwordLabel ?? 'Password'}</label>
          <PasswordInput
            id="password"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            autoComplete="current-password"
            hasError={!!errors.password}
          />
          {errors.password && <p className="auth-field-error">{errors.password}</p>}
        </div>

        <div className="auth-form-meta">
          <label className="auth-checkbox">
            <input
              type="checkbox"
              checked={form.remember}
              onChange={(e) => setForm({ ...form, remember: e.target.checked })}
            />
            {mergedCopy.rememberLabel ?? 'Remember me'}
          </label>
        </div>

        <button type="submit" className="auth-submit" disabled={loading}>
          {loading ? 'Signing in…' : (mergedCopy.submitLabel ?? 'Sign in')}
        </button>
      </form>

      <footer className="auth-layout__footer">
        <p className="auth-switch">
          {mergedCopy.registerPrompt ?? 'New here?'}{' '}
          <Link to={hostIntent ? '/become-a-host' : '/register'}>
            {mergedCopy.registerLink ?? (hostIntent ? 'Create a host account' : 'Create an account')}
          </Link>
        </p>
        {!hostIntent && (
          <>
            <div className="auth-divider" aria-hidden="true">or</div>
            <Link to="/host/login" className="auth-host-link">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
                <path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5Z" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              Sign in as a host
            </Link>
          </>
        )}
      </footer>
    </AuthPageLayout>
  )
}
