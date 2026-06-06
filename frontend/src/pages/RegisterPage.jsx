import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../components/branding/SiteLogo'
import { useAuth } from '../context/AuthContext'
import { usePageContent } from '../context/SiteContentContext'
import { useToast } from '../context/ToastContext'
import '../styles/auth-pages.css'

export default function RegisterPage() {
  const { page: copy } = usePageContent('auth-register')
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
    if (!form.password) e2.password = 'Password is required'
    else if (form.password.length < 8) e2.password = 'At least 8 characters'
    if (form.password !== form.password_confirmation) e2.password_confirmation = 'Passwords do not match'
    setErrors(e2)
    if (Object.keys(e2).length) return

    setLoading(true)
    try {
      await registerAccount(form)
      toast('Account created successfully!', 'success')
      navigate('/dashboard')
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

  const fields = [
    { id: 'name', label: 'Full name', type: 'text', key: 'name' },
    { id: 'email', label: 'Email', type: 'email', key: 'email' },
    { id: 'phone', label: 'Phone (optional)', type: 'tel', key: 'phone' },
    { id: 'password', label: 'Password', type: 'password', key: 'password' },
    { id: 'password_confirmation', label: 'Confirm password', type: 'password', key: 'password_confirmation' },
  ]

  return (
    <div className="auth-page">
      <div className="wrap auth-shell">
        <div className="auth-intro">
          <SiteLogo variant="auth" className="logo-text" />
          <h1>{copy.title ?? 'Create an account'}</h1>
          <p>{copy.subtitle ?? 'Book across Iceland in one place.'}</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-card">
          {errors.form && <div className="auth-error">{errors.form}</div>}

          {fields.map(({ id, label, type, key }) => (
            <div className="auth-field" key={id}>
              <label htmlFor={id}>{label}</label>
              <input
                id={id}
                type={type}
                value={form[key]}
                onChange={(e) => setForm({ ...form, [key]: e.target.value })}
              />
              {errors[key] && <p className="field-error">{errors[key]}</p>}
            </div>
          ))}

          <button type="submit" className="auth-submit" disabled={loading}>
            {loading ? 'Creating account…' : (copy.submitLabel ?? 'Create account')}
          </button>
        </form>

        <p className="auth-switch">
          {copy.loginPrompt ?? 'Already have an account?'}{' '}
          <Link to="/login">{copy.loginLink ?? 'Sign in'}</Link>
        </p>
      </div>
    </div>
  )
}
