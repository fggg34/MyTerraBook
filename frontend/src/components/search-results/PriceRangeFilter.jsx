import { useState } from 'react'

export default function PriceRangeFilter({
  min,
  max,
  step,
  valueMin,
  valueMax,
  onChange,
  formatPrice,
  perLabel = 'day',
}) {
  const [activeThumb, setActiveThumb] = useState(null)
  const format = formatPrice ?? ((amount) => `€${Math.round(amount).toLocaleString('en-US')}`)

  const handleMinChange = (nextMin) => {
    const clampedMin = Math.max(min, Math.min(nextMin, valueMax))
    onChange?.({ minPrice: clampedMin, maxPrice: valueMax })
  }

  const handleMaxChange = (nextMax) => {
    const clampedMax = Math.min(max, Math.max(nextMax, valueMin))
    onChange?.({ minPrice: valueMin, maxPrice: clampedMax })
  }

  const minPercent = max > min ? ((valueMin - min) / (max - min)) * 100 : 0
  const maxPercent = max > min ? ((valueMax - min) / (max - min)) * 100 : 100

  return (
    <div className="price-range-filter">
      <p className="price-range-value">
        {format(valueMin)} – {format(valueMax)}
        <span className="price-range-per"> / {perLabel}</span>
      </p>

      <div
        className={`price-range-track ${activeThumb ? 'is-dragging' : ''}`}
        style={{
          '--range-min': `${minPercent}%`,
          '--range-max': `${maxPercent}%`,
        }}
      >
        <input
          type="range"
          className={`price-range-input price-range-input--min ${activeThumb === 'min' ? 'is-active' : ''}`}
          min={min}
          max={max}
          step={step}
          value={valueMin}
          onPointerDown={() => setActiveThumb('min')}
          onPointerUp={() => setActiveThumb(null)}
          onPointerCancel={() => setActiveThumb(null)}
          onChange={(e) => handleMinChange(Number(e.target.value))}
          aria-label={`Minimum price per ${perLabel}`}
        />
        <input
          type="range"
          className={`price-range-input price-range-input--max ${activeThumb === 'max' ? 'is-active' : ''}`}
          min={min}
          max={max}
          step={step}
          value={valueMax}
          onPointerDown={() => setActiveThumb('max')}
          onPointerUp={() => setActiveThumb(null)}
          onPointerCancel={() => setActiveThumb(null)}
          onChange={(e) => handleMaxChange(Number(e.target.value))}
          aria-label={`Maximum price per ${perLabel}`}
        />
      </div>

      <div className="price-range-bounds">
        <span>{format(min)}</span>
        <span>{format(max)}</span>
      </div>
    </div>
  )
}
