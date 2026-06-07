import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PasswordInput from '../components/auth/PasswordInput'
import PageHead from '../components/seo/PageHead'
import { getPostLoginPath, useAuth } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

const REGISTER_FEATURES = [
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" strokeLinecap="round" />
      </svg>
    ),
    title: 'One account, every trip',
    text: 'Book guesthouses, campervans and cars without juggling multiple logins.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Secure & simple',
    text: 'Your details are protected so you can focus on planning your adventure.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Trip-ready in minutes',
    text: 'Create an account and start browsing Iceland\u2019s best stays and rentals.',
  },
]

export default function RegisterPage() {
  const { page: copy } = usePageContent('auth-register')
  const seo = usePageSeo('auth-register', { source: copy, robots: 'noindex' })
  const { registerAccount } = useAuth()
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
    if (!form.email) e2.email = 'Email is required'
    if (!form.phone.trim()) e2.phone = 'Phone is required'
    if (!form.password) e2.password = 'Password is required'
    else if (form.password.length < 8) e2.password = 'At least 8 characters'
    if (form.password !== form.password_confirmation) e2.password_confirmation = 'Passwords do not match'
    setErrors(e2)
    if (Object.keys(e2).length) return

    setLoading(true)
    try {
      const user = await registerAccount(form)
      toast('Account created successfully!', 'success')
      navigate(getPostLoginPath(user))
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
      toast('Could not create account', 'error')
    } finally {
      setLoading(false)
    }
  }

  const update = (key) => (e) => setForm({ ...form, [key]: e.target.value })

  return (
    <>
      <PageHead {...seo} />
      <AuthPageLayout
      variant="register"
      features={REGISTER_FEATURES}
      heroTitle={copy.heroTitle ?? 'Create your account'}
      heroText={copy.heroText ?? 'Join travelers who book guesthouses, campervans and cars across Iceland — all in one trusted marketplace.'}
    >
      <div className="auth-form-head">
        <h1>{copy.title ?? 'Create an account'}</h1>
        <p>{copy.subtitle ?? 'Fill in your details to get started.'}</p>
      </div>

      <form onSubmit={handleSubmit} className="auth-form auth-form--register">
        {errors.form && <div className="auth-form-error" role="alert">{errors.form}</div>}

        <div className="auth-field">
          <label htmlFor="name">Full name</label>
          <div className={`auth-input-wrap${errors.name ? ' auth-input-wrap--error' : ''}`}>
            <input
              id="name"
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
          <label htmlFor="email">Email address</label>
          <div className={`auth-input-wrap${errors.email ? ' auth-input-wrap--error' : ''}`}>
            <input
              id="email"
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
          <label htmlFor="phone">Phone</label>
          <div className={`auth-input-wrap${errors.phone ? ' auth-input-wrap--error' : ''}`}>
            <input
              id="phone"
              type="tel"
              className="auth-input"
              placeholder="+354 555 1234"
              autoComplete="tel"
              required
              value={form.phone}
              onChange={update('phone')}
            />
          </div>
          {errors.phone && <p className="auth-field-error">{errors.phone}</p>}
        </div>

        <div className="auth-field">
          <label htmlFor="password">Password</label>
          <PasswordInput
            id="password"
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
          <label htmlFor="password_confirmation">Confirm password</label>
          <PasswordInput
            id="password_confirmation"
            value={form.password_confirmation}
            onChange={update('password_confirmation')}
            autoComplete="new-password"
            hasError={!!errors.password_confirmation}
          />
          {errors.password_confirmation && (
            <p className="auth-field-error">{errors.password_confirmation}</p>
          )}
        </div>

        <button type="submit" className="auth-submit" disabled={loading}>
          {loading ? 'Creating account…' : (copy.submitLabel ?? 'Create account')}
        </button>
      </form>

      <footer className="auth-layout__footer">
        <p className="auth-switch">
          {copy.loginPrompt ?? 'Already have an account?'}{' '}
          <Link to="/login">{copy.loginLink ?? 'Sign in'}</Link>
        </p>
      </footer>
    </AuthPageLayout>
    </>
  )
}
