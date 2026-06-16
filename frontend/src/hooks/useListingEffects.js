import { useEffect } from 'react'

export default function useListingEffects(rootRef, { enabled = true }) {
  useEffect(() => {
    if (!enabled) return undefined

    const root = rootRef.current
    if (!root) return undefined

    document.body.classList.add('listing-active')

    const beds = [...root.querySelectorAll('.bedcard')]
    const shots = [...root.querySelectorAll('.sleep-shot')]
    const sleepCap = root.querySelector('#sleepCap')
    const onBedClick = (e) => {
      const b = e.currentTarget
      const i = Number(b.dataset.bed)
      beds.forEach((x) => x.classList.toggle('sel', x === b))
      shots.forEach((s, k) => s.classList.toggle('active', k === i))
      const cap = b.dataset.cap
      if (sleepCap && cap) sleepCap.textContent = cap
    }
    beds.forEach((b) => b.addEventListener('click', onBedClick))

    const fills = [...root.querySelectorAll('.ro-fill')]
    fills.forEach((f) => {
      f.style.transition = 'width .8s cubic-bezier(.33,1,.68,1)'
    })
    let barsFilled = false
    const fillBars = () => {
      if (barsFilled) return
      barsFilled = true
      fills.forEach((f) => {
        f.style.width = `${f.dataset.w || 0}%`
      })
    }
    const rsEl = root.querySelector('.rev-overall')
    const maybeFill = () => {
      if (!rsEl) return
      const r = rsEl.getBoundingClientRect()
      if (r.top < window.innerHeight - 60 && r.bottom > 0) fillBars()
    }
    let ro
    if ('IntersectionObserver' in window && rsEl) {
      ro = new IntersectionObserver(
        (es) => {
          es.forEach((e) => {
            if (e.isIntersecting) {
              fillBars()
              ro.disconnect()
            }
          })
        },
        { threshold: 0.25 },
      )
      ro.observe(rsEl)
    }
    window.addEventListener('scroll', maybeFill, { passive: true })
    maybeFill()
    setTimeout(maybeFill, 400)

    const bpOverlay = root.querySelector('#bpOverlay')
    const bpLink = root.querySelector('#bookProcessLink')
    const bpClose = root.querySelector('#bpClose')
    const bpCta = root.querySelector('#bpCta')
    const bpOpen = () => {
      bpOverlay?.classList.add('open')
      bpOverlay?.setAttribute('aria-hidden', 'false')
      document.body.style.overflow = 'hidden'
    }
    const bpHide = () => {
      bpOverlay?.classList.remove('open')
      bpOverlay?.setAttribute('aria-hidden', 'true')
      document.body.style.overflow = ''
    }
    bpLink?.addEventListener('click', bpOpen)
    bpClose?.addEventListener('click', bpHide)
    const onBpOverlay = (e) => {
      if (e.target === bpOverlay) bpHide()
    }
    bpOverlay?.addEventListener('click', onBpOverlay)
    const onEsc = (e) => {
      if (e.key === 'Escape' && bpOverlay?.classList.contains('open')) bpHide()
    }
    document.addEventListener('keydown', onEsc)

    return () => {
      document.body.classList.remove('listing-active')
      document.body.style.overflow = ''
      window.removeEventListener('scroll', maybeFill)
      document.removeEventListener('keydown', onEsc)
      beds.forEach((b) => b.removeEventListener('click', onBedClick))
      ro?.disconnect()
    }
  }, [enabled, rootRef])
}
