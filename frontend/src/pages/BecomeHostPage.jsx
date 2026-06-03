import { useEffect, useRef } from 'react'
import BecomeHostPageContent from '../components/become-host/BecomeHostPageContent'
import useBecomeHostEffects from '../hooks/useBecomeHostEffects'
import '../styles/become-host.css'

export default function BecomeHostPage() {
  const rootRef = useRef(null)

  useEffect(() => {
    document.body.classList.remove('homepage-active')
    document.body.classList.add('become-host-active')
    document.documentElement.style.scrollBehavior = 'smooth'
    return () => {
      document.body.classList.remove('become-host-active')
      document.documentElement.style.scrollBehavior = ''
    }
  }, [])

  useBecomeHostEffects(rootRef)

  return (
    <div className="become-host-page" ref={rootRef}>
      <BecomeHostPageContent />
    </div>
  )
}
