import { ArrowRight } from 'lucide-react'
import { Link } from 'react-router-dom'

export default function SiteButton({
  variant = 'primary',
  size = 'L',
  icon = true,
  children,
  to,
  href,
  type = 'button',
  className = '',
  ...rest
}) {
  const classes = ['site-btn', `site-btn--${variant}`, `site-btn--${String(size).toLowerCase()}`, className]
    .filter(Boolean)
    .join(' ')
  const inner = (
    <>
      <span>{children}</span>
      {icon ? <ArrowRight size={size === 'M' || size === 'm' ? 16 : 18} aria-hidden /> : null}
    </>
  )

  if (to) {
    return (
      <Link to={to} className={classes} {...rest}>
        {inner}
      </Link>
    )
  }

  if (href) {
    return (
      <a href={href} className={classes} {...rest}>
        {inner}
      </a>
    )
  }

  return (
    <button type={type} className={classes} {...rest}>
      {inner}
    </button>
  )
}
