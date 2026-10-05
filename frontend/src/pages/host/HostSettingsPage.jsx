import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostSettingsPage() {
  return (
    <AccountSettingsForms
      requirePhone
      showCurrency
      showKennitala
      showCompanyLogo
      profileDescription="Update your contact details, partner logo, and the currency used for your listing prices."
    />
  )
}
