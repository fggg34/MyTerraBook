import { Fragment, useState } from 'react'

export default function HowItWorksSection({ heading, steps = [] }) {
  const [activeStep, setActiveStep] = useState(0)

  return (
    <section className="how">
      <div className="wrap">
        <div className="how-head">{heading && <h2>{heading}</h2>}</div>
        <div className="how-steps" id="howSteps">
          {steps.map((step, index) => (
            <div
              key={step.num}
              className={`hstep ${activeStep === index ? 'active' : ''}`}
              data-step={index}
              onMouseEnter={() => setActiveStep(index)}
              onFocus={() => setActiveStep(index)}
              onClick={() => setActiveStep(index)}
              role="button"
              tabIndex={0}
              onKeyDown={(e) => {
                if (e.key === 'Enter' || e.key === ' ') setActiveStep(index)
              }}
            >
              <span className="hstep-bar" />
              <div className="hstep-num">{step.num}</div>
              <div className="hstep-title">{step.title}</div>
              <div className="hstep-body">
                <p className="hstep-desc">{step.description}</p>
                <div className="hstep-media">
                  <img src={step.image} alt={step.imageAlt || step.title} />
                </div>
                <div className="hstep-tags">
                  {step.tags?.map((tag, i) => (
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
