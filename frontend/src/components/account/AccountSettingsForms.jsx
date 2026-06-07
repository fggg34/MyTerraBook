import { useEffect, useState } from 'react'
import { updatePassword, updateProfile } from '../../api/me'
import { getStoredToken, storeAuth } from '../../auth'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'

const emptyProfile = { name: '', email: '', phone: '', current_password: '' }
const emptyPassword = { current_password: '', password: '', password_confirmation: '' }

export default function AccountSettingsForms({
  requirePhone = false,
  profileDescription = 'Update your contact details.',
}) {
  const { user, setUser } = useAuth()
  const { toast } = useToast()
  const [profile, setProfile] = useState(emptyProfile)
  const [passwordForm, setPasswordForm] = useState(emptyPassword)
  const [savingProfile, setSavingProfile] = useState(false)
  const [savingPassword, setSavingPassword] = useState(false)

  useEffect(() => {
    if (!user) return
    setProfile({
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      current_password: '',
    })
  }, [user])

  const emailChanged = profile.email !== (user?.email || '')

  const handleProfileSubmit = async (e) => {
    e.preventDefault()
    setSavingProfile(true)
    try {
      const payload = {
        name: profile.name,
        email: profile.email,
        phone: requirePhone ? profile.phone : profile.phone || null,
      }
      if (emailChanged) {
        payload.current_password = profile.current_password
      }
      const res = await updateProfile(payload)
      const nextUser = res.data.user
      const token = getStoredToken()
      if (token) storeAuth(token, nextUser)
      setUser(nextUser)
      setProfile((prev) => ({ ...prev, current_password: '' }))
      toast('Profile updated', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not update profile', 'error')
    } finally {
      setSavingProfile(false)
    }
  }

  const handlePasswordSubmit = async (e) => {
    e.preventDefault()
    setSavingPassword(true)
    try {
      await updatePassword(passwordForm)
      setPasswordForm(emptyPassword)
      toast('Password updated', 'success')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not update password', 'error')
    } finally {
      setSavingPassword(false)
    }
  }

  return (
    <div className="host-wizard space-y-6">
      <form onSubmit={handleProfileSubmit} className="host-form-card">
        <h2 className="mb-1 text-xl font-bold text-brand-950">Profile</h2>
        <p className="mb-5 text-sm text-slate-600">{profileDescription}</p>

        <div className="host-field">
          <label htmlFor="account-name">Name</label>
          <input
            id="account-name"
            value={profile.name}
            onChange={(e) => setProfile({ ...profile, name: e.target.value })}
            required
          />
        </div>

        <div className="host-field">
          <label htmlFor="account-email">Email</label>
          <input
            id="account-email"
            type="email"
            value={profile.email}
            onChange={(e) => setProfile({ ...profile, email: e.target.value })}
            required
          />
        </div>

        <div className="host-field">
          <label htmlFor="account-phone">Phone</label>
          <input
            id="account-phone"
            type="tel"
            value={profile.phone}
            onChange={(e) => setProfile({ ...profile, phone: e.target.value })}
            placeholder="+354 555 1234"
            required={requirePhone}
          />
        </div>

        {emailChanged && (
          <div className="host-field">
            <label htmlFor="account-current-password">Current password</label>
            <input
              id="account-current-password"
              type="password"
              autoComplete="current-password"
              value={profile.current_password}
              onChange={(e) => setProfile({ ...profile, current_password: e.target.value })}
              required
            />
            <p className="mt-1 text-xs text-slate-500">Required when changing your email address.</p>
          </div>
        )}

        <div className="host-actions">
          <button type="submit" className="host-btn primary" disabled={savingProfile}>
            {savingProfile ? 'Saving…' : 'Save profile'}
          </button>
        </div>
      </form>

      <form onSubmit={handlePasswordSubmit} className="host-form-card">
        <h2 className="mb-1 text-xl font-bold text-brand-950">Password</h2>
        <p className="mb-5 text-sm text-slate-600">Choose a strong password with at least 8 characters.</p>

        <div className="host-field">
          <label htmlFor="account-password-current">Current password</label>
          <input
            id="account-password-current"
            type="password"
            autoComplete="current-password"
            value={passwordForm.current_password}
            onChange={(e) => setPasswordForm({ ...passwordForm, current_password: e.target.value })}
            required
          />
        </div>

        <div className="host-field">
          <label htmlFor="account-password-new">New password</label>
          <input
            id="account-password-new"
            type="password"
            autoComplete="new-password"
            value={passwordForm.password}
            onChange={(e) => setPasswordForm({ ...passwordForm, password: e.target.value })}
            required
            minLength={8}
          />
        </div>

        <div className="host-field">
          <label htmlFor="account-password-confirm">Confirm new password</label>
          <input
            id="account-password-confirm"
            type="password"
            autoComplete="new-password"
            value={passwordForm.password_confirmation}
            onChange={(e) => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })}
            required
            minLength={8}
          />
        </div>

        <div className="host-actions">
          <button type="submit" className="host-btn primary" disabled={savingPassword}>
            {savingPassword ? 'Updating…' : 'Update password'}
          </button>
        </div>
      </form>
    </div>
  )
}
