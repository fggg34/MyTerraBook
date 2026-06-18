import { useCallback } from 'react'
import { submitListingReview } from '../../api/listingReviews'
import ListingReviewForm from './ListingReviewForm'

export default function ListingWriteReview({ reviewTarget, onReviewsChange, className }) {
  const onSubmitReview = useCallback(
    async (payload) => {
      if (!reviewTarget) {
        throw new Error('Listing not ready')
      }
      await submitListingReview(reviewTarget.listingType, reviewTarget.id, payload)
      await onReviewsChange?.()
    },
    [reviewTarget, onReviewsChange],
  )

  return (
    <div className={className || undefined}>
      <ListingReviewForm onSubmit={onSubmitReview} disabled={!reviewTarget} />
    </div>
  )
}
