import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Car } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'

export default function RegisterPage() {
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

  return (
    <div className="flex min-h-[calc(100vh-4rem)] items-center justify-center bg-slate-50 px-4 py-12">
      <div className="w-full max-w-md">
        <div className="text-center">
          <Link to="/" className="inline-flex items-center gap-2 text-brand-950">
            <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent text-white">
              <Car className="h-5 w-5" aria-hidden />
            </div>
            <span className="text-xl font-bold">MyTerraBook</span>
          </Link>
          <h1 className="mt-6 text-2xl font-bold text-brand-950">Create an account</h1>
          <p className="mt-2 text-sm text-slate-500">Join MyTerraBook to manage your rentals</p>
        </div>

        <form onSubmit={handleSubmit} className="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-card">
          {errors.form && (
            <div className="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{errors.form}</div>
          )}

          <div className="space-y-4">
            {[
              { id: 'name', label: 'Full name', type: 'text', key: 'name' },
              { id: 'email', label: 'Email', type: 'email', key: 'email' },
              { id: 'phone', label: 'Phone (optional)', type: 'tel', key: 'phone' },
              { id: 'password', label: 'Password', type: 'password', key: 'password' },
              { id: 'password_confirmation', label: 'Confirm password', type: 'password', key: 'password_confirmation' },
            ].map(({ id, label, type, key }) => (
              <div key={id}>
                <label className="label-field" htmlFor={id}>{label}</label>
                <input
                  id={id}
                  type={type}
                  className={`input-field ${errors[key] ? 'input-field-error' : ''}`}
                  value={form[key]}
                  onChange={(e) => setForm({ ...form, [key]: e.target.value })}
                />
                {errors[key] && <p className="mt-1 text-xs text-red-600">{errors[key]}</p>}
              </div>
            ))}
          </div>

          <button type="submit" className="btn-primary mt-6 w-full py-3" disabled={loading}>
            {loading ? 'Creating account…' : 'Create account'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-slate-600">
          Already have an account?{' '}
          <Link to="/login" className="font-semibold text-accent hover:text-accent-hover">
            Sign in
          </Link>
        </p>
      </div>
    </div>
  )
}
