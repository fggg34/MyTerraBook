import { useAuth } from '../../context/AuthContext'

export default function HostSettingsPage() {
  const { user } = useAuth()

  return (
    <div className="host-form-card">
      <h2 className="mb-4 text-xl font-bold text-brand-950">Account</h2>
      <p className="text-sm text-slate-600">Signed in as <strong>{user?.email}</strong></p>
      <p className="mt-2 text-sm text-slate-600">Host profile settings will expand in a future update.</p>
    </div>
  )
}
