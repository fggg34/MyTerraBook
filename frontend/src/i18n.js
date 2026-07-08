import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

const resources = {
  en: {
    translation: {
      appTitle: 'MyTerraBook Rentals',
      home: 'Home',
      cars: 'Cars',
      dashboard: 'Dashboard',
      admin: 'Admin',
      login: 'Login',
      logout: 'Logout',
      findRentalCar: 'Find your rental car',
    },
  },
}

i18n.use(initReactI18next).init({
  resources,
  lng: 'en',
  fallbackLng: 'en',
  interpolation: { escapeValue: false },
})

export default i18n
