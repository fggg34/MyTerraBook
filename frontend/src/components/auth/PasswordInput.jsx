import { useState } from 'react'

export default function PasswordInput({
  id,
  value,
  onChange,
  placeholder,
  autoComplete,
  hasError = false,
}) {
  const [visible, setVisible] = useState(false)

  return (
    <div className={`auth-input-wrap${hasError ? ' auth-input-wrap--error' : ''}`}>
      <input
        id={id}
        type={visible ? 'text' : 'password'}
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        autoComplete={autoComplete}
        className="auth-input"
      />
      <button
        type="button"
        className="auth-input-toggle"
        onClick={() => setVisible((v) => !v)}
        aria-label={visible ? 'Hide password' : 'Show password'}
      >
        {visible ? (
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
            <path d="M3 3l18 18" strokeLinecap="round" />
            <path d="M10.6 10.6A2 2 0 0 0 12 15a2 2 0 0 0 1.4-.4" strokeLinecap="round" />
            <path d="M6.7 6.7C4.6 8.1 3.1 10 2 12c2.5 4.5 6.5 7 10 7 1.4 0 2.8-.4 4.1-1.1M17.3 17.3C19.4 15.9 20.9 14 22 12c-2.5-4.5-6.5-7-10-7-1.1 0-2.2.2-3.2.6" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        ) : (
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" strokeLinecap="round" strokeLinejoin="round" />
            <circle cx="12" cy="12" r="2.5" />
          </svg>
        )}
      </button>
    </div>
  )
}
