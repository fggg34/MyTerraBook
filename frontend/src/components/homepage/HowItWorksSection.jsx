import { Fragment, useRef } from 'react'
import CmsImage from '../cms/CmsImage'
import useHowStepsEffects from '../../hooks/useHowStepsEffects'

function StepMedia({ step }) {
  return (
    <CmsImage
      src={step.image}
      alt={step.imageAlt || step.title}
    />
  )
}

export default function HowItWorksSection({ heading, steps = [] }) {
  const wrapRef = useRef(null)
  useHowStepsEffects(wrapRef, steps.length)

  return (
    <section className="how">
      <div className="wrap">
        <div className="how-head">{heading && <h2>{heading}</h2>}</div>
        <div className="how-layout">
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
                    <StepMedia step={step} />
                  </div>
                  <div className="hstep-tags">
                    {[...new Set(step.tags?.filter(Boolean) ?? [])].map((tag, i) => (
                      <Fragment key={tag}>
                        {i > 0 && <span className="dot" />}
                        <span className="hstep-tag">{tag}</span>
                      </Fragment>
                    ))}
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="how-stage" aria-hidden="true">
            {steps.map((step, index) => (
              <div
                key={`stage-${step.num}`}
                className={`how-stage-frame${index === 0 ? ' active' : ''}`}
                data-step={index}
              >
                <StepMedia step={step} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
