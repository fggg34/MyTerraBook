import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function ClientSettingsPage() {
  return (
    <div>
      <div className="client-page-head">
        <h2>Settings</h2>
        <p>Update your profile and password.</p>
      </div>
      <AccountSettingsForms
        requirePhone
        showBecomeHost
        profileDescription="Keep your contact details up to date for booking confirmations and trip support."
      />
    </div>
  )
}
