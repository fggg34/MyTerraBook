import { Link } from 'react-router-dom'
import { ChevronLeft, Lock } from 'lucide-react'

export default function RequestToBookSubbar({ backHref, backLabel = 'Back to listing' }) {
  return (
    <div className="subbar">
      <div className="rtb-wrap">
        <Link to={backHref} className="crumb">
          <ChevronLeft aria-hidden />
          {backLabel}
        </Link>
        <span className="secure">
          <Lock aria-hidden />
          Secure checkout
        </span>
      </div>
    </div>
  )
}
