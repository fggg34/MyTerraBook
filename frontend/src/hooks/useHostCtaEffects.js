import { useEffect } from 'react'

const SPARK = [0.45, 0.6, 0.52, 0.72, 0.66, 0.85, 0.78, 1.0]

const PINGS = [
  { n: 'Anna', c: 'Reykjavík', a: '+€240' },
  { n: 'Jón', c: 'Akureyri', a: '+€180' },
  { n: 'Eva', c: 'Vík', a: '+€310' },
  { n: 'Ólafur', c: 'Höfn', a: '+€155' },
  { n: 'Sara', c: 'Selfoss', a: '+€275' },
]

export default function useHostCtaEffects(sectionRef) {
  useEffect(() => {
    const sec = sectionRef.current
    if (!sec) return undefined

    if (sec.dataset.hostFxInit === '1') return undefined
    sec.dataset.hostFxInit = '1'

    const heAmt = sec.querySelector('#heAmt')
    const spark = sec.querySelector('#hcSpark')
    const stage = sec.querySelector('.host-stage')
    const photoImg = sec.querySelector('.host-photo img')
    const vanImg = sec.querySelector('.host-van img')
    const pingHost = sec.querySelector('#hostPings')
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (spark && !spark.children.length) {
      SPARK.forEach(() => {
        const bar = document.createElement('i')
        bar.style.height = '4px'
        spark.appendChild(bar)
      })
    }

    let pingIx = 0
    let pingTimer = null
    let io
    let scrollRaf = false
    let revealDoneTimer = null

    const spawnPing = () => {
      if (reduce || !pingHost) return
      const d = PINGS[pingIx % PINGS.length]
      pingIx += 1
      const el = document.createElement('div')
      el.className = 'host-ping'
      el.style.left = `${32 + Math.random() * 40}%`
      el.innerHTML = `<span class="pg-av">${d.n.charAt(0)}</span><span>${d.n} · ${d.c}</span><span class="pg-amt">${d.a}</span>`
      pingHost.appendChild(el)
      requestAnimationFrame(() => el.classList.add('show'))
      window.setTimeout(() => el.remove(), 4400)
    }

    const parallax = () => {
      scrollRaf = false
      if (!stage) return
      const rect = stage.getBoundingClientRect()
      const vh = window.innerHeight || 1
      const prog = Math.max(-1, Math.min(1, (rect.top + rect.height / 2 - vh / 2) / vh))
      if (photoImg) photoImg.style.setProperty('--ph-par', `${(prog * -46).toFixed(1)}px`)
      if (vanImg) vanImg.style.setProperty('--van-par', `${(prog * 26).toFixed(1)}px`)
    }

    const onParallaxScroll = () => {
      if (!scrollRaf) {
        scrollRaf = true
        requestAnimationFrame(parallax)
      }
    }

    const run = () => {
      if (sec.dataset.hostRun === '1') return
      sec.dataset.hostRun = '1'
      sec.classList.add('revealed')
      revealDoneTimer = window.setTimeout(() => sec.classList.add('reveal-done'), 1500)

      if (spark) {
        spark.querySelectorAll('i').forEach((bar, i) => {
          const h = 8 + SPARK[i] * 30
          if (reduce) {
            bar.style.height = `${h.toFixed(0)}px`
            return
          }
          window.setTimeout(() => {
            bar.style.transition = 'height .5s cubic-bezier(.33,1,.68,1)'
            bar.style.height = `${h.toFixed(0)}px`
          }, 500 + i * 70)
        })
      }

      if (heAmt) {
        if (reduce) {
          heAmt.textContent = '€1,900'
        } else {
          const target = 1900
          const t0 = performance.now()
          const dur = 1100
          const step = (now) => {
            const p = Math.min((now - t0) / dur, 1)
            const e = 1 - (1 - p) ** 3
            heAmt.textContent = `€${Math.round(target * e).toLocaleString('en-US')}`
            if (p < 1) requestAnimationFrame(step)
          }
          requestAnimationFrame(step)
        }
      }

      if (!reduce) {
        window.setTimeout(spawnPing, 1200)
        pingTimer = window.setInterval(spawnPing, 2600)
        parallax()
        window.addEventListener('scroll', onParallaxScroll, { passive: true })
      }
    }

    const maybeReveal = () => {
      if (sec.dataset.hostRun === '1') return true
      const rect = sec.getBoundingClientRect()
      const vh = window.innerHeight || document.documentElement.clientHeight
      if (rect.top < vh * 0.92 && rect.bottom > vh * 0.04) {
        run()
        if (io) io.disconnect()
        window.removeEventListener('scroll', maybeReveal)
        window.removeEventListener('resize', maybeReveal)
        return true
      }
      return false
    }

    if ('IntersectionObserver' in window) {
      io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) maybeReveal()
          })
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
      )
      io.observe(sec)
    }

    window.addEventListener('scroll', maybeReveal, { passive: true })
    window.addEventListener('resize', maybeReveal)
    maybeReveal()
    const t1 = window.setTimeout(maybeReveal, 120)
    const t2 = window.setTimeout(maybeReveal, 500)

    return () => {
      if (io) io.disconnect()
      if (pingTimer) window.clearInterval(pingTimer)
      if (revealDoneTimer) window.clearTimeout(revealDoneTimer)
      window.removeEventListener('scroll', maybeReveal)
      window.removeEventListener('resize', maybeReveal)
      window.removeEventListener('scroll', onParallaxScroll)
      delete sec.dataset.hostFxInit
      delete sec.dataset.hostRun
    }
  }, [sectionRef])
}
