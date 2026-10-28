export default function FigmaIcon({ name, size = 16, className = '' }) {
  return (
    <img
      className={className || undefined}
      src={`/figma/icon-${name}.svg`}
      width={size}
      height={size}
      alt=""
    />
  )
}
