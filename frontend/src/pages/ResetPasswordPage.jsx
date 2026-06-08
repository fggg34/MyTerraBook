import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PasswordInput from '../components/auth/PasswordInput'
import PageHead from '../components/seo/PageHead'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams()
  const navigate = useNavigate()
  const { resetPassword } = useAuth()
  const { toast } = useToast()
  const token = searchParams.get('token') ?? ''
  const emailFromQuery = searchParams.get('email') ?? ''
  const hostIntent = searchParams.get('intent') === 'host'

  const seo = usePageSeo('auth-login', {
    source: {
      title: 'Choose a new password',
      subtitle: 'Enter a new password for your account.',
    },
    robots: 'noindex',
  })

  const [form, setForm] = useState({
    email: emailFromQuery,
    password: '',
    password_confirmation: '',
  })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    const nextErrors = {}
    if (!form.email.trim()) nextErrors.email = 'Email is required'
    if (!token) nextErrors.form = 'This reset link is invalid or expired.'
    if (!form.password) nextErrors.password = 'Password is required'
    else if (form.password.length < 8) nextErrors.password = 'At least 8 characters'
    if (form.password !== form.password_confirmation) {
      nextErrors.password_confirmation = 'Passwords do not match'
    }
    setErrors(nextErrors)
    if (Object.keys(nextErrors).length) return

    setLoading(true)
    try {
      const message = await resetPassword({
        email: form.email.trim(),
        token,
        password: form.password,
        password_confirmation: form.password_confirmation,
      })
      toast(message || 'Password updated. You can sign in now.', 'success')
      navigate(hostIntent ? '/host/login' : '/login', { replace: true })
    } catch (err) {
      const apiErrors = err.response?.data?.errors
      if (apiErrors) {
        const mapped = {}
        Object.entries(apiErrors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value
        })
        setErrors(mapped)
      } else {
        setErrors({ form: err.response?.data?.message || 'Could not reset password' })
      }
      toast('Could not reset password', 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <PageHead {...seo} />
      <AuthPageLayout
        variant="login"
        heroTitle="Choose a new password"
        heroText="Pick a strong password you have not used on MyTerraBook before."
      >
        <div className="auth-form-head">
          <h1>Set a new password</h1>
          <p>Enter your email and choose a new password.</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          {errors.form && <div className="auth-form-error" role="alert">{errors.form}</div>}

          <div className="auth-field">
            <label htmlFor="reset-email">Email address</label>
            <div className={`auth-input-wrap${errors.email ? ' auth-input-wrap--error' : ''}`}>
              <input
                id="reset-email"
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
            <label htmlFor="reset-password">New password</label>
            <PasswordInput
              id="reset-password"
              value={form.password}
              onChange={(e) => setForm({ ...form, password: e.target.value })}
              autoComplete="new-password"
              hasError={!!errors.password}
            />
            {errors.password
              ? <p className="auth-field-error">{errors.password}</p>
              : <p className="auth-password-hint">At least 8 characters</p>}
          </div>

          <div className="auth-field">
            <label htmlFor="reset-password-confirm">Confirm password</label>
            <PasswordInput
              id="reset-password-confirm"
              value={form.password_confirmation}
              onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
              autoComplete="new-password"
              hasError={!!errors.password_confirmation}
            />
            {errors.password_confirmation && (
              <p className="auth-field-error">{errors.password_confirmation}</p>
            )}
          </div>

          <button type="submit" className="auth-submit" disabled={loading || !token}>
            {loading ? 'Updating…' : 'Update password'}
          </button>
        </form>

        <footer className="auth-layout__footer">
          <p className="auth-switch">
            Back to{' '}
            <Link to="/login">guest sign in</Link>
            {' or '}
            <Link to="/host/login">host sign in</Link>
          </p>
        </footer>
      </AuthPageLayout>
    </>
  )
}
