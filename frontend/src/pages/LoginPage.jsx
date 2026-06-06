import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import SiteLogo from '../components/branding/SiteLogo'
import { getPostLoginPath, useAuth } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import '../styles/auth-pages.css'

export default function LoginPage() {
  const { page: copy } = usePageContent('auth-login')
  const { loginWithCredentials } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const redirect = searchParams.get('redirect')
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
    <div className="auth-page">
      <div className="wrap auth-shell">
        <div className="auth-intro">
          <SiteLogo variant="auth" className="logo-text" />
          <h1>{copy.title ?? 'Welcome back'}</h1>
          <p>{copy.subtitle ?? 'Sign in to manage your bookings.'}</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-card">
          {errors.form && <div className="auth-error">{errors.form}</div>}

          <div className="auth-field">
            <label htmlFor="email">{copy.emailLabel ?? 'Email'}</label>
            <input
              id="email"
              type="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
            {errors.email && <p className="field-error">{errors.email}</p>}
          </div>

          <div className="auth-field">
            <label htmlFor="password">{copy.passwordLabel ?? 'Password'}</label>
            <input
              id="password"
              type="password"
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
            />
            {errors.password && <p className="field-error">{errors.password}</p>}
          </div>

          <div className="auth-row">
            <label>
              <input
                type="checkbox"
                checked={form.remember}
                onChange={(e) => setForm({ ...form, remember: e.target.checked })}
              />{' '}
              {copy.rememberLabel ?? 'Remember me'}
            </label>
          </div>

          <button type="submit" className="auth-submit" disabled={loading}>
            {loading ? 'Signing in…' : (copy.submitLabel ?? 'Sign in')}
          </button>
        </form>

        <p className="auth-switch">
          {copy.registerPrompt ?? 'New here?'}{' '}
          <Link to="/register">{copy.registerLink ?? 'Create an account'}</Link>
        </p>
      </div>
    </div>
  )
}
