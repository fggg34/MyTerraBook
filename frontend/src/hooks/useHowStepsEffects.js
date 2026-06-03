import { useEffect } from 'react'

const DURATION_MS = 7000

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

    const clearTimer = () => {
      if (timer) {
        clearTimeout(timer)
        timer = null
      }
    }

    const activate = (index) => {
      current = index
      steps.forEach((step, idx) => step.classList.toggle('active', idx === index))
      bars.forEach((bar, idx) => {
        if (idx !== index && bar) {
          bar.style.transition = 'height .3s ease'
          bar.style.height = '0%'
        }
      })
      fillBar(bars[index], !paused)
      clearTimer()
      if (!paused) {
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

    const onWrapEnter = () => pauseAtCurrent()

    const resume = () => {
      paused = false
      activate(current)
    }

    const onStepEnter = (index) => () => {
      paused = true
      clearTimer()
      current = index
      steps.forEach((step, idx) => step.classList.toggle('active', idx === index))
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

    const onStepClick = (index) => () => {
      paused = false
      activate(index)
    }

    const onWrapLeave = () => resume()

    steps.forEach((step, index) => {
      step.addEventListener('mouseenter', onStepEnter(index))
      step.addEventListener('click', onStepClick(index))
    })
    wrap.addEventListener('mouseenter', onWrapEnter)
    wrap.addEventListener('mouseleave', onWrapLeave)

    let io
    if ('IntersectionObserver' in window) {
      io = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting && !started) {
              started = true
              activate(0)
            }
          })
        },
        { threshold },
      )
      io.observe(wrap)
    } else {
      activate(0)
    }

    return () => {
      clearTimer()
      steps.forEach((step, index) => {
        step.removeEventListener('mouseenter', onStepEnter(index))
        step.removeEventListener('click', onStepClick(index))
      })
      wrap.removeEventListener('mouseenter', onWrapEnter)
      wrap.removeEventListener('mouseleave', onWrapLeave)
      if (io) io.disconnect()
      delete wrap.dataset.howFxInit
    }
  }, [wrapRef, stepCount, duration, threshold])
}
