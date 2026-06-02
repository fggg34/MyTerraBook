import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Car } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import { useToast } from '../context/ToastContext'

export default function LoginPage() {
  const { loginWithCredentials } = useAuth()
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
      await loginWithCredentials(form.email, form.password)
      toast('Welcome back!', 'success')
      navigate('/dashboard')
    } catch (err) {
      const msg = err.response?.data?.message || 'Invalid email or password'
      setErrors({ form: msg })
      toast(msg, 'error')
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
          <h1 className="mt-6 text-2xl font-bold text-brand-950">Welcome back</h1>
          <p className="mt-2 text-sm text-slate-500">Sign in to manage your bookings</p>
        </div>

        <form onSubmit={handleSubmit} className="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-card">
          {errors.form && (
            <div className="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{errors.form}</div>
          )}

          <div className="space-y-4">
            <div>
              <label className="label-field" htmlFor="email">Email</label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                className={`input-field ${errors.email ? 'input-field-error' : ''}`}
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
              />
              {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
            </div>

            <div>
              <label className="label-field" htmlFor="password">Password</label>
              <input
                id="password"
                type="password"
                autoComplete="current-password"
                className={`input-field ${errors.password ? 'input-field-error' : ''}`}
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
              />
              {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
            </div>

            <div className="flex items-center justify-between text-sm">
              <label className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  checked={form.remember}
                  onChange={(e) => setForm({ ...form, remember: e.target.checked })}
                  className="rounded border-slate-300 text-accent focus:ring-accent"
                />
                <span className="text-slate-600">Remember me</span>
              </label>
              <a href="#" className="font-medium text-accent hover:text-accent-hover">
                Forgot password?
              </a>
            </div>
          </div>

          <button type="submit" className="btn-primary mt-6 w-full py-3" disabled={loading}>
            {loading ? 'Signing in…' : 'Sign in'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-slate-600">
          Don&apos;t have an account?{' '}
          <Link to="/register" className="font-semibold text-accent hover:text-accent-hover">
            Create one
          </Link>
        </p>
      </div>
    </div>
  )
}
