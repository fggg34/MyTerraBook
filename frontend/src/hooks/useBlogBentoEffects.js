import { useEffect } from 'react'

export default function useBlogBentoEffects(enabled = true) {
  useEffect(() => {
    if (!enabled) return undefined

    const bento = document.getElementById('bento')
    if (!bento) return undefined

    const cards = [...bento.querySelectorAll('.bcard')]
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (reduce) {
      cards.forEach((card) => {
        card.style.opacity = '1'
        card.style.transform = 'none'
        card.style.filter = 'none'
      })
      return undefined
    }

    const froms = cards.map((card, i) => {
      if (card.classList.contains('featured')) return { x: -120, y: 60, r: -5 }
      const dirs = [
        { x: 90, y: -70, r: 4 },
        { x: 120, y: -45, r: 5 },
        { x: 75, y: 80, r: -4 },
        { x: 120, y: 65, r: 4 },
      ]
      return dirs[(i - 1) % dirs.length]
    })

    const clamp = (v, a, b) => Math.max(a, Math.min(b, v))
    const easeOutCubic = (t) => 1 - (1 - t) ** 3

    let ticking = false
    const render = () => {
      ticking = false
      const rect = bento.getBoundingClientRect()
      const vh = window.innerHeight || document.documentElement.clientHeight
      const p = clamp((vh * 0.94 - rect.top) / (vh * 0.62), 0, 1)

      cards.forEach((card, i) => {
        const d = i * 0.1
        const lp = clamp((p - d) / (1 - d), 0, 1)
        const e = easeOutCubic(lp)
        const f = froms[i]
        const x = (f.x * (1 - e)).toFixed(1)
        const y = (f.y * (1 - e)).toFixed(1)
        const rot = (f.r * (1 - e)).toFixed(2)
        const sc = (0.88 + 0.12 * e).toFixed(3)
        const blur = (1 - e) * 9
        card.style.transform = `translate3d(${x}px,${y}px,0) rotate(${rot}deg) scale(${sc})`
        card.style.opacity = (0.08 + 0.92 * e).toFixed(3)
        card.style.filter = blur > 0.25 ? `blur(${blur.toFixed(1)}px)` : 'none'
      })
    }

    const onScroll = () => {
      if (!ticking) {
        ticking = true
        requestAnimationFrame(render)
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
    render()

    const cleanups = []
    if (window.matchMedia('(pointer:fine)').matches) {
      cards.forEach((card) => {
        let raf = null
        let nx = 0
        let ny = 0

        const onMove = (ev) => {
          const r = card.getBoundingClientRect()
          nx = (ev.clientX - r.left) / r.width - 0.5
          ny = (ev.clientY - r.top) / r.height - 0.5
          if (!raf) raf = requestAnimationFrame(apply)
        }

        const onLeave = () => {
          card.style.setProperty('--px', '0px')
          card.style.setProperty('--py', '0px')
        }

        const apply = () => {
          raf = null
          card.style.setProperty('--px', `${(nx * 18).toFixed(1)}px`)
          card.style.setProperty('--py', `${(ny * 18).toFixed(1)}px`)
        }

        card.addEventListener('pointermove', onMove)
        card.addEventListener('pointerleave', onLeave)
        cleanups.push(() => {
          card.removeEventListener('pointermove', onMove)
          card.removeEventListener('pointerleave', onLeave)
        })
      })
    }

    return () => {
      window.removeEventListener('scroll', onScroll)
      window.removeEventListener('resize', onScroll)
      cards.forEach((card) => {
        card.style.transform = ''
        card.style.opacity = ''
        card.style.filter = ''
      })
      cleanups.forEach((fn) => fn())
    }
  }, [enabled])
}
