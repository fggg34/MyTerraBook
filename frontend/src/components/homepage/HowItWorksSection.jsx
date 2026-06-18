import { Fragment, useRef } from 'react'
import { resolveCmsImage } from '../../api'
import useHowStepsEffects from '../../hooks/useHowStepsEffects'

const STEP_IMAGE_FALLBACKS = [
  '/images/homepage/stay-hofn.jpg',
  '/images/homepage/why-photo.jpg',
  '/images/homepage/hero.jpg',
]

export default function HowItWorksSection({ heading, steps = [] }) {
  const wrapRef = useRef(null)
  useHowStepsEffects(wrapRef, steps.length)

  return (
    <section className="how">
      <div className="wrap">
        <div className="how-head">{heading && <h2>{heading}</h2>}</div>
        <div className="how-steps" id="howSteps" ref={wrapRef}>
          {steps.map((step, index) => (
            <div
              key={step.num}
              className={`hstep ${index === 0 ? 'active' : ''}`}
              data-step={index}
              role="button"
              tabIndex={0}
              aria-expanded={index === 0}
            >
              <span className="hstep-bar" />
              <div className="hstep-num">{step.num}</div>
              <div className="hstep-title">{step.title}</div>
              <div className="hstep-body">
                <p className="hstep-desc">{step.description}</p>
                <div className="hstep-media">
                  <img
                    src={resolveCmsImage(step.image, STEP_IMAGE_FALLBACKS[index])}
                    alt={step.imageAlt || step.title}
                  />
                </div>
                <div className="hstep-tags">
                  {[...new Set(step.tags?.filter(Boolean) ?? [])].map((tag, i) => (
                    <Fragment key={tag}>
                      {i > 0 && <span className="dot" />}
                      {tag}
                    </Fragment>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
