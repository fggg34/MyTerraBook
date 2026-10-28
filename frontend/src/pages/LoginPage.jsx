import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PasswordInput from '../components/auth/PasswordInput'
import FigmaIcon from '../components/site/FigmaIcon'
import PageHead from '../components/seo/PageHead'
import { getPostLoginPath, normalizeUserRole, useAuth, useRedirectIfAuthenticated } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

export default function LoginPage({ hostIntent = false }) {
  useRedirectIfAuthenticated(hostIntent)
  const { page: authPage } = usePageContent('auth-login')
  const mergedCopy = hostIntent ? (authPage.hostLogin ?? {}) : authPage
  const seo = usePageSeo('auth-login', {
    source: mergedCopy,
    robots: 'noindex',
  })
  const { loginWithCredentials, logout } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
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
      const role = normalizeUserRole(loggedInUser)
      if (hostIntent && role !== 'host' && role !== 'admin') {
        await logout()
        const msg = 'This account is not registered as a host. Sign in as a guest or create a host account.'
        setErrors({ form: msg })
        toast(msg, 'error')
        return
      }
      toast('Welcome back!', 'success')
      navigate(getPostLoginPath(loggedInUser, { hostIntent }), { replace: true })
    } catch (err) {
      const msg = err.response?.data?.message || 'Invalid email or password'
      setErrors({ form: msg })
      toast(msg, 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <PageHead {...seo} />
      <AuthPageLayout variant="login">
      <form onSubmit={handleSubmit} className="tb-login">
        {errors.form && <div className="auth-form-error" role="alert">{errors.form}</div>}
        <div>
          <p className="kicker">WELCOME BACK</p>
          <h1>Sign in to your <em>trip</em>.</h1>
          <p className="lead">Manage bookings, download vouchers and pick up right where you left off.</p>
        </div>

        <div>
          <label htmlFor="email">Email</label>
          <div className={`tb-field${errors.email ? ' is-error' : ''}`}>
            <FigmaIcon name="mail-navy" size={16} />
            <input
              id="email"
              type="email"
              placeholder="you@example.com"
              autoComplete="email"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
          </div>
          {errors.email && <p className="auth-field-error">{errors.email}</p>}
        </div>

        <div>
          <label htmlFor="password">Password</label>
          <PasswordInput
            id="password"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            autoComplete="current-password"
            hasError={!!errors.password}
            placeholder="••••••••••"
          />
          {errors.password && <p className="auth-field-error">{errors.password}</p>}
        </div>

        <div className="row">
          <label className="auth-checkbox">
            <input
              type="checkbox"
              checked={form.remember}
              onChange={(e) => setForm({ ...form, remember: e.target.checked })}
            />
            Remember me
          </label>
          <Link to={hostIntent ? '/host/forgot-password' : '/forgot-password'} className="tb-link">
            Forgot password?
          </Link>
        </div>

        <button type="submit" className="auth-submit" disabled={loading}>
          {loading ? 'Signing in…' : 'Sign in'} <FigmaIcon name="arrow-white" size={16} />
        </button>
        <div className="or">or</div>
        <Link to={hostIntent ? '/host/register' : '/register'} className="outline">Create an account <FigmaIcon name="arrow" size={16} /></Link>
        {!hostIntent && (
          <Link to="/host/login" className="host"><FigmaIcon name="briefcase" size={16} />Sign in as a host →</Link>
        )}
      </form>
    </AuthPageLayout>
    </>
  )
}
