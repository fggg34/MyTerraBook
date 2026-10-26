import AccountSettingsForms from '../../components/account/AccountSettingsForms'

export default function HostSettingsPage() {
  return (
    <AccountSettingsForms
      requirePhone
      showCurrency
      showKennitala
      showCompanyLogo
      showRentalTerms
      passwordHref="/host/settings/password"
      profileDescription="Update your contact details, partner logo, contract terms, and the currency used for your listing prices."
    />
  )
}
