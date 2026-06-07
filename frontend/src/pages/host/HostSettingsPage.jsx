import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostSettingsPage() {
  return (
    <AccountSettingsForms
      requirePhone
      profileDescription="Update the contact details shown on your host account."
    />
  )
}
