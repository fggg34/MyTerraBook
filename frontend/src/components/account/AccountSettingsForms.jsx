import { useEffect, useState } from 'react'
import PasswordInput from '../auth/PasswordInput'
import PhoneField from '../forms/PhoneField'
import RequiredMark from '../forms/RequiredMark'
import { getStoredToken, storeAuth } from '../../auth'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import { updatePassword, updateProfile } from '../../api/me'
import { formatPhoneForApi, validatePhone } from '../../utils/phone'
import { useShopConfig } from '../../context/ShopConfigContext'
import HostCurrencySelect from '../host/HostCurrencySelect'

export default function AccountSettingsForms({
  requirePhone = false,
  showCurrency = false,
  profileDescription = 'Update your account details.',
}) {
  const { user, setUser } = useAuth()
  const { toast } = useToast()
  const { baseCurrency } = useShopConfig()

  const [profile, setProfile] = useState({
    name: '',
    email: '',
    phone: '',
    currency: baseCurrency || 'EUR',
    current_password: '',
  })
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  })
  const [profileErrors, setProfileErrors] = useState({})
  const [passwordErrors, setPasswordErrors] = useState({})
  const [profileLoading, setProfileLoading] = useState(false)
  const [passwordLoading, setPasswordLoading] = useState(false)

  useEffect(() => {
    if (!user) return
    setProfile({
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      currency: user.currency || baseCurrency || 'EUR',
      current_password: '',
    })
  }, [user, baseCurrency])

  const handleProfileSubmit = async (e) => {
    e.preventDefault()
    const errors = {}
    if (!profile.name.trim()) errors.name = 'Name is required'
    if (!profile.email.trim()) errors.email = 'Email is required'
    if (requirePhone) {
      const phoneError = validatePhone(profile.phone)
      if (phoneError) errors.phone = phoneError
    }
    if (showCurrency && !profile.currency) {
      errors.currency = 'Pricing currency is required'
    }
    if (profile.email !== user?.email && !profile.current_password) {
      errors.current_password = 'Current password is required to change email'
    }
    setProfileErrors(errors)
    if (Object.keys(errors).length) return

    setProfileLoading(true)
    try {
      const payload = {
        name: profile.name.trim(),
        email: profile.email.trim(),
        phone: formatPhoneForApi(profile.phone),
      }
      if (showCurrency) {
        payload.currency = profile.currency
      }
      if (profile.current_password) {
        payload.current_password = profile.current_password
      }
      const res = await updateProfile(payload)
      const nextUser = res.data?.user ?? res.data?.data
      if (nextUser) {
        setUser(nextUser)
        const token = getStoredToken()
        if (token) storeAuth(token, nextUser)
      }
      setProfile((prev) => ({ ...prev, current_password: '' }))
      toast('Profile updated', 'success')
    } catch (err) {
      const apiErrors = err.response?.data?.errors
      if (apiErrors) {
        const mapped = {}
        Object.entries(apiErrors).forEach(([k, v]) => {
          mapped[k] = Array.isArray(v) ? v[0] : v
        })
        setProfileErrors(mapped)
      } else {
        toast(err.response?.data?.message || 'Could not update profile', 'error')
      }
    } finally {
      setProfileLoading(false)
    }
  }

  const handlePasswordSubmit = async (e) => {
    e.preventDefault()
    const errors = {}
    if (!passwordForm.current_password) errors.current_password = 'Current password is required'
    if (!passwordForm.password) errors.password = 'New password is required'
    else if (passwordForm.password.length < 8) errors.password = 'At least 8 characters'
    if (passwordForm.password !== passwordForm.password_confirmation) {
      errors.password_confirmation = 'Passwords do not match'
    }
    setPasswordErrors(errors)
    if (Object.keys(errors).length) return

    setPasswordLoading(true)
    try {
      await updatePassword(passwordForm)
      setPasswordForm({ current_password: '', password: '', password_confirmation: '' })
      toast('Password updated', 'success')
    } catch (err) {
      const apiErrors = err.response?.data?.errors
      if (apiErrors) {
        const mapped = {}
        Object.entries(apiErrors).forEach(([k, v]) => {
          mapped[k] = Array.isArray(v) ? v[0] : v
        })
        setPasswordErrors(mapped)
      } else {
        toast(err.response?.data?.message || 'Could not update password', 'error')
      }
    } finally {
      setPasswordLoading(false)
    }
  }

  return (
    <div className="client-settings">
      <section className="client-settings-section">
        <h2>Profile</h2>
        <p className="client-settings-desc">{profileDescription}</p>
        <form onSubmit={handleProfileSubmit} className="client-settings-form">
          <div className="client-field">
            <label htmlFor="settings-name">Full name <RequiredMark className="client-req" /></label>
            <input
              id="settings-name"
              type="text"
              value={profile.name}
              onChange={(e) => setProfile({ ...profile, name: e.target.value })}
              required
            />
            {profileErrors.name && <p className="client-field-error">{profileErrors.name}</p>}
          </div>
          <div className="client-field">
            <label htmlFor="settings-email">Email address <RequiredMark className="client-req" /></label>
            <input
              id="settings-email"
              type="email"
              value={profile.email}
              onChange={(e) => setProfile({ ...profile, email: e.target.value })}
              required
            />
            {profileErrors.email && <p className="client-field-error">{profileErrors.email}</p>}
          </div>
          <div className="client-field">
            <PhoneField
              id="settings-phone"
              label="Phone"
              variant="client"
              required={requirePhone}
              requiredMarkClassName="client-req"
              value={profile.phone}
              onChange={(phone) => setProfile({ ...profile, phone })}
              hasError={!!profileErrors.phone}
              placeholder="555 1234"
            />
            {profileErrors.phone && <p className="client-field-error">{profileErrors.phone}</p>}
          </div>
          {showCurrency && (
            <div className="client-field client-currency-field">
              <div className="client-currency-field__main">
                <div className="client-currency-field__text">
                  <label htmlFor="settings-currency">Pricing currency</label>
                  <p className="client-currency-field__desc">
                    Vehicle and guesthouse prices you enter use this currency.
                  </p>
                </div>
                <div className="client-currency-field__control">
                  <HostCurrencySelect
                    value={profile.currency}
                    onChange={(v) => setProfile({ ...profile, currency: v })}
                    ariaLabel="Pricing currency"
                    className="client-currency-select"
                  />
                </div>
              </div>
              {profileErrors.currency && <p className="client-field-error">{profileErrors.currency}</p>}
            </div>
          )}
          {profile.email !== user?.email && (
            <div className="client-field">
              <label htmlFor="settings-current-for-email">Current password</label>
              <PasswordInput
                id="settings-current-for-email"
                value={profile.current_password}
                onChange={(e) => setProfile({ ...profile, current_password: e.target.value })}
                autoComplete="current-password"
                hasError={!!profileErrors.current_password}
              />
              {profileErrors.current_password && (
                <p className="client-field-error">{profileErrors.current_password}</p>
              )}
            </div>
          )}
          <button type="submit" className="client-btn primary" disabled={profileLoading}>
            {profileLoading ? 'Saving…' : 'Save profile'}
          </button>
        </form>
      </section>

      <section className="client-settings-section">
        <h2>Password</h2>
        <p className="client-settings-desc">Choose a strong password with at least 8 characters.</p>
        <form onSubmit={handlePasswordSubmit} className="client-settings-form">
          <div className="client-field">
            <label htmlFor="settings-current-password">Current password</label>
            <PasswordInput
              id="settings-current-password"
              value={passwordForm.current_password}
              onChange={(e) => setPasswordForm({ ...passwordForm, current_password: e.target.value })}
              autoComplete="current-password"
              hasError={!!passwordErrors.current_password}
            />
            {passwordErrors.current_password && (
              <p className="client-field-error">{passwordErrors.current_password}</p>
            )}
          </div>
          <div className="client-field">
            <label htmlFor="settings-new-password">New password</label>
            <PasswordInput
              id="settings-new-password"
              value={passwordForm.password}
              onChange={(e) => setPasswordForm({ ...passwordForm, password: e.target.value })}
              autoComplete="new-password"
              hasError={!!passwordErrors.password}
            />
            {passwordErrors.password && (
              <p className="client-field-error">{passwordErrors.password}</p>
            )}
          </div>
          <div className="client-field">
            <label htmlFor="settings-confirm-password">Confirm new password</label>
            <PasswordInput
              id="settings-confirm-password"
              value={passwordForm.password_confirmation}
              onChange={(e) => setPasswordForm({ ...passwordForm, password_confirmation: e.target.value })}
              autoComplete="new-password"
              hasError={!!passwordErrors.password_confirmation}
            />
            {passwordErrors.password_confirmation && (
              <p className="client-field-error">{passwordErrors.password_confirmation}</p>
            )}
          </div>
          <button type="submit" className="client-btn primary" disabled={passwordLoading}>
            {passwordLoading ? 'Updating…' : 'Update password'}
          </button>
        </form>
      </section>
    </div>
  )
}
