import { Link } from 'react-router-dom'

export default function BookingCheckoutFooter() {
  return (
    <footer className="rtb-checkout-ftr">
      <div className="rtb-wrap">
        <span className="ftr-copy">
          © {new Date().getFullYear()} <b>MyTerraBook ehf.</b> · Reykjavík, Iceland
        </span>
        <div className="ftr-links">
          <Link to="/help">Help center</Link>
          <Link to="/terms">Rental terms</Link>
          <Link to="/privacy">Privacy</Link>
          <Link to="/contact">Contact</Link>
        </div>
      </div>
    </footer>
  )
}
