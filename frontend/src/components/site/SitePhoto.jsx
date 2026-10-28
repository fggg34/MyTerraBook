import { useState } from 'react'

export default function SitePhoto({
  src,
  alt = '',
  className = '',
  ratio,
  loading = 'lazy',
}) {
  const [failed, setFailed] = useState(!src)
  const style = ratio ? { aspectRatio: ratio } : undefined

  return (
    <div className={`site-photo${failed ? ' site-photo--empty' : ''} ${className}`.trim()} style={style}>
      {src && !failed ? (
        <img
          src={src}
          alt={alt}
          loading={loading}
          onError={() => setFailed(true)}
        />
      ) : (
        <div className="site-photo__fallback" aria-hidden="true" />
      )}
    </div>
  )
}
