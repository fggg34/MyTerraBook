import { useState } from 'react'
import { Link } from 'react-router-dom'
import AuthPageLayout from '../components/auth/AuthPageLayout'
import PageHead from '../components/seo/PageHead'
import { useAuth } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

export default function ForgotPasswordPage({ hostIntent = false }) {
  const { page: authPage } = usePageContent('auth-login')
  const forgot = authPage.forgot ?? {}
  const title = hostIntent
    ? (forgot.hostTitle || 'Forgot host password?')
    : (forgot.title || 'Forgot password?')
  const heroTitle = hostIntent
    ? (forgot.hostHeroTitle || 'Reset host password')
    : (forgot.heroTitle || 'Reset your password')
  const subtitle = hostIntent
    ? (forgot.hostSubtitle || 'Enter your host account email and we will send reset instructions.')
    : (forgot.subtitle || 'Enter the email address linked to your account.')
  const seo = usePageSeo('auth-login', {
    source: {
      title: heroTitle,
      subtitle,
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
        heroTitle={heroTitle}
        heroText={forgot.heroText || 'We will email you a secure link to choose a new password.'}
      >
        <div className="auth-form-head">
          <h1>{title}</h1>
          <p>
            {sent
              ? (forgot.sentMessage || 'If an account exists for that email, we sent a password reset link.')
              : subtitle}
          </p>
        </div>

        {sent ? (
          <div className="auth-form">
            <p className="auth-password-hint">
              {forgot.retryHint || 'Did not receive it? Check spam or try again in a few minutes.'}
            </p>
            <Link to={loginPath} className="auth-submit auth-submit--link">
              {forgot.backLabel || 'Back to sign in'}
            </Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="auth-form">
            {error && <div className="auth-form-error" role="alert">{error}</div>}

            <div className="auth-field">
              <label htmlFor="forgot-email">{forgot.emailLabel || 'Email address'}</label>
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
              {loading ? (forgot.sendingLabel || 'Sending…') : (forgot.submitLabel || 'Send reset link')}
            </button>
          </form>
        )}

        <footer className="auth-layout__footer">
          <p className="auth-switch">
            {forgot.rememberPrompt || 'Remember your password?'}{' '}
            <Link to={loginPath}>{forgot.signInLabel || 'Sign in'}</Link>
          </p>
        </footer>
      </AuthPageLayout>
    </>
  )
}
