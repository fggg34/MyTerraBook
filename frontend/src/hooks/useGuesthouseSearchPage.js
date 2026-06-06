import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../api'
import { usePageContent } from '../context/SiteContentContext'
import {
  GUESTHOUSE_QUICK_FILTERS,
  GUESTHOUSE_SORT_OPTIONS,
  PAGE_SIZE,
  VEHICLE_TYPES,
} from '../data/searchResultsConfig'
import { mergePageContent } from '../utils/mergePageContent'
import { mapGuestHouseToResultCard } from '../utils/mapGuestHouseToResultCard'

function matchesPrice(card, maxEuros) {
  if (!maxEuros) return true
  return card.sortPrice <= maxEuros
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

  const cards = useMemo(() => {
    let list = houses.map((house) => mapGuestHouseToResultCard(house, { searchQuery }))

    list = list.filter((card) => matchesPrice(card, filters.maxPrice))
    if (filters.minGuests) list = list.filter((card) => card.sortGuests >= filters.minGuests)

    GUESTHOUSE_QUICK_FILTERS.forEach((qf) => {
      if (quickFilters.includes(qf.id)) {
        list = list.filter((card) => qf.match(card))
      }
    })

    list.sort((a, b) => {
      if (sort === 'price-asc') return a.sortPrice - b.sortPrice
      if (sort === 'price-desc') return b.sortPrice - a.sortPrice
      if (sort === 'guests') return b.sortGuests - a.sortGuests
      return a.name.localeCompare(b.name)
    })

    return list
  }, [houses, filters, quickFilters, searchQuery, sort])

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
    setQuickFilters((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]))
    setVisibleCount(PAGE_SIZE)
  }

  const clearFilters = () => {
    setFilters({ maxPrice: 500, minGuests: 0 })
    setQuickFilters([])
    setVisibleCount(PAGE_SIZE)
  }

  const hasActiveFilters =
    quickFilters.length > 0 || filters.minGuests > 0 || filters.maxPrice < 500

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
    quickFilterOptions: GUESTHOUSE_QUICK_FILTERS,
    filters,
    setFilters,
    quickFilters,
    toggleQuick,
    clearFilters,
    hasActiveFilters,
    guestsLabel,
    isGuesthouse: true,
  }
}
