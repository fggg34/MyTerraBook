import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import '../../styles/auth-pages.css'

export default function HostRegisterPage() {
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      await registerAsHost(form)
      toast('Host account created', 'success')
      navigate('/host')
    } catch (err) {
      toast(err.response?.data?.message || 'Registration failed', 'error')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="auth-page">
      <div className="wrap auth-shell">
        <div className="auth-intro">
          <Link to="/" className="logo-text">MyTerraBook</Link>
          <h1>Become a host</h1>
          <p>Create your host account to list guesthouses and vehicles.</p>
        </div>
        <form onSubmit={handleSubmit} className="auth-card">
          <div className="auth-field"><label>Name</label><input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></div>
          <div className="auth-field"><label>Email</label><input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required /></div>
          <div className="auth-field"><label>Phone</label><input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} /></div>
          <div className="auth-field"><label>Password</label><input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required /></div>
          <div className="auth-field"><label>Confirm password</label><input type="password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} required /></div>
          <button type="submit" className="auth-submit" disabled={loading}>{loading ? 'Creating…' : 'Create host account'}</button>
          <p className="auth-foot">Already a host? <Link to="/login?redirect=/host">Sign in</Link></p>
        </form>
      </div>
    </div>
  )
}
