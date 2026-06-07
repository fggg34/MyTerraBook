import { useEffect } from 'react'

/** Enables homepage.css Tailwind-safe overrides on routes that use the public site chrome. */
export default function useHomepageBodyClass() {
  useEffect(() => {
    document.body.classList.add('homepage-active')
    return () => document.body.classList.remove('homepage-active')
  }, [])
}
