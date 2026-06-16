import LucideIcon, { hasIcon } from './iconCatalog'

const uploadIconStyle = (iconUrl, size) => ({
  display: 'inline-block',
  width: size,
  height: size,
  backgroundColor: 'currentColor',
  WebkitMaskImage: `url("${iconUrl}")`,
  maskImage: `url("${iconUrl}")`,
  WebkitMaskSize: 'contain',
  maskSize: 'contain',
  WebkitMaskRepeat: 'no-repeat',
  maskRepeat: 'no-repeat',
  WebkitMaskPosition: 'center',
  maskPosition: 'center',
})

/**
 * Renders a catalog icon (Lucide key or custom upload) at a consistent size.
 * Used on listing pages, host editor, and anywhere catalog items appear.
 */
export default function CatalogIcon({
  name,
  iconUrl,
  size = 20,
  strokeWidth = 1.8,
  imgClassName = 'catalog-icon-img',
  fallback = 'check',
}) {
  if (iconUrl) {
    return (
      <span
        aria-hidden
        className={imgClassName}
        style={uploadIconStyle(iconUrl, size)}
      />
    )
  }

  const iconName = name && hasIcon(name) ? name : fallback

  return <LucideIcon name={iconName} size={size} strokeWidth={strokeWidth} />
}
