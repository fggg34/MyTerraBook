import { Link } from 'react-router-dom'
import { CalendarRange, ChevronRight } from 'lucide-react'
import RequestToBookSubbar from './RequestToBookSubbar'

const STEPS = [
  'Browse campervans, cars, or guesthouses',
  'Choose your dates and trip details',
  'Continue to secure checkout from the listing',
]

const QUICK_LINKS = [
  { label: 'Campervans', to: '/campervans' },
  { label: 'Cars', to: '/cars' },
  { label: 'Guesthouses', to: '/guesthouses' },
]

export default function CheckoutEmptyState() {
  return (
    <div className="rtb-page">
      <RequestToBookSubbar backHref="/" backLabel="Back to home" />
      <div className="rtb-page-inner">
        <div className="rtb-wrap">
          <div className="rtb-empty">
            <div className="rtb-empty-card">
              <div className="rtb-empty-icon" aria-hidden>
                <CalendarRange />
              </div>
              <p className="rtb-empty-kick">Checkout</p>
              <h1>No trip selected yet</h1>
              <p className="rtb-empty-lead">
                This page opens when you start a booking from a listing. Pick your dates on a
                campervan, car, or guesthouse first, then continue here to complete checkout.
              </p>
              <ol className="rtb-empty-steps">
                {STEPS.map((step, index) => (
                  <li key={step} className="rtb-empty-step">
                    <span className="rtb-empty-step-num">{index + 1}</span>
                    <span>{step}</span>
                  </li>
                ))}
              </ol>
              <div className="rtb-empty-actions">
                <Link to="/" className="rtb-empty-primary">
                  Browse listings
                  <ChevronRight aria-hidden />
                </Link>
                <div className="rtb-empty-links">
                  {QUICK_LINKS.map((link) => (
                    <Link key={link.to} to={link.to} className="rtb-empty-link">
                      {link.label}
                    </Link>
                  ))}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
