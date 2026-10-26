import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostPasswordPage() {
  return (
    <AccountSettingsForms
      passwordOnly
      passwordBackHref="/host/settings"
    />
  )
}
