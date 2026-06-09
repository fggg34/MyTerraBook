import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { usePageContent } from '../context/SiteContentContext'
import {
  GUESTHOUSE_SORT_OPTIONS,
  PAGE_SIZE,
  VEHICLE_TYPES,
} from '../data/searchResultsConfig'
import { mergePageContent } from '../utils/mergePageContent'
import { mapGuestHouseToResultCard } from '../utils/mapGuestHouseToResultCard'
import {
  applyQuickFilters,
  buildGuesthouseQuickFilters,
  pruneQuickFilters,
  toggleQuickFilter,
} from '../utils/searchQuickFilters'
import {
  clampPriceFilters,
  computePriceBounds,
  defaultPriceFilters,
  isPriceFilterActive,
} from '../utils/searchPriceBounds'

function matchesPrice(card, minPrice, maxPrice) {
  return card.sortPrice >= minPrice && card.sortPrice <= maxPrice
}

function formatDateRange(checkIn, checkOut) {
  if (!checkIn || !checkOut) return 'Select dates'
  const start = new Date(checkIn)
  const end = new Date(checkOut)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return 'Select dates'
  const fmt = (dt) => dt.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' })
  return `${fmt(start)} – ${fmt(end)}`
}

export default function useGuesthouseSearchPage(enabled = true) {
  const staticConfig = VEHICLE_TYPES.guesthouse
  const { page: cmsPage } = usePageContent('search-guesthouse', staticConfig)
  const config = useMemo(() => mergePageContent(staticConfig, cmsPage), [staticConfig, cmsPage])
  const [searchParams, setSearchParams] = useSearchParams()
  const query = Object.fromEntries(searchParams.entries())
  const searchQuery = searchParams.toString()

  const [houses, setHouses] = useState([])
  const [loading, setLoading] = useState(true)
  const [visibleCount, setVisibleCount] = useState(PAGE_SIZE)
  const [sort, setSort] = useState('rec')
  const [quickFilters, setQuickFilters] = useState([])
  const [filters, setFilters] = useState({
    minPrice: 0,
    maxPrice: 500,
    minGuests: 0,
  })

  useEffect(() => {
    if (!enabled) {
      setLoading(false)
      return undefined
    }

    setLoading(true)
    const params = { per_page: 100 }
    if (query.city) params.city = query.city
    if (query.type) params.type = query.type
    if (query.guests) params.guests = query.guests
    if (query.check_in) params.check_in = query.check_in
    if (query.check_out) params.check_out = query.check_out
    if (query.min_price) params.min_price = query.min_price
    if (query.max_price) params.max_price = query.max_price

    api
      .get('/guest-houses', { params })
      .then((res) => setHouses(res.data?.data ?? []))
      .catch(() => setHouses([]))
      .finally(() => setLoading(false))
  }, [
    enabled,
    query.city,
    query.type,
    query.guests,
    query.check_in,
    query.check_out,
    query.min_price,
    query.max_price,
  ])

  const allCards = useMemo(
    () => houses.map((house) => mapGuestHouseToResultCard(house, { searchQuery })),
    [houses, searchQuery],
  )

  const quickFilterOptions = useMemo(
    () => buildGuesthouseQuickFilters(houses),
    [houses],
  )

  const priceBounds = useMemo(() => computePriceBounds(allCards), [allCards])

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
    let list = [...allCards]

    list = list.filter((card) => matchesPrice(card, filters.minPrice, filters.maxPrice))
    if (filters.minGuests) list = list.filter((card) => card.sortGuests >= filters.minGuests)
    list = applyQuickFilters(list, quickFilters, quickFilterOptions)

    list.sort((a, b) => {
      if (sort === 'price-asc') return a.sortPrice - b.sortPrice
      if (sort === 'price-desc') return b.sortPrice - a.sortPrice
      if (sort === 'guests') return b.sortGuests - a.sortGuests
      return a.name.localeCompare(b.name)
    })

    return list
  }, [allCards, filters, quickFilters, quickFilterOptions, sort])

  const visibleCards = cards.slice(0, visibleCount)
  const cityLabel = query.city || 'Iceland'
  const dateLabel = formatDateRange(query.check_in, query.check_out)
  const guestsLabel = query.guests || '2'

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
    setFilters({ ...defaultPriceFilters(priceBounds), minGuests: 0 })
    setQuickFilters([])
    setVisibleCount(PAGE_SIZE)
  }

  const hasActiveFilters =
    quickFilters.length > 0 || filters.minGuests > 0 || isPriceFilterActive(filters, priceBounds)

  const sortLabel = GUESTHOUSE_SORT_OPTIONS.find((o) => o.id === sort)?.label || 'Recommended'

  return {
    config,
    loading,
    cards,
    visibleCards,
    visibleCount,
    setVisibleCount,
    totalCount: cards.length,
    pickupLabel: cityLabel,
    dropoffLabel: dateLabel,
    query,
    searchQuery,
    updateSearch,
    sort,
    setSort,
    sortLabel,
    sortOptions: GUESTHOUSE_SORT_OPTIONS,
    quickFilterOptions,
    filters,
    setFilters,
    quickFilters,
    toggleQuick,
    clearFilters,
    hasActiveFilters,
    priceBounds,
    guestsLabel,
    isGuesthouse: true,
  }
}
