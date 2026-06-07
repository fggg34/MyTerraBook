import { useCallback, useEffect, useMemo, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { fetchListingReviews } from '../api/listingReviews'
import { usePageContent } from '../context/SiteContentContext'
import { LISTING_TYPES } from '../data/listingConfig'
import { mergePageContent } from '../utils/mergePageContent'
import { mapCarToListing } from '../utils/mapCarToListing'
import { mapGuestHouseToListing } from '../utils/mapGuestHouseToListing'
import { mapApiListingReviews } from '../utils/mapListingReviews'
import { mainCategoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'

const LISTING_PAGE_KEYS = {
  campervan: 'listing-campervan',
  car: 'listing-car',
  guesthouse: 'listing-guesthouse',
}

export default function useListingPage(listingType) {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const queryDefaults = Object.fromEntries(searchParams.entries())
  const staticConfig = LISTING_TYPES[listingType] || LISTING_TYPES.campervan
  const { page: cmsPage } = usePageContent(LISTING_PAGE_KEYS[listingType] || 'listing-campervan', staticConfig)
  const typeConfig = useMemo(() => mergePageContent(staticConfig, cmsPage), [staticConfig, cmsPage])

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
        const mainCategorySlug = data?.main_category?.slug || data?.category?.main_category_slug
        if (typeConfig.mainCategorySlug && mainCategorySlug && !mainCategoryMatchesVehicleType(mainCategorySlug, listingType)) {
          setLoadState('error')
          return
        }
        setEntity(data)
        setReviews(mapApiListingReviews(data?.listing_reviews))
        setLoadState('ok')
      })
      .catch(() => setLoadState('error'))

    return undefined
  }, [id, listingType, typeConfig.mainCategorySlug])

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

    const params = typeConfig.mainCategorySlug ? { main_category: typeConfig.mainCategorySlug } : {}

    api.get('/cars', { params }).then((res) => {
      const all = res.data.data || []
      const filtered = all
        .filter((c) => c.id !== entity.id)
        .filter((c) => mainCategoryMatchesVehicleType(c.main_category_slug, listingType))
        .slice(0, 6)
      setRelated(filtered)
    })
    return undefined
  }, [entity, listingType, typeConfig.mainCategorySlug])

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
