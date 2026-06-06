import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { usePageContent } from '../context/SiteContentContext'
import { PAGE_SIZE, QUICK_FILTERS, SORT_OPTIONS, VEHICLE_TYPES } from '../data/searchResultsConfig'
import { mergePageContent } from '../utils/mergePageContent'
import { mapCarToResultCard } from '../utils/mapCarToResultCard'
import { categoryMatchesVehicleType } from '../utils/vehicleCategoryFilter'
import { toApiDateTime } from '../utils/format'

function matchesTransmission(car, value) {
  if (!value) return true
  return String(car.transmission || '').toLowerCase().includes(value.toLowerCase())
}

function matchesPrice(car, max) {
  if (!max) return true
  return car.sortPrice <= max
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

    Promise.all([api.get('/cars', { params }), api.get('/categories'), api.get('/locations')])
      .then(([carsRes, catRes, locRes]) => {
        setCars(carsRes.data.data || [])
        setCategories(catRes.data.data || [])
        setLocations(locRes.data.data || [])
      })
      .finally(() => setLoading(false))
  }, [vehicleType, query.pickup_location_id, query.dropoff_location_id, query.pickup_at, query.dropoff_at])

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

  const cards = useMemo(() => {
    let list = cars
      .filter((car) => {
        const name = car.category_name || categoryMap[car.category_id]
        return categoryMatchesVehicleType(name, config.categoryNames)
      })
      .map((car) => {
        const categoryName = car.category_name || categoryMap[car.category_id]
        return mapCarToResultCard(
          { ...car, categoryName },
          { searchQuery, config, categoryName },
        )
      })

    list = list.filter((car) => matchesTransmission(car, filters.transmission))
    list = list.filter((car) => matchesPrice(car, filters.maxPrice))
    if (filters.minSeats) list = list.filter((car) => car.sortSeats >= filters.minSeats)
    if (filters.minSleeps) list = list.filter((car) => car.sortSleeps >= filters.minSleeps)

    QUICK_FILTERS.forEach((qf) => {
      if (quickFilters.includes(qf.id)) {
        list = list.filter((car) => qf.match({ ...car, categoryName: car.categoryName }))
      }
    })

    list.sort((a, b) => {
      if (sort === 'price-asc') return a.sortPrice - b.sortPrice
      if (sort === 'price-desc') return b.sortPrice - a.sortPrice
      if (sort === 'seats') return b.sortSeats - a.sortSeats
      if (sort === 'sleeps') return b.sortSleeps - a.sortSleeps
      return a.name.localeCompare(b.name)
    })

    return list
  }, [cars, categoryMap, config, filters, quickFilters, searchQuery, sort])

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
    setQuickFilters((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]))
    setVisibleCount(PAGE_SIZE)
  }

  const clearFilters = () => {
    setFilters({ maxPrice: 500, transmission: '', minSeats: 0, minSleeps: 0 })
    setQuickFilters([])
    setVisibleCount(PAGE_SIZE)
  }

  const hasActiveFilters =
    quickFilters.length > 0 ||
    filters.transmission ||
    filters.minSeats > 0 ||
    filters.minSleeps > 0 ||
    filters.maxPrice < 500

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
    quickFilterOptions: QUICK_FILTERS,
    filters,
    setFilters,
    quickFilters,
    toggleQuick,
    clearFilters,
    hasActiveFilters,
    locations,
    categoryMap,
    guestsLabel: null,
    isGuesthouse: false,
  }
}
