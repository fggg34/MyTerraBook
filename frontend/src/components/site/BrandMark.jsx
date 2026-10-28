export default function BrandMark({ className = '', size = 36 }) {
  return (
    <span className={`brand-mark ${className}`.trim()} style={{ width: size, height: size }} aria-hidden="true">
      <svg viewBox="0 0 36 36" fill="none">
        <rect width="36" height="36" rx="10" fill="currentColor" />
        <path
          d="M11 25V11h5.2c2.9 0 4.7 1.6 4.7 4.1 0 1.5-.7 2.7-1.9 3.3 1.5.5 2.4 1.8 2.4 3.6 0 2.7-2 4-5.1 4H11Zm3.1-8.1h1.7c1.4 0 2.1-.7 2.1-1.7s-.7-1.6-2.1-1.6h-1.7v3.3Zm0 2.5v3.6h2.1c1.5 0 2.3-.7 2.3-1.8 0-1.1-.8-1.8-2.3-1.8h-2.1Z"
          fill="#f6f2ec"
        />
      </svg>
    </span>
  )
}
