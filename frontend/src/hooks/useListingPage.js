import { useCallback, useEffect, useMemo, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { fetchListingReviews } from '../api/listingReviews'
import { LISTING_TYPES } from '../data/listingConfig'
import { mapCarToListing } from '../utils/mapCarToListing'
import { mapGuestHouseToListing } from '../utils/mapGuestHouseToListing'
import { mapApiListingReviews } from '../utils/mapListingReviews'
import { categoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'

export default function useListingPage(listingType) {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const queryDefaults = Object.fromEntries(searchParams.entries())
  const typeConfig = LISTING_TYPES[listingType] || LISTING_TYPES.campervan

  const [entity, setEntity] = useState(null)
  const [reviews, setReviews] = useState([])
  const [related, setRelated] = useState([])
  const [loadState, setLoadState] = useState('loading')

  const refetchReviews = useCallback(async () => {
    if (!id) return []
    try {
      const rows = await fetchListingReviews(listingType, id)
      setReviews(rows)
      return rows
    } catch {
      return []
    }
  }, [id, listingType])

  useEffect(() => {
    setLoadState('loading')
    setEntity(null)
    setReviews([])

    if (listingType === 'guesthouse') {
      api
        .get(`/guest-houses/${id}`)
        .then((res) => {
          const data = res.data?.data
          if (!data) {
            setLoadState('error')
            return
          }
          setEntity(data)
          setReviews(mapApiListingReviews(data.listing_reviews))
          setLoadState('ok')
        })
        .catch(() => setLoadState('error'))
      return undefined
    }

    api
      .get(`/cars/${id}`)
      .then((res) => {
        const data = res.data?.data
        const catName = data?.category?.name
        const allowed = typeConfig.categoryNames
        if (allowed?.length && catName && !categoryMatchesVehicleType(catName, allowed)) {
          setLoadState('error')
          return
        }
        setEntity(data)
        setReviews(mapApiListingReviews(data?.listing_reviews))
        setLoadState('ok')
      })
      .catch(() => setLoadState('error'))

    return undefined
  }, [id, listingType, typeConfig.categoryNames])

  useEffect(() => {
    if (!entity?.id) {
      setRelated([])
      return undefined
    }

    if (listingType === 'guesthouse') {
      api.get('/guest-houses', { params: { per_page: 12 } }).then((res) => {
        const all = res.data?.data || []
        const filtered = all.filter((h) => h.slug !== entity.slug && h.id !== entity.id).slice(0, 6)
        setRelated(filtered)
      })
      return undefined
    }

    api.get('/cars').then((res) => {
      const all = res.data.data || []
      const filtered = all
        .filter((c) => c.id !== entity.id)
        .filter((c) => {
          if (!typeConfig.categoryNames?.length) return true
          return categoryMatchesVehicleType(c.category_name, typeConfig.categoryNames)
        })
        .slice(0, 6)
      setRelated(filtered)
    })
    return undefined
  }, [entity, listingType, typeConfig.categoryNames])

  const listing = useMemo(() => {
    if (!entity) return null
    if (listingType === 'guesthouse') {
      return mapGuestHouseToListing(entity, reviews)
    }
    return mapCarToListing(entity, listingType, reviews)
  }, [entity, listingType, reviews])

  const reviewTarget = useMemo(() => {
    if (!id) return null
    return { listingType, id: String(id) }
  }, [id, listingType])

  return {
    listingType,
    typeConfig,
    listing,
    car: entity,
    related,
    loadState,
    queryDefaults,
    searchQuery: searchParams.toString(),
    reviewTarget,
    refetchReviews,
  }
}
