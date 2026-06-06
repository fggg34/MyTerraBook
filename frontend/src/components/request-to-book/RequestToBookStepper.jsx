export default function RequestToBookStepper({ steps, currentStep, onStepClick }) {
  return (
    <div className="stepper-wrap">
      <div className="rtb-wrap">
        <div className="stepper">
          {steps.map((stp, i) => (
            <span key={stp.num} style={{ display: 'contents' }}>
              <button
                type="button"
                className={`stp${currentStep === stp.num ? ' active' : ''}${currentStep > stp.num ? ' done' : ''}`}
                onClick={() => stp.num < currentStep && onStepClick?.(stp.num)}
                style={{ cursor: stp.num < currentStep ? 'pointer' : 'default' }}
              >
                <span className="num">{stp.num}</span>
                <span className="stx">
                  <span className="sk">{stp.sk}</span>
                  <span className="sl">{stp.sl}</span>
                </span>
              </button>
              {i < steps.length - 1 && (
                <div className={`stp-line${currentStep > stp.num ? ' filled' : ''}`} data-line={stp.num}>
                  <span className="fill" />
                </div>
              )}
            </span>
          ))}
        </div>
      </div>
    </div>
  )
}
