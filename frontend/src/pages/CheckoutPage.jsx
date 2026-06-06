import { Link } from 'react-router-dom'
import '../styles/request-to-book.css'
import useRequestToBook from '../hooks/useRequestToBook'
import RequestToBookSubbar from '../components/request-to-book/RequestToBookSubbar'
import RequestToBookStepper from '../components/request-to-book/RequestToBookStepper'
import BookingSummarySidebar from '../components/request-to-book/BookingSummarySidebar'
import Step1TripTimes from '../components/request-to-book/Step1TripTimes'
import Step2ExtrasCover from '../components/request-to-book/Step2ExtrasCover'
import Step3YourDetails from '../components/request-to-book/Step3YourDetails'
import Step4Payment from '../components/request-to-book/Step4Payment'
import BookingConfirmation from '../components/request-to-book/BookingConfirmation'
import { PageLoader } from '../components/ui/LoadingSpinner'
import PageHead from '../components/seo/PageHead'
import { useToast } from '../context/ToastContext'
import usePageSeo from '../hooks/usePageSeo'

export default function CheckoutPage() {
  const { toast } = useToast()
  const seo = usePageSeo('checkout', { robots: 'noindex' })
  const rtb = useRequestToBook()

  if (rtb.loadState === 'loading') {
    return (
      <>
        <PageHead {...seo} />
        <PageLoader message="Loading checkout…" />
      </>
    )
  }

  if (rtb.loadState === 'error' || !rtb.bookingType) {
    return (
      <>
        <PageHead {...seo} />
        <div className="mx-auto max-w-lg px-4 py-16 text-center">
        <p className="text-slate-600">No booking in progress.</p>
        <Link to="/" className="btn-primary mt-4 inline-flex">
          Browse listings
        </Link>
        </div>
      </>
    )
  }

  const backHref = rtb.config.backLink(rtb.item, rtb.bookingType)

  const handleCoupon = (code, applyClick) => {
    rtb.updateForm({ coupon_code: code })
    if (applyClick && code.trim()) toast('Promo code applied', 'success')
  }

  if (rtb.confirmed) {
    return (
      <>
        <PageHead {...seo} />
        <div className="rtb-page">
        <RequestToBookSubbar backHref={backHref} />
        <div className="rtb-page-inner">
          <div className="rtb-wrap">
            <BookingConfirmation
              confirmed={rtb.confirmed}
              config={rtb.config}
              item={rtb.item}
              itemImage={rtb.itemImage}
              form={rtb.form}
              nights={rtb.nights}
              bookingType={rtb.bookingType}
              locationName={rtb.locationName}
              selectedPriceType={rtb.selectedPriceType}
            />
          </div>
        </div>
      </div>
      </>
    )
  }

  const stepProps = {
    config: rtb.config,
    bookingType: rtb.bookingType,
    item: rtb.item,
    form: rtb.form,
    updateForm: rtb.updateForm,
    nights: rtb.nights,
    locations: rtb.locations,
    blockedDates: rtb.blockedDates,
    errors: rtb.errors,
    toggleAddon: rtb.toggleAddon,
    locationName: rtb.locationName,
    locationFeeLabel: rtb.locationFeeLabel,
    onNext: rtb.nextStep,
    onBack: rtb.prevStep,
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="rtb-page">
      <RequestToBookSubbar backHref={backHref} />
      <RequestToBookStepper
        steps={rtb.config.stepperSteps}
        currentStep={rtb.step}
        onStepClick={rtb.goStep}
      />
      <div className="rtb-page-inner">
        <div className="rtb-wrap">
          <div className="split">
            <div className="maincol">
              <div className={`stepview${rtb.step === 1 ? ' show' : ''}`}>
                {rtb.step === 1 && <Step1TripTimes {...stepProps} />}
              </div>
              <div className={`stepview${rtb.step === 2 ? ' show' : ''}`}>
                {rtb.step === 2 && <Step2ExtrasCover {...stepProps} />}
              </div>
              <div className={`stepview${rtb.step === 3 ? ' show' : ''}`}>
                {rtb.step === 3 && <Step3YourDetails {...stepProps} />}
              </div>
              <div className={`stepview${rtb.step === 4 ? ' show' : ''}`}>
                {rtb.step === 4 && (
                  <Step4Payment
                    {...stepProps}
                    saving={rtb.saving}
                    onSubmit={rtb.submit}
                  />
                )}
              </div>
            </div>
            <BookingSummarySidebar
              config={rtb.config}
              item={rtb.item}
              itemImage={rtb.itemImage}
              form={rtb.form}
              quote={rtb.quote}
              quoteLoading={rtb.quoteLoading}
              nights={rtb.nights}
              locationName={rtb.locationName}
              bookingType={rtb.bookingType}
              selectedPriceType={rtb.selectedPriceType}
              onCouponApply={handleCoupon}
            />
          </div>
        </div>
      </div>
    </div>
    </>
  )
}
