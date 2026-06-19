import { resolveStorageUrl } from '../../api'

/**
 * CMS image without bundled demo fallbacks — avoids flashing static HTML demo assets
 * before admin-uploaded images are available.
 */
export default function CmsImage({
  src,
  alt = '',
  className = '',
  loading: loadingAttr = 'lazy',
  ...props
}) {
  const resolved = src ? resolveStorageUrl(src) : ''

  if (!resolved) {
    return (
      <div
        className={className}
        aria-hidden={alt ? undefined : true}
        role={alt ? 'img' : undefined}
        aria-label={alt || undefined}
        {...props}
      />
    )
  }

  return (
    <img
      src={resolved}
      alt={alt}
      className={className}
      loading={loadingAttr}
      {...props}
    />
  )
}
