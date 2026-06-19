import { useEffect } from 'react'

const DURATION_MS = 7000
const CLICK_ONLY_MQ = '(max-width: 980px)'
const DESKTOP_MQ = '(min-width: 981px)'

function prefersClickToOpen() {
  return window.matchMedia(CLICK_ONLY_MQ).matches
}

function syncOpenHeights(wrap, steps) {
  const section = wrap.closest('.how')

  if (!window.matchMedia(DESKTOP_MQ).matches) {
    wrap.style.removeProperty('--how-step-height')
    wrap.style.removeProperty('--how-step-body-height')
    section?.style.removeProperty('--how-section-height')
    return
  }

  const activeBefore = steps.findIndex((step) => step.classList.contains('active'))
  let maxStep = 0
  let maxBody = 0
  let maxSection = 0

  wrap.classList.add('how-steps--measure')

  steps.forEach((_, index) => {
    steps.forEach((step, idx) => {
      step.classList.toggle('active', idx === index)
    })

    const step = steps[index]
    const body = step.querySelector('.hstep-body')
    maxStep = Math.max(maxStep, step.offsetHeight)
    if (body) maxBody = Math.max(maxBody, body.offsetHeight)
    if (section) maxSection = Math.max(maxSection, section.offsetHeight)
  })

  wrap.classList.remove('how-steps--measure')

  const restoreIndex = activeBefore >= 0 ? activeBefore : 0
  steps.forEach((step, idx) => {
    step.classList.toggle('active', idx === restoreIndex)
  })

  wrap.style.setProperty('--how-step-height', `${maxStep}px`)
  wrap.style.setProperty('--how-step-body-height', `${maxBody}px`)
  section?.style.setProperty('--how-section-height', `${maxSection}px`)
}

function fillBar(bar, on) {
  if (!bar) return
  bar.style.transition = 'none'
  bar.style.height = '0%'
  void bar.offsetHeight
  if (on) {
    bar.style.transition = `height ${DURATION_MS}ms linear`
    bar.style.height = '100%'
  }
}

export default function useHowStepsEffects(wrapRef, stepCount, { duration = DURATION_MS, threshold = 0.35 } = {}) {
  useEffect(() => {
    const wrap = wrapRef.current
    if (!wrap || stepCount < 1) return undefined

    if (wrap.dataset.howFxInit === '1') return undefined
    wrap.dataset.howFxInit = '1'

    const steps = Array.from(wrap.querySelectorAll('.hstep'))
    const bars = steps.map((step) => step.querySelector('.hstep-bar'))
    let current = 0
    let timer = null
    let paused = false
    let started = false
    let resizeTimer = null

    const remeasure = () => syncOpenHeights(wrap, steps)

    const onResize = () => {
      clearTimeout(resizeTimer)
      resizeTimer = setTimeout(remeasure, 150)
    }

    const clearTimer = () => {
      if (timer) {
        clearTimeout(timer)
        timer = null
      }
    }

    const activate = (index) => {
      current = index
      steps.forEach((step, idx) => {
        step.classList.toggle('active', idx === index)
        step.setAttribute('aria-expanded', idx === index ? 'true' : 'false')
      })
      bars.forEach((bar, idx) => {
        if (idx !== index && bar) {
          bar.style.transition = 'height .3s ease'
          bar.style.height = '0%'
        }
      })
      fillBar(bars[index], !paused && !prefersClickToOpen())
      clearTimer()
      if (!paused && !prefersClickToOpen()) {
        timer = setTimeout(() => activate((index + 1) % steps.length), duration)
      }
    }

    const pauseAtCurrent = () => {
      paused = true
      clearTimer()
      const bar = bars[current]
      if (bar) {
        const h = window.getComputedStyle(bar).height
        bar.style.transition = 'none'
        bar.style.height = h
      }
    }

    const onWrapEnter = () => {
      if (prefersClickToOpen()) return
      pauseAtCurrent()
    }

    const resume = () => {
      if (prefersClickToOpen()) return
      paused = false
      activate(current)
    }

    const openStep = (index) => {
      paused = true
      clearTimer()
      current = index
      steps.forEach((step, idx) => {
        step.classList.toggle('active', idx === index)
        step.setAttribute('aria-expanded', idx === index ? 'true' : 'false')
      })
      bars.forEach((bar, idx) => {
        if (!bar) return
        if (idx !== index) {
          bar.style.transition = 'height .3s ease'
          bar.style.height = '0%'
        } else {
          bar.style.transition = 'none'
          bar.style.height = '100%'
        }
      })
    }

    const onStepHover = (index) => () => {
      if (prefersClickToOpen()) return
      openStep(index)
    }

    const onStepClick = (index) => () => {
      if (prefersClickToOpen()) {
        openStep(index)
        return
      }
      paused = false
      activate(index)
    }

    const onStepKeyDown = (index) => (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return
      event.preventDefault()
      onStepClick(index)()
    }

    const onWrapLeave = () => resume()

    steps.forEach((step, index) => {
      step.addEventListener('mouseenter', onStepHover(index))
      step.addEventListener('click', onStepClick(index))
      step.addEventListener('keydown', onStepKeyDown(index))
    })
    wrap.addEventListener('mouseenter', onWrapEnter)
    wrap.addEventListener('mouseleave', onWrapLeave)

    remeasure()
    window.addEventListener('resize', onResize)
    wrap.querySelectorAll('.hstep-media img').forEach((img) => {
      if (!img.complete) {
        img.addEventListener('load', remeasure, { once: true })
      }
    })

    let io
    if ('IntersectionObserver' in window) {
      io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting && !started) {
              started = true
              if (prefersClickToOpen()) {
                const bar = bars[0]
                if (bar) {
                  bar.style.transition = 'none'
                  bar.style.height = '100%'
                }
              } else {
                activate(0)
              }
            }
          })
        },
        { threshold },
      )
      io.observe(wrap)
    } else if (prefersClickToOpen()) {
      const bar = bars[0]
      if (bar) {
        bar.style.transition = 'none'
        bar.style.height = '100%'
      }
    } else {
      activate(0)
    }

    return () => {
      clearTimer()
      clearTimeout(resizeTimer)
      window.removeEventListener('resize', onResize)
      steps.forEach((step, index) => {
        step.removeEventListener('mouseenter', onStepHover(index))
        step.removeEventListener('click', onStepClick(index))
        step.removeEventListener('keydown', onStepKeyDown(index))
      })
      wrap.removeEventListener('mouseenter', onWrapEnter)
      wrap.removeEventListener('mouseleave', onWrapLeave)
      if (io) io.disconnect()
      wrap.style.removeProperty('--how-step-height')
      wrap.style.removeProperty('--how-step-body-height')
      wrap.closest('.how')?.style.removeProperty('--how-section-height')
      delete wrap.dataset.howFxInit
    }
  }, [wrapRef, stepCount, duration, threshold])
}
