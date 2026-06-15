import { useCallback, useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import DatePicker from 'react-datepicker'
import 'react-datepicker/dist/react-datepicker.css'
import { api } from '../../api'
import GuestHouseCard from '../../components/guest-houses/GuestHouseCard'
import EmptyState from '../../components/ui/EmptyState'
import { Home } from 'lucide-react'

const TYPES = [
  { id: '', label: 'All' },
  { id: 'villa', label: 'Villa' },
  { id: 'apartment', label: 'Apartment' },
  { id: 'cottage', label: 'Cottage' },
  { id: 'studio', label: 'Studio' },
  { id: 'room', label: 'Room' },
  { id: 'chalet', label: 'Chalet' },
]

export default function GuestHouseListPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [houses, setHouses] = useState([])
  const [meta, setMeta] = useState({})
  const [loading, setLoading] = useState(true)
  const [page, setPage] = useState(1)

  const filters = useMemo(
    () => ({
      city: searchParams.get('city') || '',
      type: searchParams.get('type') || '',
      guests: searchParams.get('guests') || '2',
      check_in: searchParams.get('check_in') || '',
      check_out: searchParams.get('check_out') || '',
      min_price: searchParams.get('min_price') || '',
      max_price: searchParams.get('max_price') || '',
    }),
    [searchParams],
  )

  const [local, setLocal] = useState(filters)
  const [checkIn, setCheckIn] = useState(filters.check_in ? new Date(filters.check_in) : null)
  const [checkOut, setCheckOut] = useState(filters.check_out ? new Date(filters.check_out) : null)
  const [priceMax, setPriceMax] = useState(Number(filters.max_price) || 30000)

  const fetchList = useCallback(() => {
    setLoading(true)
    const params = { page, per_page: 12 }
    if (filters.city) params.city = filters.city
    if (filters.type) params.type = filters.type
    if (filters.guests) params.guests = filters.guests
    if (filters.check_in) params.check_in = filters.check_in
    if (filters.check_out) params.check_out = filters.check_out
    if (filters.min_price) params.min_price = filters.min_price
    if (filters.max_price) params.max_price = filters.max_price

    api
      .get('/guest-houses', { params })
      .then((res) => {
        setHouses(res.data?.data ?? [])
        setMeta(res.data?.meta ?? {})
      })
      .catch(() => setHouses([]))
      .finally(() => setLoading(false))
  }, [filters, page])

  useEffect(() => {
    fetchList()
  }, [fetchList])

  const applyFilters = (e) => {
    e?.preventDefault()
    const next = new URLSearchParams()
    if (local.city) next.set('city', local.city)
    if (local.type) next.set('type', local.type)
    if (local.guests) next.set('guests', local.guests)
    if (checkIn) next.set('check_in', checkIn.toISOString().slice(0, 10))
    if (checkOut) next.set('check_out', checkOut.toISOString().slice(0, 10))
    if (priceMax < 30000) next.set('max_price', String(priceMax))
    setPage(1)
    setSearchParams(next)
  }

  const queryString = searchParams.toString()

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <h1 className="section-title">Guest houses &amp; stays</h1>
      <p className="section-subtitle">Find your perfect home away from home in Iceland.</p>

      <form onSubmit={applyFilters} className="mt-8 rounded-xl border border-slate-200 bg-white p-4 shadow-card">
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
          <input
            type="text"
            placeholder="City"
            className="input-field"
            value={local.city}
            onChange={(e) => setLocal((s) => ({ ...s, city: e.target.value }))}
          />
          <DatePicker
            selected={checkIn}
            onChange={setCheckIn}
            placeholderText="Check-in"
            className="input-field w-full"
            minDate={new Date()}
          />
          <DatePicker
            selected={checkOut}
            onChange={setCheckOut}
            placeholderText="Check-out"
            className="input-field w-full"
            minDate={checkIn || new Date()}
          />
          <input
            type="number"
            min={1}
            max={20}
            className="input-field"
            value={local.guests}
            onChange={(e) => setLocal((s) => ({ ...s, guests: e.target.value }))}
            placeholder="Guests"
          />
          <button type="submit" className="btn-primary">
            Search
          </button>
        </div>
        <div className="mt-4 flex flex-wrap gap-2">
          {TYPES.map(({ id, label }) => (
            <button
              key={id || 'all'}
              type="button"
              onClick={() => {
                setLocal((s) => ({ ...s, type: id }))
                const next = new URLSearchParams(searchParams)
                if (id) next.set('type', id)
                else next.delete('type')
                setSearchParams(next)
              }}
              className={`rounded-full px-3 py-1 text-sm font-medium ${
                filters.type === id ? 'bg-accent text-white' : 'bg-slate-100 text-slate-600'
              }`}
            >
              {label}
            </button>
          ))}
        </div>
        <div className="mt-4">
          <label className="text-sm text-slate-600">Max price per night (cents): {priceMax}</label>
          <input
            type="range"
            min={3000}
            max={30000}
            step={500}
            value={priceMax}
            onChange={(e) => setPriceMax(Number(e.target.value))}
            className="mt-1 w-full"
          />
        </div>
      </form>

      {!loading && houses.length === 0 ? (
        <EmptyState
          icon={Home}
          title="No stays found"
          description="Try different dates or filters."
          className="mt-12"
        />
      ) : (
        <>
          <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {houses.map((house) => (
              <GuestHouseCard key={house.id} house={house} searchParams={queryString} />
            ))}
          </div>
          {meta.last_page > 1 && (
            <div className="mt-8 flex justify-center gap-2">
              <button
                type="button"
                disabled={page <= 1}
                className="btn-secondary"
                onClick={() => setPage((p) => p - 1)}
              >
                Previous
              </button>
              <span className="flex items-center px-4 text-sm text-slate-600">
                Page {meta.current_page} of {meta.last_page}
              </span>
              <button
                type="button"
                disabled={page >= meta.last_page}
                className="btn-secondary"
                onClick={() => setPage((p) => p + 1)}
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  )
}
