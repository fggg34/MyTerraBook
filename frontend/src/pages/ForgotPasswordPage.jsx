import { useState } from 'react'
import { Link } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PageHead from '../components/seo/PageHead'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

export default function ForgotPasswordPage({ hostIntent = false }) {
  const seo = usePageSeo('auth-login', {
    source: {
      title: hostIntent ? 'Reset host password' : 'Reset your password',
      subtitle: hostIntent
        ? 'Enter your host account email and we will send reset instructions.'
        : 'Enter your email and we will send reset instructions.',
    },
    robots: 'noindex',
  })
  const { requestPasswordReset } = useAuth()
  const { toast } = useToast()
  const [email, setEmail] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [sent, setSent] = useState(false)

  const loginPath = hostIntent ? '/host/login' : '/login'

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!email.trim()) {
      setError('Email is required')
      return
    }

    setError('')
    setLoading(true)
    try {
      const message = await requestPasswordReset(email.trim())
      setSent(true)
      toast(message || 'Check your email for reset instructions.', 'success')
    } catch (err) {
      const msg = err.response?.data?.message || 'Could not send reset email'
      setError(msg)
      toast(msg, 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <PageHead {...seo} />
      <AuthPageLayout
        variant="login"
        heroTitle={hostIntent ? 'Reset host password' : 'Reset your password'}
        heroText="We will email you a secure link to choose a new password."
      >
        <div className="auth-form-head">
          <h1>{hostIntent ? 'Forgot host password?' : 'Forgot password?'}</h1>
          <p>
            {sent
              ? 'If an account exists for that email, we sent a password reset link.'
              : 'Enter the email address linked to your account.'}
          </p>
        </div>

        {sent ? (
          <div className="auth-form">
            <p className="auth-password-hint">
              Did not receive it? Check spam or try again in a few minutes.
            </p>
            <Link to={loginPath} className="auth-submit auth-submit--link">
              Back to sign in
            </Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="auth-form">
            {error && <div className="auth-form-error" role="alert">{error}</div>}

            <div className="auth-field">
              <label htmlFor="forgot-email">Email address</label>
              <div className={`auth-input-wrap${error ? ' auth-input-wrap--error' : ''}`}>
                <input
                  id="forgot-email"
                  type="email"
                  className="auth-input"
                  placeholder="you@example.com"
                  autoComplete="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                />
              </div>
            </div>

            <button type="submit" className="auth-submit" disabled={loading}>
              {loading ? 'Sending…' : 'Send reset link'}
            </button>
          </form>
        )}

        <footer className="auth-layout__footer">
          <p className="auth-switch">
            Remember your password?{' '}
            <Link to={loginPath}>Sign in</Link>
          </p>
        </footer>
      </AuthPageLayout>
    </>
  )
}
