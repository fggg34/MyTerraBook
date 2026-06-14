import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { usePageContent } from '../context/SiteContentContext'
import { PAGE_SIZE, SORT_OPTIONS, VEHICLE_TYPES } from '../data/searchResultsConfig'
import { mergePageContent } from '../utils/mergePageContent'
import { useFormatPrice } from './useFormatPrice'
import { mapCarToResultCard } from '../utils/mapCarToResultCard'
import {
  applyQuickFilters,
  buildCategoryQuickFilters,
  buildVehicleQuickFilters,
  pruneQuickFilters,
  toggleQuickFilter,
} from '../utils/searchQuickFilters'
import { mainCategoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'
import { toApiDateTime } from '../utils/format'
import {
  clampPriceFilters,
  computePriceBounds,
  defaultPriceFilters,
  isPriceFilterActive,
} from '../utils/searchPriceBounds'

function matchesTransmission(car, value) {
  if (!value) return true
  return String(car.transmission || '').toLowerCase().includes(value.toLowerCase())
}

function matchesPrice(car, minPrice, maxPrice) {
  return car.sortPrice >= minPrice && car.sortPrice <= maxPrice
}

const SEARCH_PAGE_KEYS = {
  campervan: 'search-campervan',
  car: 'search-car',
  guesthouse: 'search-guesthouse',
}

export default function useSearchResultsPage(vehicleType) {
  const staticConfig = VEHICLE_TYPES[vehicleType] || VEHICLE_TYPES.campervan
  const { page: cmsPage } = usePageContent(SEARCH_PAGE_KEYS[vehicleType] || 'search-campervan', staticConfig)
  const config = useMemo(() => mergePageContent(staticConfig, cmsPage), [staticConfig, cmsPage])
  const [searchParams, setSearchParams] = useSearchParams()
  const query = Object.fromEntries(searchParams.entries())
  const searchQuery = searchParams.toString()

  const [cars, setCars] = useState([])
  const [categories, setCategories] = useState([])
  const [locations, setLocations] = useState([])
  const [loading, setLoading] = useState(true)
  const [visibleCount, setVisibleCount] = useState(PAGE_SIZE)
  const [sort, setSort] = useState('rec')
  const [quickFilters, setQuickFilters] = useState([])
  const [filters, setFilters] = useState({
    minPrice: 0,
    maxPrice: 500,
    transmission: '',
    minSeats: 0,
    minSleeps: 0,
  })

  useEffect(() => {
    if (vehicleType === 'guesthouse') {
      setLoading(false)
      return undefined
    }

    setLoading(true)
    const params = {}
    if (query.pickup_location_id) params.pickup_location_id = query.pickup_location_id
    if (query.dropoff_location_id) params.dropoff_location_id = query.dropoff_location_id
    if (query.pickup_at) params.pickup_at = toApiDateTime(query.pickup_at)
    if (query.dropoff_at) params.dropoff_at = toApiDateTime(query.dropoff_at)

    const carParams = { ...params }
    if (config.mainCategorySlug) carParams.main_category = config.mainCategorySlug

    const fetchCars = (params) => api.get('/cars', { params }).then((res) => res.data.data || [])

    Promise.all([
      fetchCars(carParams),
      api.get('/sub-categories', { params: config.mainCategorySlug ? { main_category: config.mainCategorySlug, search_filters_only: 1 } : {} }),
      api.get('/locations'),
    ])
      .then(async ([carData, catRes, locRes]) => {
        const hasLocationFilter = query.pickup_location_id || query.dropoff_location_id
        if (carData.length === 0 && hasLocationFilter) {
          const fallbackParams = { ...carParams }
          delete fallbackParams.pickup_location_id
          delete fallbackParams.dropoff_location_id
          carData = await fetchCars(fallbackParams)
        }

        setCars(carData)
        setCategories(catRes.data.data || [])
        setLocations(locRes.data.data || [])
      })
      .finally(() => setLoading(false))
  }, [vehicleType, config.mainCategorySlug, query.pickup_location_id, query.dropoff_location_id, query.pickup_at, query.dropoff_at])

  const categoryMap = useMemo(() => {
    const m = {}
    categories.forEach((c) => {
      m[c.id] = c.name
    })
    return m
  }, [categories])

  const locationMap = useMemo(() => {
    const m = {}
    locations.forEach((l) => {
      m[l.id] = l.name
    })
    return m
  }, [locations])

  const priceFormatter = useFormatPrice()

  const vehicleCards = useMemo(() => {
    return cars
      .filter((car) => mainCategoryMatchesVehicleType(car.main_category_slug, vehicleType))
      .map((car) => {
        const categoryName = car.category_name || categoryMap[car.category_id]
        return mapCarToResultCard(
          { ...car, categoryName },
          { searchQuery, config, categoryName, priceFormatter },
        )
      })
  }, [cars, categoryMap, config, searchQuery, priceFormatter, vehicleType])

  const attributeQuickFilters = useMemo(() => buildVehicleQuickFilters(), [])

  const categoryFilterOptions = useMemo(
    () => buildCategoryQuickFilters(categories, vehicleCards),
    [categories, vehicleCards],
  )

  const quickFilterOptions = useMemo(
    () => [...attributeQuickFilters, ...categoryFilterOptions],
    [attributeQuickFilters, categoryFilterOptions],
  )

  const priceBounds = useMemo(() => computePriceBounds(vehicleCards), [vehicleCards])

  const transmissionOptions = useMemo(() => {
    const values = [...new Set(vehicleCards.map((c) => c.transmission).filter(Boolean))]
    if (!values.length) return ['automatic', 'manual']
    return values
  }, [vehicleCards])

  useEffect(() => {
    setFilters((prev) => {
      const atInitialDefaults = prev.minPrice === 0 && prev.maxPrice === 500
      const next = atInitialDefaults
        ? defaultPriceFilters(priceBounds)
        : clampPriceFilters(prev, priceBounds)
      if (next.minPrice === prev.minPrice && next.maxPrice === prev.maxPrice) return prev
      return { ...prev, ...next }
    })
  }, [priceBounds.min, priceBounds.max])

  useEffect(() => {
    setQuickFilters((prev) => pruneQuickFilters(prev, quickFilterOptions))
  }, [quickFilterOptions])

  const cards = useMemo(() => {
    let list = vehicleCards

    list = list.filter((car) => matchesTransmission(car, filters.transmission))
    list = list.filter((car) => matchesPrice(car, filters.minPrice, filters.maxPrice))
    if (filters.minSeats) list = list.filter((car) => car.sortSeats >= filters.minSeats)
    if (filters.minSleeps) list = list.filter((car) => car.sortSleeps >= filters.minSleeps)
    list = applyQuickFilters(list, quickFilters, quickFilterOptions)

    list.sort((a, b) => {
      if (sort === 'price-asc') return a.sortPrice - b.sortPrice
      if (sort === 'price-desc') return b.sortPrice - a.sortPrice
      if (sort === 'seats') return b.sortSeats - a.sortSeats
      if (sort === 'sleeps') return b.sortSleeps - a.sortSleeps
      return a.name.localeCompare(b.name)
    })

    return list
  }, [vehicleCards, filters, quickFilters, quickFilterOptions, sort])

  const visibleCards = cards.slice(0, visibleCount)
  const pickupLabel = locationMap[query.pickup_location_id] || 'Keflavík Airport (KEF)'
  const dropoffLabel =
    query.dropoff_location_id && query.dropoff_location_id !== query.pickup_location_id
      ? locationMap[query.dropoff_location_id] || 'Drop-off'
      : 'Same as pick-up'

  const updateSearch = (patch) => {
    const next = new URLSearchParams(searchParams)
    Object.entries(patch).forEach(([k, v]) => {
      if (v) next.set(k, v)
      else next.delete(k)
    })
    setSearchParams(next)
    setVisibleCount(PAGE_SIZE)
  }

  const toggleQuick = (id) => {
    setQuickFilters((prev) => toggleQuickFilter(prev, id, quickFilterOptions))
    setVisibleCount(PAGE_SIZE)
  }

  const clearFilters = () => {
    setFilters({ ...defaultPriceFilters(priceBounds), transmission: '', minSeats: 0, minSleeps: 0 })
    setQuickFilters([])
    setVisibleCount(PAGE_SIZE)
  }

  const hasActiveFilters =
    quickFilters.length > 0 ||
    filters.transmission ||
    filters.minSeats > 0 ||
    filters.minSleeps > 0 ||
    isPriceFilterActive(filters, priceBounds)

  const sortLabel = SORT_OPTIONS.find((o) => o.id === sort)?.label || 'Recommended'

  return {
    config,
    loading,
    cards,
    visibleCards,
    visibleCount,
    setVisibleCount,
    totalCount: cards.length,
    pickupLabel,
    dropoffLabel,
    query,
    searchQuery,
    updateSearch,
    sort,
    setSort,
    sortLabel,
    sortOptions: SORT_OPTIONS,
    quickFilterOptions,
    attributeQuickFilters,
    categoryFilterOptions,
    filters,
    setFilters,
    quickFilters,
    toggleQuick,
    clearFilters,
    hasActiveFilters,
    priceBounds,
    transmissionOptions,
    locations,
    categoryMap,
    guestsLabel: null,
    isGuesthouse: false,
  }
}
