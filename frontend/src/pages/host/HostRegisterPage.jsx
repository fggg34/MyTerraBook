import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../../components/branding/SiteLogo'
import { getPostLoginPath, useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import '../../styles/auth-pages.css'

export default function HostRegisterPage() {
  const { page: copy } = usePageContent('auth-host-register')
  const { registerAsHost } = useAuth()
  const { toast } = useToast()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)
    try {
      const user = await registerAsHost(form)
      toast('Host account created', 'success')
      navigate(getPostLoginPath(user, '/host'))
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
          <SiteLogo variant="auth" className="logo-text" />
          <h1>{copy.title ?? 'Host registration'}</h1>
          <p>{copy.subtitle ?? 'List your van or guesthouse on MyTerraBook.'}</p>
        </div>
        <form onSubmit={handleSubmit} className="auth-card">
          <div className="auth-field"><label>Name</label><input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required /></div>
          <div className="auth-field"><label>Email</label><input type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required /></div>
          <div className="auth-field"><label>Phone</label><input value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} /></div>
          <div className="auth-field"><label>Password</label><input type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required /></div>
          <div className="auth-field"><label>Confirm password</label><input type="password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} required /></div>
          <button type="submit" className="auth-submit" disabled={loading}>{loading ? 'Creating…' : (copy.submitLabel ?? 'Register as host')}</button>
          <p className="auth-foot">Already a host? <Link to="/host/login">Sign in</Link></p>
        </form>
      </div>
    </div>
  )
}
