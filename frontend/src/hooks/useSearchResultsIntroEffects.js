import { useEffect } from 'react'
import { gsap } from 'gsap'

const SPLIT_TEXT_URL = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/SplitText.min.js'

function loadScript(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) {
      resolve()
      return
    }
    const script = document.createElement('script')
    script.src = src
    script.async = true
    script.onload = () => resolve()
    script.onerror = () => reject(new Error(`Failed to load ${src}`))
    document.head.appendChild(script)
  })
}

function revealStatic(root) {
  root.querySelectorAll('.reveal-title, .reveal-desc').forEach((el) => {
    el.style.opacity = '1'
  })
}

export default function useSearchResultsIntroEffects(rootRef, { ready = true } = {}) {
  useEffect(() => {
    const root = rootRef.current
    if (!root) return undefined

    if (!ready) {
      revealStatic(root)
      return undefined
    }

    let cancelled = false
    const splits = []

    const init = async () => {
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
      if (reduce) {
        revealStatic(root)
        return
      }

      try {
        await loadScript(SPLIT_TEXT_URL)
      } catch {
        revealStatic(root)
        return
      }

      if (cancelled || !window.SplitText) {
        revealStatic(root)
        return
      }

      const SplitText = window.SplitText
      gsap.registerPlugin(SplitText)

      const titles = Array.from(root.querySelectorAll('.reveal-title'))
      const descs = Array.from(root.querySelectorAll('.reveal-desc'))

      const safety = window.setTimeout(() => revealStatic(root), 2500)

      const animateDesc = (el) => {
        gsap.set(el, { opacity: 1 })
        const split = SplitText.create(el, { type: 'chars,words', charsClass: 'rd-char' })
        splits.push(split)
        gsap.from(split.chars, {
          duration: 0.9,
          opacity: 0,
          scale: 0,
          y: 60,
          rotationX: 160,
          transformOrigin: '0% 50% -50',
          ease: 'back',
          stagger: 0.012,
          onComplete: () => split.revert(),
        })
      }

      await (document.fonts ? document.fonts.ready : Promise.resolve())
      if (cancelled) return
      window.clearTimeout(safety)

      titles.forEach((el) => {
        gsap.set(el, { opacity: 1 })
        const fireNow = el.dataset.revealNow === '1'
        const split = SplitText.create(el, {
          type: 'lines',
          linesClass: 'rl-line',
          mask: 'lines',
          ignore: '.ri-count',
          autoSplit: false,
          onSplit(self) {
            return gsap.from(self.lines, {
              yPercent: 115,
              duration: 0.85,
              stagger: 0.12,
              ease: 'expo.out',
              delay: fireNow ? 0.05 : 0,
              scrollTrigger: fireNow
                ? undefined
                : {
                    trigger: el,
                    start: 'top 84%',
                    once: true,
                  },
            })
          },
        })
        splits.push(split)
      })

      descs.forEach((el) => {
        if (el.dataset.revealNow === '1') {
          animateDesc(el)
        }
      })
    }

    init()

    return () => {
      cancelled = true
      splits.forEach((split) => {
        split.revert?.()
      })
    }
  }, [rootRef, ready])
}
