import { useEffect, useMemo, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { GUESTHOUSE_MOCK, LISTING_TYPES } from '../data/listingConfig'
import { mapCarToListing } from '../utils/mapCarToListing'
import { categoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'

export default function useListingPage(listingType) {
  const { id } = useParams()
  const [searchParams] = useSearchParams()
  const queryDefaults = Object.fromEntries(searchParams.entries())
  const typeConfig = LISTING_TYPES[listingType] || LISTING_TYPES.campervan

  const [car, setCar] = useState(null)
  const [related, setRelated] = useState([])
  const [loadState, setLoadState] = useState('loading')

  useEffect(() => {
    if (listingType === 'guesthouse') {
      setCar({ ...GUESTHOUSE_MOCK, id: id || 'demo', slug: id || 'demo' })
      setLoadState('ok')
      return undefined
    }

    setLoadState('loading')
    api
      .get(`/cars/${id}`)
      .then((res) => {
        const data = res.data.data
        if (listingType !== 'guesthouse') {
          const catName = data?.category?.name
          const allowed = typeConfig.categoryNames
          if (allowed?.length && catName && !categoryMatchesVehicleType(catName, allowed)) {
            setLoadState('error')
            return
          }
        }
        setCar(data)
        setLoadState('ok')
      })
      .catch(() => setLoadState('error'))

    return undefined
  }, [id, listingType, typeConfig.categoryNames])

  useEffect(() => {
    if (!car?.id || listingType === 'guesthouse') {
      setRelated([])
      return undefined
    }
    api.get('/cars').then((res) => {
      const all = res.data.data || []
      const filtered = all
        .filter((c) => c.id !== car.id)
        .filter((c) => {
          if (!typeConfig.categoryNames?.length) return true
          return categoryMatchesVehicleType(
            c.category_name,
            typeConfig.categoryNames,
          )
        })
        .slice(0, 6)
      setRelated(filtered)
    })
    return undefined
  }, [car, listingType, typeConfig.categoryNames])

  const listing = useMemo(() => {
    if (!car) return null
    return mapCarToListing(car, listingType)
  }, [car, listingType])

  return {
    listingType,
    typeConfig,
    listing,
    car,
    related,
    loadState,
    queryDefaults,
    searchQuery: searchParams.toString(),
  }
}
