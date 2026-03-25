import { useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import './UnderConstructionPage.css'

function CarSilhouetteIcon() {
  return (
    <svg
      className="uc-icon"
      viewBox="0 0 120 120"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      aria-hidden
    >
      <path
        d="M24 68h8l4-12h48l4 12h8v8H24V68zm12-20l8-24h32l8 24H36zm16 32a6 6 0 1 1-12 0 6 6 0 0 1 12 0zm40 0a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"
        fill="currentColor"
        opacity="0.92"
      />
      <path
        d="M20 76h80v6a4 4 0 0 1-4 4H24a4 4 0 0 1-4-4v-6z"
        fill="currentColor"
        opacity="0.45"
      />
    </svg>
  )
}

export default function UnderConstructionPage() {
  const { t } = useTranslation()

  useEffect(() => {
    document.body.classList.add('uc-body-dim')
    return () => document.body.classList.remove('uc-body-dim')
  }, [])

  return (
    <div className="uc-page">
      <div className="uc-road" aria-hidden>
        <div className="uc-road-inner">
          <div className="uc-road-lines" />
          <div className="uc-edge uc-edge--l" />
          <div className="uc-edge uc-edge--r" />
        </div>
      </div>

      <header className="uc-header">
        <div className="uc-logo">
          MyTerra<span>Book</span>
        </div>
      </header>

      <main className="uc-main">
        <CarSilhouetteIcon />
        <p className="uc-badge">{t('ucBadge')}</p>
        <h1 className="uc-title">{t('ucTitle')}</h1>
        <p className="uc-subtitle">{t('ucSubtitle')}</p>
        <div className="uc-progress" aria-hidden>
          <div className="uc-progress-bar" />
        </div>
        <p className="uc-hint">{t('ucHint')}</p>
      </main>

      <footer className="uc-footer">
        <p className="uc-footer-line">{t('ucFooter')}</p>
      </footer>
    </div>
  )
}
