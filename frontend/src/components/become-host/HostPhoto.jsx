export default function HostPhoto({ src, alt = '', className = '', style = {} }) {
  if (src) {
    return <img src={src} alt={alt} className={className} style={style} loading="lazy" />
  }
  return (
    <div
      className={className}
      style={{ ...style, background: 'linear-gradient(145deg, var(--cream-2), var(--cream))' }}
      aria-hidden={alt ? undefined : true}
      role={alt ? 'img' : undefined}
      aria-label={alt || undefined}
    />
  )
}
