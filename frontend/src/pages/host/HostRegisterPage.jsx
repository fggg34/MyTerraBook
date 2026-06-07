import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../../components/branding/SiteLogo'
import PhoneField from '../../components/forms/PhoneField'
import RequiredMark from '../../components/forms/RequiredMark'
import PageHead from '../../components/seo/PageHead'
import { getPostLoginPath, useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import usePageSeo from '../../hooks/usePageSeo'
import { formatPhoneForApi, validatePhone } from '../../utils/phone'
import '../../styles/auth-pages.css'

export default function HostRegisterPage() {
  const { page: copy } = usePageContent('auth-host-register')
  const seo = usePageSeo('auth-host-register', { source: copy, robots: 'noindex' })
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    const e2 = {}
    if (!form.name.trim()) e2.name = 'Name is required'
    if (!form.email.trim()) e2.email = 'Email is required'
    const phoneError = validatePhone(form.phone)
    if (phoneError) e2.phone = phoneError
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
      navigate(getPostLoginPath(user))
    } catch (err) {
      toast(err.response?.data?.message || 'Registration failed', 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="auth-page">
      <div className="wrap auth-shell">
        <div className="auth-intro">
          <SiteLogo variant="auth" className="logo-text" />
          <h1>{copy.title ?? 'Host registration'}</h1>
          <p>{copy.subtitle ?? 'List your van or guesthouse on MyTerraBook.'}</p>
        </div>
        <form onSubmit={handleSubmit} className="auth-card auth-form--register">
          <div className="auth-field">
            <label htmlFor="host-reg-name">Name <RequiredMark /></label>
            <input id="host-reg-name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
            {errors.name && <p className="auth-field-error">{errors.name}</p>}
          </div>
          <div className="auth-field">
            <label htmlFor="host-reg-email">Email <RequiredMark /></label>
            <input id="host-reg-email" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
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
            <input id="host-reg-password" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required minLength={8} autoComplete="new-password" />
          </div>
          <div className="auth-field">
            <label htmlFor="host-reg-password-confirm">Confirm password <RequiredMark /></label>
            <input id="host-reg-password-confirm" type="password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} required minLength={8} autoComplete="new-password" />
            {errors.password_confirmation && <p className="auth-field-error">{errors.password_confirmation}</p>}
          </div>
          <button type="submit" className="auth-submit" disabled={loading}>{loading ? 'Creating…' : (copy.submitLabel ?? 'Register as host')}</button>
          <p className="auth-foot">Already a host? <Link to="/host/login">Sign in</Link></p>
        </form>
      </div>
    </div>
    </>
  )
}
