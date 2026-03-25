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
      ucBadge: 'Under construction',
      ucTitle: 'A smoother way to rent is almost here',
      ucSubtitle:
        'We are building a refined booking experience for MyTerraBook — easy pickup, fair pricing, and the right car when you need it.',
      ucHint: 'Thank you for your patience. Check back soon.',
      ucFooter: '© MyTerraBook Rentals',
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
