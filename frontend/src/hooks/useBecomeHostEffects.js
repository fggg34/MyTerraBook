import { useEffect } from 'react'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

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
  root.querySelectorAll('.reveal-title, .reveal-desc, .bento .fcard').forEach((el) => {
    el.style.opacity = '1'
  })
}

export default function useBecomeHostEffects(rootRef) {
  useEffect(() => {
    document.documentElement.classList.add('reveal-on')

    const root = rootRef.current
    if (!root) return undefined

    let megaTimer = null
    let howTimer = null
    let howCur = 0
    let splitTextLoaded = false

    const navWhy = root.querySelector('#navWhy')
    const navLink = navWhy?.querySelector('.nav-link')
    const onMegaEnter = () => {
      clearTimeout(megaTimer)
      navWhy?.classList.add('open')
    }
    const onMegaLeave = () => {
      megaTimer = setTimeout(() => navWhy?.classList.remove('open'), 120)
    }
    const onMegaClick = (event) => {
      event.stopPropagation()
      navWhy?.classList.toggle('open')
    }
    const onDocClick = () => navWhy?.classList.remove('open')

    if (navWhy && navLink) {
      navWhy.addEventListener('mouseenter', onMegaEnter)
      navWhy.addEventListener('mouseleave', onMegaLeave)
      navLink.addEventListener('click', onMegaClick)
      document.addEventListener('click', onDocClick)
    }

    const tabs = root.querySelector('#howTabs')
    const stage = root.querySelector('#howStage')
    const tabEls = tabs ? Array.from(tabs.querySelectorAll('.how-tab')) : []
    const slides = stage ? Array.from(stage.querySelectorAll('.how-slide')) : []
    const HOW_DUR = 6000

    const showHow = (index) => {
      howCur = (index + tabEls.length) % tabEls.length
      tabEls.forEach((tab, n) => tab.classList.toggle('active', n === howCur))
      slides.forEach((slide, n) => slide.classList.toggle('active', n === howCur))
      const bar = tabEls[howCur]?.querySelector('.bar')
      if (bar) {
        bar.style.animation = 'none'
        void bar.offsetWidth
        bar.style.animation = ''
      }
    }

    const scheduleHow = () => {
      clearTimeout(howTimer)
      howTimer = setTimeout(() => {
        showHow(howCur + 1)
        scheduleHow()
      }, HOW_DUR)
    }

    const goHow = (index) => {
      showHow(index)
      scheduleHow()
    }

    const onHowClick = (event) => {
      const btn = event.target.closest('.how-tab')
      if (!btn) return
      goHow(parseInt(btn.getAttribute('data-tab'), 10))
    }

    const onStageEnter = () => clearTimeout(howTimer)
    const onStageLeave = () => scheduleHow()

    if (tabs && stage && tabEls.length) {
      tabs.addEventListener('click', onHowClick)
      stage.addEventListener('mouseenter', onStageEnter)
      stage.addEventListener('mouseleave', onStageLeave)
      showHow(0)
      scheduleHow()
    }

    const faqList = root.querySelector('#faqList')
    const faqItems = faqList ? Array.from(faqList.querySelectorAll('.faq-item')) : []
    const faqHandlers = faqItems.map((item) => {
      const btn = item.querySelector('.faq-q')
      const handler = () => {
        const isOpen = item.classList.contains('open')
        faqItems.forEach((other) => {
          other.classList.remove('open')
          other.querySelector('.faq-q')?.setAttribute('aria-expanded', 'false')
        })
        if (!isOpen) {
          item.classList.add('open')
          btn?.setAttribute('aria-expanded', 'true')
        }
      }
      btn?.addEventListener('click', handler)
      return { btn, handler }
    })

    let cancelled = false
    const spinCleanups = []

    const initMotion = async () => {
      const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
      if (reduce) {
        revealStatic(root)
        return
      }

      try {
        gsap.registerPlugin(ScrollTrigger)
        await loadScript(SPLIT_TEXT_URL)
        splitTextLoaded = !!window.SplitText
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
      const bento = Array.from(root.querySelectorAll('.bento .fcard'))

      const safety = window.setTimeout(() => revealStatic(root), 2500)

      const animateDesc = (el) => {
        gsap.set(el, { opacity: 1 })
        const split = SplitText.create(el, { type: 'chars,words', charsClass: 'rd-char' })
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
        SplitText.create(el, {
          type: 'lines',
          linesClass: 'rl-line',
          mask: 'lines',
          autoSplit: true,
          onSplit(self) {
            return gsap.from(self.lines, {
              yPercent: 115,
              duration: 0.85,
              stagger: 0.12,
              ease: 'expo.out',
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
      })

      descs.forEach((el) => {
        if (el.dataset.revealNow === '1') {
          animateDesc(el)
        } else {
          ScrollTrigger.create({
            trigger: el,
            start: 'top 86%',
            once: true,
            onEnter: () => animateDesc(el),
          })
        }
      })

      if (bento.length) {
        gsap.set(bento, { opacity: 1 })
        const dirs = [
          { x: -90, y: 0, rotation: -3 },
          { x: 90, y: 0, rotation: 3 },
          { x: 0, y: 90, rotation: -2 },
          { x: 0, y: 90, rotation: 2 },
        ]
        bento.forEach((card, i) => {
          const d = dirs[i % dirs.length]
          const bottom = i >= 2
          gsap.from(card, {
            opacity: 0,
            x: d.x,
            y: d.y,
            rotation: d.rotation,
            duration: bottom ? 1.5 : 0.9,
            ease: bottom ? 'power3.out' : 'back.out(1.4)',
            scrollTrigger: {
              trigger: card,
              start: 'top 92%',
              once: true,
            },
          })
        })
      }

      Array.from(root.querySelectorAll('.cta-spin')).forEach((label) => {
        const trigger = label.closest('button, a') || label
        gsap.set(label, { perspective: 500 })
        const onEnter = () => {
          if (label._spinning) return
          label._spinning = true
          const split = SplitText.create(label, { type: 'chars', charsClass: 'cta-char' })
          gsap.fromTo(
            split.chars,
            { rotationX: 0 },
            {
              rotationX: 360,
              duration: 0.6,
              ease: 'power2.out',
              stagger: 0.045,
              transformOrigin: '50% 50% -16',
              onComplete: () => {
                split.revert()
                label._spinning = false
              },
            },
          )
        }
        trigger.addEventListener('mouseenter', onEnter)
        spinCleanups.push(() => trigger.removeEventListener('mouseenter', onEnter))
      })
    }

    initMotion()

    return () => {
      cancelled = true
      document.documentElement.classList.remove('reveal-on')
      clearTimeout(megaTimer)
      clearTimeout(howTimer)
      if (navWhy && navLink) {
        navWhy.removeEventListener('mouseenter', onMegaEnter)
        navWhy.removeEventListener('mouseleave', onMegaLeave)
        navLink.removeEventListener('click', onMegaClick)
        document.removeEventListener('click', onDocClick)
      }
      if (tabs && stage) {
        tabs.removeEventListener('click', onHowClick)
        stage.removeEventListener('mouseenter', onStageEnter)
        stage.removeEventListener('mouseleave', onStageLeave)
      }
      faqHandlers.forEach(({ btn, handler }) => btn?.removeEventListener('click', handler))
      spinCleanups.forEach((fn) => fn())
      ScrollTrigger.getAll().forEach((trigger) => trigger.kill())
    }
  }, [rootRef])
}
