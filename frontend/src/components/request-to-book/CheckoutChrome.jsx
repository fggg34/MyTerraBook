import { Link } from 'react-router-dom'
import RequestToBookStepper from './RequestToBookStepper'

export default function CheckoutChrome({ steps, currentStep, onStepClick, helpPhone = '+354 787 0707' }) {
  const tel = String(helpPhone).replace(/[^\d+]/g, '')
  return (
    <header className="tb-check-head">
      <Link to="/" className="tb-check-logo" aria-label="MyTerraBook home">
        <img src="/figma/logo.svg" alt="" />
      </Link>
      <RequestToBookStepper steps={steps} currentStep={currentStep} onStepClick={onStepClick} />
      <a className="tb-check-help" href={`tel:${tel}`}>
        Need help? <strong>{helpPhone}</strong>
      </a>
    </header>
  )
}
