import { useEffect } from 'react'

export default function useListingEffects(rootRef, { priceFrom = 94, onBook }) {
  useEffect(() => {
    document.body.classList.add('listing-active')

    const root = rootRef.current
    if (!root) return undefined

    const tabs = [...root.querySelectorAll('.tab')]
    const panels = [...root.querySelectorAll('.tpanel')]
    const card = root.querySelector('#tabcard')
    let cur = 0

    const measure = () => {
      const p = panels[cur]
      if (card && p) card.style.height = `${p.offsetHeight}px`
    }

    const go = (i) => {
      cur = (i + panels.length) % panels.length
      tabs.forEach((t, k) => t.classList.toggle('active', k === cur))
      panels.forEach((p, k) => p.classList.toggle('active', k === cur))
      requestAnimationFrame(measure)
    }

    const onTabClick = (e) => {
      go(Number(e.currentTarget.dataset.i))
    }
    tabs.forEach((t) => t.addEventListener('click', onTabClick))

    const desc = root.querySelector('#desc')
    const showMore = root.querySelector('#showMore')
    const onShowMore = () => {
      desc?.classList.toggle('open')
      if (showMore && desc) {
        const label = showMore.querySelector('[data-label]') || showMore.firstChild
        if (label) label.textContent = desc.classList.contains('open') ? 'Show less ' : 'Show more '
      }
      requestAnimationFrame(measure)
    }
    showMore?.addEventListener('click', onShowMore)

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
      requestAnimationFrame(measure)
    }
    beds.forEach((b) => b.addEventListener('click', onBedClick))

    const onResize = () => measure()
    window.addEventListener('resize', onResize)
    if (document.fonts?.ready) document.fonts.ready.then(measure)
    window.addEventListener('load', measure)
    setTimeout(measure, 300)

    if (panels.length) go(0)

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

    const simTrack = root.querySelector('#simTrack')
    const simPrev = root.querySelector('#simPrev')
    const simNext = root.querySelector('#simNext')
    const simUpdate = () => {
      if (!simTrack) return
      const max = simTrack.scrollWidth - simTrack.clientWidth - 2
      if (simPrev) simPrev.disabled = simTrack.scrollLeft <= 2
      if (simNext) simNext.disabled = simTrack.scrollLeft >= max
    }
    const simStep = () => {
      const c = simTrack?.querySelector('.pcard')
      return c ? c.getBoundingClientRect().width + 22 : 320
    }
    const onSimPrev = () => simTrack?.scrollBy({ left: -simStep(), behavior: 'smooth' })
    const onSimNext = () => simTrack?.scrollBy({ left: simStep(), behavior: 'smooth' })
    simPrev?.addEventListener('click', onSimPrev)
    simNext?.addEventListener('click', onSimNext)
    simTrack?.addEventListener('scroll', simUpdate, { passive: true })
    window.addEventListener('resize', simUpdate)
    simUpdate()

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
    bpCta?.addEventListener('click', () => {
      bpHide()
      onBook?.()
    })
    const onEsc = (e) => {
      if (e.key === 'Escape' && bpOverlay?.classList.contains('open')) bpHide()
    }
    document.addEventListener('keydown', onEsc)

    const calWrap = root.querySelector('#dateWrap')
    let calCleanup = () => {}
    if (calWrap) {
      const field = root.querySelector('#dateField')
      const pop = root.querySelector('#calPop')
      const grid = root.querySelector('#calGrid')
      const calTitle = root.querySelector('#calTitle')
      const calPrevBtn = root.querySelector('#calPrev')
      const calNextBtn = root.querySelector('#calNext')
      const calNights = root.querySelector('#calNights')
      const calClear = root.querySelector('#calClear')
      const dfStart = root.querySelector('#dfStart')
      const dfEnd = root.querySelector('#dfEnd')
      const rateL = root.querySelector('#rateL')
      const rateR = root.querySelector('#rateR')
      const RATE = Number(priceFrom) || 94
      const MON = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
      const M3 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const view = new Date(today.getFullYear(), today.getMonth(), 1)
      let dStart = null
      let dEnd = null

      const same = (a, b) => a && b && a.getTime() === b.getTime()
      const fmt = (d) => `${d.getDate()} ${M3[d.getMonth()]}`
      const euro = (n) => `€${n.toLocaleString('en-IE')}`

      const renderCal = () => {
        if (!grid || !calTitle) return
        calTitle.textContent = `${MON[view.getMonth()]} ${view.getFullYear()}`
        if (calPrevBtn) {
          calPrevBtn.disabled = view.getFullYear() === today.getFullYear() && view.getMonth() === today.getMonth()
        }
        grid.innerHTML = ''
        const startDow = (new Date(view.getFullYear(), view.getMonth(), 1).getDay() + 6) % 7
        const days = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate()
        for (let i = 0; i < startDow; i++) {
          const e = document.createElement('div')
          e.className = 'cal-cell empty'
          grid.appendChild(e)
        }
        for (let d = 1; d <= days; d++) {
          const date = new Date(view.getFullYear(), view.getMonth(), d)
          const cell = document.createElement('button')
          cell.type = 'button'
          cell.className = 'cal-cell'
          cell.textContent = String(d)
          if (date < today) cell.classList.add('past')
          else {
            if (same(date, today)) cell.classList.add('today')
            if (same(date, dStart) || same(date, dEnd)) cell.classList.add('sel')
            if (dStart && dEnd) {
              if (same(date, dStart)) cell.classList.add('range-start')
              if (same(date, dEnd)) cell.classList.add('range-end')
              if (date > dStart && date < dEnd) cell.classList.add('inrange')
            }
            cell.addEventListener('click', (ev) => {
              ev.stopPropagation()
              if (!dStart || (dStart && dEnd) || date < dStart) {
                dStart = date
                dEnd = null
              } else if (!same(date, dStart)) {
                dEnd = date
              }
              updateCal()
              renderCal()
            })
          }
          grid.appendChild(cell)
        }
      }

      const updateCal = () => {
        if (dfStart) {
          dfStart.textContent = dStart ? fmt(dStart) : 'Add date'
          dfStart.classList.toggle('set', !!dStart)
        }
        if (dfEnd) {
          dfEnd.textContent = dEnd ? fmt(dEnd) : 'Add date'
          dfEnd.classList.toggle('set', !!dEnd)
        }
        if (dStart && dEnd) {
          const n = Math.round((dEnd - dStart) / 86400000)
          const total = n * RATE
          if (calNights) {
            calNights.innerHTML = `<b>${n} night${n > 1 ? 's' : ''}</b> <span>· ${euro(total)} total</span>`
          }
          if (rateL) rateL.textContent = `Total · ${n} night${n > 1 ? 's' : ''}`
          if (rateR) rateR.innerHTML = `<b>${euro(total)}.00</b>`
        } else {
          if (calNights) {
            calNights.innerHTML = dStart
              ? '<span>Choose your drop-off date</span>'
              : '<span>Select your dates</span>'
          }
          if (rateL) rateL.textContent = rateL.dataset.default || 'Daily rate'
          if (rateR) rateR.innerHTML = `From <b>€${RATE.toFixed(2)}</b>`
        }
      }

      const openCal = () => {
        pop?.classList.add('open')
        field?.classList.add('open')
      }
      const closeCal = () => {
        pop?.classList.remove('open')
        field?.classList.remove('open')
      }
      const onField = (e) => {
        e.stopPropagation()
        if (pop?.classList.contains('open')) closeCal()
        else openCal()
      }
      const onDoc = () => {
        if (pop?.classList.contains('open')) closeCal()
      }
      field?.addEventListener('click', onField)
      pop?.addEventListener('click', (e) => e.stopPropagation())
      document.addEventListener('click', onDoc)
      calPrevBtn?.addEventListener('click', (e) => {
        e.stopPropagation()
        view.setMonth(view.getMonth() - 1)
        renderCal()
      })
      calNextBtn?.addEventListener('click', (e) => {
        e.stopPropagation()
        view.setMonth(view.getMonth() + 1)
        renderCal()
      })
      calClear?.addEventListener('click', (e) => {
        e.stopPropagation()
        dStart = null
        dEnd = null
        updateCal()
        renderCal()
      })
      if (rateL) rateL.dataset.default = rateL.textContent
      renderCal()
      updateCal()

      const bookBtn = root.querySelector('#listingBookBtn')
      const onBookClick = () => onBook?.({ pickupDate: dStart, dropoffDate: dEnd })
      bookBtn?.addEventListener('click', onBookClick)

      calCleanup = () => {
        document.removeEventListener('click', onDoc)
        bookBtn?.removeEventListener('click', onBookClick)
      }
    }

    return () => {
      document.body.classList.remove('listing-active')
      document.body.style.overflow = ''
      window.removeEventListener('resize', onResize)
      window.removeEventListener('scroll', maybeFill)
      window.removeEventListener('resize', simUpdate)
      document.removeEventListener('keydown', onEsc)
      tabs.forEach((t) => t.removeEventListener('click', onTabClick))
      showMore?.removeEventListener('click', onShowMore)
      beds.forEach((b) => b.removeEventListener('click', onBedClick))
      ro?.disconnect()
      calCleanup()
    }
  }, [rootRef, priceFrom, onBook])
}
