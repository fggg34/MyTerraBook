import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostSettingsPage() {
  return (
    <AccountSettingsForms
      requirePhone
      showCurrency
      profileDescription="Update your contact details and the currency used for your listing prices."
    />
  )
}
