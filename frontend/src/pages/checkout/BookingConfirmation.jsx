import { Link } from 'react-router-dom'
import { formatMoney } from '../../api/rapyd'

/**
 * Guest booking confirmation shown after a successful 20% card payment.
 *
 * Props: { bookingReference, listingName, dates, location, totalPrice,
 *          platformFee, cashDue, currency }
 */
export default function BookingConfirmation({
  bookingReference,
  listingName,
  dates,
  location,
  totalPrice,
  platformFee,
  cashDue,
  currency = 'USD',
}) {
  return (
    <div className="mx-auto max-w-lg px-4 py-10">
      <div className="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
        <div className="text-center">
          <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-3xl">
            ✅
          </div>
          <h1 className="mt-4 text-2xl font-bold text-gray-900">Your booking is confirmed!</h1>
          {bookingReference ? (
            <p className="mt-1 text-sm text-gray-500">
              Booking reference: <span className="font-semibold text-gray-700">{bookingReference}</span>
            </p>
          ) : null}
        </div>

        <div className="mt-6 space-y-1 text-sm text-gray-700">
          {listingName ? <p className="font-medium text-gray-900">{listingName}</p> : null}
          {dates ? <p>{dates}</p> : null}
          {location ? <p className="text-gray-500">{location}</p> : null}
        </div>

        <hr className="my-6 border-gray-100" />

        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <span className="font-medium text-emerald-700">✅ Platform Fee Paid</span>
            <span className="font-semibold text-emerald-700">
              {formatMoney(platformFee, currency)} <span className="rounded bg-emerald-100 px-2 py-0.5 text-xs">PAID</span>
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span className="font-medium text-amber-700">💵 Cash Due on Arrival</span>
            <span className="font-semibold text-amber-700">
              {formatMoney(cashDue, currency)}{' '}
              <span className="rounded bg-amber-100 px-2 py-0.5 text-xs">DUE AT LOCATION</span>
            </span>
          </div>
        </div>

        <hr className="my-6 border-gray-100" />

        <div className="rounded-xl border border-amber-300 bg-amber-50 p-4">
          <p className="font-semibold text-amber-800">
            Remember to bring {formatMoney(cashDue, currency)} in cash to pay the host directly upon arrival.
          </p>
        </div>

        {totalPrice != null ? (
          <p className="mt-4 text-right text-sm text-gray-500">
            Total booking value: <span className="font-semibold text-gray-700">{formatMoney(totalPrice, currency)}</span>
          </p>
        ) : null}

        <Link
          to="/dashboard?type=guesthouse"
          className="mt-6 block w-full rounded-xl bg-teal-600 px-4 py-3 text-center font-semibold text-white transition hover:bg-teal-700"
        >
          View My Bookings
        </Link>
      </div>
    </div>
  )
}
