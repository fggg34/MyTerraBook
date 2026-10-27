import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostPasswordPage() {
  return (
    <AccountSettingsForms
      panel
      passwordOnly
      passwordBackHref="/host/settings"
    />
  )
}
